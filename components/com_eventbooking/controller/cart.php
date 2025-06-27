<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;
use OSSolution\EventBooking\Admin\Event\Registration\ValidateCartFormData;

class EventbookingControllerCart extends EventbookingController
{
	use EventbookingControllerCaptcha;

	/**
	 * Add the selected events to shopping cart
	 *
	 * @throws Exception
	 */
	public function add_cart()
	{
		$data = $this->input->getData();

		if (is_numeric($data['id']))
		{
			// Check if this is event is password protected
			$event = EventbookingHelperDatabase::getEvent($data['id']);

			if (!$event)
			{
				throw new Exception(Text::_('EB_EVENT_NOT_FOUND'), 404);
			}

			if ($event->event_password)
			{
				$passwordPassed = $this->app->getSession()->get('eb_passowrd_' . $event->id, 0);

				if (!$passwordPassed)
				{
					$return = base64_encode(Uri::getInstance()->toString());
					$this->app->redirect(
						Route::_(
							'index.php?option=com_eventbooking&view=password&event_id=' . $event->id . '&return=' . $return . '&Itemid=' . $this->input->getInt(
								'Itemid',
								0
							),
							false
						)
					);
				}
				else
				{
					// Add event to cart, then redirect to cart page

					/* @var EventbookingModelCart $model */
					$model = $this->getModel('cart');
					$model->processAddToCart($data);
					$Itemid = $this->input->getInt('Itemid', 0);
					$this->app->redirect(
						Route::_(
							EventbookingHelperRoute::getViewRoute('cart', $Itemid) . EventbookingHelper::addTimeToUrl(),
							false
						)
					);
				}
			}
		}

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');
		$model->processAddToCart($data);

		$this->input->set('view', 'cart');
		$this->input->set('layout', 'mini');

		$this->display();

		$this->app->close();
	}

	/**
	 * Add selected events to cart and redirect to checkout page
	 */
	public function add_events_to_cart()
	{
		$eventIds = $this->input->post->getString('event_ids');
		$eventIds = explode(',', $eventIds);
		$eventIds = array_filter(ArrayHelper::toInteger($eventIds));
		$Itemid   = $this->input->getInt('Itemid');

		$data['id'] = $eventIds;

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');
		$model->processAddToCart($data);

		// Redirect to checkout page
		$checkoutUrl = Route::_(
			'index.php?option=com_eventbooking&task=view_checkout&Itemid=' . $Itemid . EventbookingHelper::addTimeToUrl(
			),
			false
		);

		$this->app->redirect($checkoutUrl);
	}

	/**
	 * Update the cart with new updated quantities
	 *
	 * @throws Exception
	 */
	public function update_cart()
	{
		$Itemid     = $this->input->getInt('Itemid', 0);
		$redirect   = $this->input->getInt('redirect', 1);
		$eventIds   = $this->input->get('event_id', '', 'none');
		$quantities = $this->input->get('quantity', '', 'none');

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');

		if (!$redirect)
		{
			$eventIds   = explode(',', $eventIds);
			$quantities = explode(',', $quantities);
		}

		$model->processUpdateCart($eventIds, $quantities);

		if ($redirect)
		{
			$this->setRedirect(
				Route::_(
					EventbookingHelperRoute::getViewRoute('cart', $Itemid) . EventbookingHelper::addTimeToUrl(),
					false
				)
			);
		}
		else
		{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'mini');
			$this->display();
			$this->app->close();
		}
	}

	/**
	 * Remove the selected event from shopping cart
	 */
	public function remove_cart()
	{
		$redirect = $this->input->getInt('redirect', 1);
		$Itemid   = $this->input->getInt('Itemid', 0);
		$id       = $this->input->getInt('id', 0);

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');
		$model->removeEvent($id);

		if ($redirect)
		{
			$this->setRedirect(
				Route::_(
					EventbookingHelperRoute::getViewRoute('cart', $Itemid) . EventbookingHelper::addTimeToUrl(),
					false
				)
			);
		}
		else
		{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'mini');

			$this->display();

			$this->app->close();
		}
	}

	/***
	 * Process checkout
	 *
	 * @throws Exception
	 */
	public function process_checkout()
	{
		$this->antiSpam();

		$user   = $this->app->getIdentity();
		$config = EventbookingHelper::getConfig();
		$errors = [];

		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = Text::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		$cart  = new EventbookingHelperCart();
		$items = $cart->getItems();

		if (!count($items))
		{
			$this->app->enqueueMessage(Text::_('Sorry, your session was expired. Please try again!'), 'warning');
			$this->app->redirect(Uri::root());
		}

		if (!$user->id && $config->use_email_as_username && !$this->input->post->exists('username'))
		{
			$this->input->post->set('username', $this->input->post->getString('email'));
		}

		// Validate username and password
		if (!$user->id && $config->user_registration)
		{
			$errors = array_merge(
				$errors,
				EventbookingHelperValidator::validateUsername($this->input->post->get('username', '', 'raw'))
			);
			$errors = array_merge(
				$errors,
				EventbookingHelperValidator::validatePassword($this->input->post->get('password1', '', 'raw'))
			);
		}

		// Check email
		$result = $this->validateRegistrantEmail($items, $this->input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$errors[] = $result['message'];
		}

		$data = $this->input->post->getData();

		if ($formErrors = $this->validateFormData($data))
		{
			$errors = array_merge($errors, $formErrors);
		}

		// Validate quantity
		$events = $cart->getEvents();

		foreach ($events as $event)
		{
			if ($event->event_capacity)
			{
				$numberRegistrantsAvailable = $event->event_capacity - $event->total_registrants + EventbookingHelperRegistration::countAwaitingPaymentRegistrations(
						$event
					);

				if ($numberRegistrantsAvailable < $event->quantity)
				{
					$errors[] = Text::sprintf(
						'EB_NUMBER_REGISTRANTS_ERROR',
						$event->quantity,
						$numberRegistrantsAvailable
					);
				}
			}
		}

		if (count($errors))
		{
			// Enqueue the error message
			foreach ($errors as $error)
			{
				$this->app->enqueueMessage($error, 'error');
			}

			$this->input->set('captcha_invalid', 1);
			$this->input->set('view', 'register');
			$this->input->set('layout', 'cart');
			$this->display();

			return;
		}

		/* @var EventbookingModelCart $model */
		$model  = $this->getModel('cart');
		$return = $model->processCheckout($data);

		if ($return == 1)
		{
			$this->setRedirect(
				Route::_(
					'index.php?option=com_eventbooking&view=complete&registration_code=' . $data['registration_code'] . '&Itemid=' . $this->input->getInt(
						'Itemid'
					),
					false
				)
			);
		}
	}

	/**
	 * Calculate registration fee, then update information on cart registration form
	 */
	public function calculate_cart_registration_fee()
	{
		$input               = $this->input;
		$config              = EventbookingHelper::getConfig();
		$paymentMethod       = $input->getString('payment_method', '');
		$data                = $input->post->getData();
		$data['coupon_code'] = $input->getString('coupon_code', '');
		$cart                = new EventbookingHelperCart();
		$response            = [];
		$rowFields           = EventbookingHelperRegistration::getFormFields(0, 4);
		$form                = new RADForm($rowFields);
		$form->bind($data);
		$form->handleFieldsDependOnPaymentMethod($paymentMethod);

		$fees = EventbookingHelper::callOverridableHelperMethod(
			'Registration',
			'calculateCartRegistrationFee',
			[$cart, $form, $data, $config, $paymentMethod],
			'Helper'
		);

		$response['total_amount']           = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount']        = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount']             = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['late_fee']               = EventbookingHelper::formatAmount($fees['late_fee'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount(
			$fees['payment_processing_fee'],
			$config
		);
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount']         = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid']           = $fees['coupon_valid'];
		$response['payment_amount']         = round($fees['amount'], 2);

		$response['vat_number_valid']      = $fees['vat_number_valid'];
		$response['show_vat_number_field'] = $fees['show_vat_number_field'];

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Validate form data, make sure the required fields are entered
	 *
	 * @param   array  $data
	 *
	 * @return array
	 */
	protected function validateFormData($data)
	{
		$config        = EventbookingHelper::getConfig();
		$rowFields     = EventbookingHelperRegistration::getFormFields(0, 4);
		$paymentMethod = $data['payment_method'] ?? '';

		$form = new RADForm($rowFields);
		$form->bind($data)
			->buildFieldsDependency();
		$form->handleFieldsDependOnPaymentMethod($paymentMethod);
		$errors = [];

		// Validate members input
		if ($config->collect_member_information_in_cart)
		{
			$cart       = new EventbookingHelperCart();
			$items      = $cart->getItems();
			$quantities = $cart->getQuantities();
			$count      = 0;

			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$eventId  = $items[$i];
				$quantity = $quantities[$i];
				$event    = EventbookingHelperDatabase::getEvent($eventId);

				$memberFormFields = EventbookingHelperRegistration::getFormFields($eventId, 2);

				for ($j = 0; $j < $quantity; $j++)
				{
					$count++;
					$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields(
						$memberFormFields,
						$j + 1
					);
					$memberForm              = new RADForm($currentMemberFormFields);
					$memberForm->setFieldSuffix($count);
					$memberForm->bind($data);
					$memberForm->buildFieldsDependency();
					$memberErrors = $memberForm->validate();

					if (count($memberErrors))
					{
						foreach ($memberErrors as $memberError)
						{
							$errors[] = Text::sprintf(
									'EB_MEMBER_VALIDATION_ERROR',
									$event->title,
									$j + 1
								) . ' ' . $memberError;
						}
					}
				}
			}
		}

		$errors = array_merge($errors, $form->validate());

		// Validate privacy policy
		if ($config->show_privacy_policy_checkbox && empty($data['agree_privacy_policy']))
		{
			$errors[] = Text::_('EB_AGREE_PRIVACY_POLICY_ERROR');
		}

		PluginHelper::importPlugin('eventbooking');

		$eventObj = new ValidateCartFormData(
			'onEBValidateCartFormData',
			['data' => $data]
		);

		$validateResults = $this->app->triggerEvent('onEBValidateCartFormData', $eventObj);

		foreach ($validateResults as $result)
		{
			if (is_array($result) && count($result))
			{
				$errors = array_merge($errors, $result);
			}
		}

		return $errors;
	}

	/**
	 * Validate to see whether this email can be used to register for this event or not
	 *
	 * @param   array  $eventIds
	 * @param          $email
	 *
	 * @return array
	 */
	protected function validateRegistrantEmail($eventIds, $email)
	{
		$user = $this->app->getIdentity();

		/* @var DatabaseDriver $db */
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$result = [
			'success' => true,
			'message' => '',
		];

		if ($config->prevent_duplicate_registration)
		{
			$registeredEventTitles = [];

			foreach ($eventIds as $eventId)
			{
				if (!EventbookingHelperValidator::validateDuplicateRegistration($eventId, $user->id, $email))
				{
					$event                   = EventbookingHelperDatabase::getEvent($eventId);
					$registeredEventTitles[] = $event->title;
				}
			}

			if (count($registeredEventTitles))
			{
				$result['success'] = false;
				$result['message'] = Text::sprintf(
					'EB_YOU_REGISTERED_FOR_EVENTS',
					implode(' | ', $registeredEventTitles)
				);
			}
		}

		if ($result['success'] && $config->user_registration && !$user->id)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__users')
				->where('email = ' . $db->quote($email));
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total)
			{
				$result['success'] = false;
				$result['message'] = Text::_('EB_EMAIL_USED_BY_DIFFERENT_USER');
			}
		}

		if ($result['success'] && !EventbookingHelperValidator::validateEmailDomain($email))
		{
			$emailDomain = explode('@', $email);
			$emailDomain = $emailDomain[1];

			$result['success'] = false;
			$result['message'] = Text::sprintf('JGLOBAL_EMAIL_DOMAIN_NOT_ALLOWED', $emailDomain);
		}

		return $result;
	}

	/**
	 * Refresh content of cart module so that data will be keep synchronized
	 *
	 * This is now done by the module itself. The method is left here to avoid fatal error in case someone
	 * call the method in the override
	 *
	 * @return void
	 */
	protected function reloadCartModule()
	{
	}
}
