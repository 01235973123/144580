<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;

class OsMembershipViewCompleteHtml extends MPFViewHtml
{
	/**
	 * Flag to mark that this view does not have an associated model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * The subscription complete message
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * The conversion tracking code
	 *
	 * @var string
	 */
	protected $conversionTrackingCode;

	/**
	 * The subscription record
	 *
	 * @var stdClass
	 */
	protected $rowSubscriber;

	/**
	 * The replaces array
	 *
	 * @var array
	 */
	protected $replaces;

	/**
	 * Preview view data before rendering
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$app     = Factory::getApplication();
		$user    = $app->getIdentity();
		$session = $app->getSession();

		/* @var DatabaseDriver $db */
		$db               = Factory::getContainer()->get('db');
		$query            = $db->getQuery(true);
		$subscriptionId   = $session->get('mp_subscription_id');
		$subscriptionCode = $app->getInput()->getString('subscription_code');

		if (!$subscriptionId && !$subscriptionCode)
		{
			$app->enqueueMessage(Text::_('Invalid subscription code'));
			$app->redirect(Uri::root(), 404);

			return;
		}

		// Get subscriber information
		$query->select('*')
			->from('#__osmembership_subscribers');

		if ($subscriptionId > 0)
		{
			$query->where('id = ' . (int) $subscriptionId);
		}
		else
		{
			$query->where('subscription_code = ' . $db->quote($subscriptionCode));
		}

		$db->setQuery($query);
		$rowSubscriber = $db->loadObject();

		if (!$rowSubscriber)
		{
			$app->enqueueMessage(Text::_('Invalid subscription code'));

			$app->redirect(Uri::root(), 404);

			return;
		}

		$subscriptionId = $rowSubscriber->id;

		// Validate ideal payment, very special case
		if ($rowSubscriber->gross_amount > 0 && $rowSubscriber->published == 0
			&& (str_contains($rowSubscriber->payment_method, 'os_ideal') || str_contains(
					$rowSubscriber->payment_method,
					'os_payu'
				)))
		{
			// Use online payment method and the payment is not success for some reason, we need to redirec to failure page
			$failureUrl = Route::_(
				'index.php?option=com_osmembership&view=failure&id=' . $rowSubscriber->id . '&Itemid=' . $this->Itemid,
				false
			);
			$session->set('omnipay_payment_error_reason', Text::_('OSM_PAYMENT_FAILURE_OR_CANCELLED'));
			$app->redirect($failureUrl);
		}

		$config = OSMembershipHelper::getConfig();

		// Workaround for auto-login in case payment gateway notify the system late about payment, wait up to 18 seconds
		if (!$user->id && $config->auto_login
			&& ($rowSubscriber->published == 0 || $rowSubscriber->user_id == 0)
			&& !str_contains($rowSubscriber->payment_method, 'os_offline'))
		{
			$query->clear()
				->select('*')
				->from('#__osmembership_subscribers')
				->where('id = ' . (int) $subscriptionId);

			for ($i = 0; $i < 6; $i++)
			{
				sleep(3);

				// Reload the subscription record from database to get updated data
				$db->setQuery($query);
				$rowSubscriber = $db->loadObject();

				if ($rowSubscriber->published == 1 && $rowSubscriber->user_id > 0)
				{
					break;
				}
			}
		}

		$messageObj  = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		//Get plan information
		$query->clear()
			->select('a.*')
			->select($db->quoteName('a.title' . $fieldSuffix, 'title'))
			->from('#__osmembership_plans AS a')
			->where('id = ' . (int) $rowSubscriber->plan_id);
		$db->setQuery($query);
		$rowPlan = $db->loadObject();

		// Auto Login and auto reload Joomla groups
		if ($rowSubscriber->user_id && $rowSubscriber->published == 1)
		{
			$user = Factory::getApplication()->getIdentity();

			if ($user->id)
			{
				$user->clearAccessRights();
			}
			elseif ($config->auto_login)
			{
				$loginProcessed = false;

				if ($session->get('mp_subscriber_password'))
				{
					$subscriberAccount = Factory::getUser((int) $rowSubscriber->user_id);

					if ($subscriberAccount->id && !$subscriberAccount->block)
					{
						$options             = [];
						$options['remember'] = true;

						// Get the login credentials.
						$credentials             = [];
						$credentials['username'] = $subscriberAccount->username;
						$credentials['password'] = $session->get('mp_subscriber_password');

						$app->login($credentials, $options);

						$loginProcessed = true;
					}
				}

				if (!$loginProcessed)
				{
					$session->set('user', new User($rowSubscriber->user_id));
				}
			}
		}

		// Check and redirect subscriber back to restricted page if needed
		$user = Factory::getApplication()->getIdentity();

		if ($user->id && $rowSubscriber->published == 1)
		{
			$sessionReturnUrl       = $session->get('osm_return_url');
			$sessionRequiredPlanIds = $session->get('required_plan_ids');

			if (!empty($sessionReturnUrl) && is_array($sessionRequiredPlanIds) && in_array(
					$rowSubscriber->plan_id,
					$sessionRequiredPlanIds
				))
			{
				// Clear the old session data
				$session->clear('osm_return_url');
				$session->clear('required_plan_ids');
				$app->redirect($sessionReturnUrl);
			}
		}

		// If a custom URL is set up for this plan, we need to redirect to that custom URL
		if (str_contains($rowSubscriber->payment_method, 'os_offline')
			&& $rowSubscriber->published == 0
			&& $rowPlan->offline_payment_subscription_complete_url)
		{
			$app->redirect($rowPlan->offline_payment_subscription_complete_url);
		}
		elseif ($rowPlan->subscription_complete_url)
		{
			$app->redirect($rowPlan->subscription_complete_url);
		}

		if (str_contains($rowSubscriber->payment_method, 'os_offline') && $rowSubscriber->published == 0)
		{
			$useOfflinePayment = true;
			$offlineSuffix     = str_replace('os_offline', '', $rowSubscriber->payment_method);
		}
		else
		{
			$useOfflinePayment = false;
			$offlineSuffix     = '';
		}

		if ($rowPlan->category_id > 0)
		{
			$category = OSMembershipHelperDatabase::getCategory($rowPlan->category_id);

			OSMembershipHelper::setPlanMessagesDataFromCategory($rowPlan, $category, [
				'thanks_message',
				'thanks_message_offline',
				'renew_thanks_message',
				'renew_thanks_message_offline',
				'upgrade_thanks_message',
				'upgrade_thanks_message_offline',
			]);
		}

		switch ($rowSubscriber->act)
		{
			case 'renew':
				// Use offline payment thank you message if available
				if ($useOfflinePayment)
				{
					if ($offlineSuffix && $fieldSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'renew_thanks_message_offline' . $offlineSuffix . $fieldSuffix}
						))
					{
						$message = $messageObj->{'renew_thanks_message_offline' . $offlineSuffix . $fieldSuffix};
					}
					elseif ($offlineSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'renew_thanks_message_offline' . $offlineSuffix}
						))
					{
						$message = $messageObj->{'renew_thanks_message_offline' . $offlineSuffix};
					}
					elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
							$rowPlan->{'renew_thanks_message_offline' . $fieldSuffix}
						))
					{
						$message = $rowPlan->{'renew_thanks_message_offline' . $fieldSuffix};
					}
					elseif (OSMembershipHelper::isValidMessage($rowPlan->renew_thanks_message_offline))
					{
						$message = $rowPlan->renew_thanks_message_offline;
					}
					elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'renew_thanks_message_offline' . $fieldSuffix}
						))
					{
						$message = $messageObj->{'renew_thanks_message_offline' . $fieldSuffix};
					}
					elseif (OSMembershipHelper::isValidMessage($messageObj->renew_thanks_message_offline))
					{
						$message = $messageObj->renew_thanks_message_offline;
					}
					else
					{
						$message = $messageObj->renew_thanks_message;
					}
				}
				elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
						$rowPlan->{'renew_thanks_message' . $fieldSuffix}
					))
				{
					$message = $rowPlan->{'renew_thanks_message' . $fieldSuffix};
				}
				elseif (OSMembershipHelper::isValidMessage($rowPlan->renew_thanks_message))
				{
					$message = $rowPlan->renew_thanks_message;
				}
				elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
						$messageObj->{'renew_thanks_message' . $fieldSuffix}
					))
				{
					$message = $messageObj->{'renew_thanks_message' . $fieldSuffix};
				}
				else
				{
					$message = $messageObj->renew_thanks_message;
				}

				if ($rowSubscriber->to_date)
				{
					$toDate = HTMLHelper::_('date', $rowSubscriber->to_date, $config->date_format);
				}
				else
				{
					$toDate = '';
				}

				$message = str_replace('[END_DATE]', $toDate, $message);
				$message = str_replace('[PLAN_TITLE]', $rowPlan->title, $message);
				break;
			case 'upgrade':
				// Use offline payment thank you message if available
				if ($useOfflinePayment)
				{
					if ($offlineSuffix && $fieldSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'upgrade_thanks_message_offline' . $offlineSuffix . $fieldSuffix}
						))
					{
						$message = $messageObj->{'upgrade_thanks_message_offline' . $offlineSuffix . $fieldSuffix};
					}
					elseif ($offlineSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'upgrade_thanks_message_offline' . $offlineSuffix}
						))
					{
						$message = $messageObj->{'upgrade_thanks_message_offline' . $offlineSuffix};
					}
					elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
							$rowPlan->{'upgrade_thanks_message_offline' . $fieldSuffix}
						))
					{
						$message = $rowPlan->{'upgrade_thanks_message_offline' . $fieldSuffix};
					}
					elseif (OSMembershipHelper::isValidMessage($rowPlan->upgrade_thanks_message_offline))
					{
						$message = $rowPlan->upgrade_thanks_message_offline;
					}
					elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'upgrade_thanks_message_offline' . $fieldSuffix}
						))
					{
						$message = $messageObj->{'upgrade_thanks_message_offline' . $fieldSuffix};
					}
					elseif (OSMembershipHelper::isValidMessage($messageObj->upgrade_thanks_message_offline))
					{
						$message = $messageObj->upgrade_thanks_message_offline;
					}
					else
					{
						$message = $messageObj->upgrade_thanks_message;
					}
				}
				elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
						$rowPlan->{'upgrade_thanks_message' . $fieldSuffix}
					))
				{
					$message = $rowPlan->{'upgrade_thanks_message' . $fieldSuffix};
				}
				elseif (OSMembershipHelper::isValidMessage($rowPlan->upgrade_thanks_message))
				{
					$message = $rowPlan->upgrade_thanks_message;
				}
				elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
						$messageObj->{'upgrade_thanks_message' . $fieldSuffix}
					))
				{
					$message = $messageObj->{'upgrade_thanks_message' . $fieldSuffix};
				}
				else
				{
					$message = $messageObj->upgrade_thanks_message;
				}

				$query->clear()
					->select('c.title')
					->from('#__osmembership_subscribers AS a')
					->innerJoin('#__osmembership_upgraderules AS b ON a.upgrade_option_id=b.id')
					->innerJoin('#__osmembership_plans AS c ON b.from_plan_id = c.id')
					->where('a.id = ' . $rowSubscriber->id);
				$db->setQuery($query);
				$fromPlan = $db->loadResult();
				$message  = str_replace('[PLAN_TITLE]', $fromPlan, $message);
				$message  = str_replace('[TO_PLAN_TITLE]', $rowPlan->title, $message);
				break;
			default:
				if ($useOfflinePayment)
				{
					if ($offlineSuffix && $fieldSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'thanks_message_offline' . $offlineSuffix . $fieldSuffix}
						))
					{
						$message = $messageObj->{'thanks_message_offline' . $offlineSuffix . $fieldSuffix};
					}
					elseif ($offlineSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'thanks_message_offline' . $offlineSuffix}
						))
					{
						$message = $messageObj->{'thanks_message_offline' . $offlineSuffix};
					}
					elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
							$rowPlan->{'thanks_message_offline' . $fieldSuffix}
						))
					{
						$message = $rowPlan->{'thanks_message_offline' . $fieldSuffix};
					}
					elseif (OSMembershipHelper::isValidMessage($rowPlan->thanks_message_offline))
					{
						$message = $rowPlan->thanks_message_offline;
					}
					elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
							$messageObj->{'thanks_message_offline' . $fieldSuffix}
						))
					{
						$message = $messageObj->{'thanks_message_offline' . $fieldSuffix};
					}
					else
					{
						$message = $messageObj->thanks_message_offline;
					}
				}
				elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($rowPlan->{'thanks_message' . $fieldSuffix}))
				{
					$message = $rowPlan->{'thanks_message' . $fieldSuffix};
				}
				elseif (OSMembershipHelper::isValidMessage($rowPlan->thanks_message))
				{
					$message = $rowPlan->thanks_message;
				}
				elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
						$messageObj->{'thanks_message' . $fieldSuffix}
					))
				{
					$message = $messageObj->{'thanks_message' . $fieldSuffix};
				}
				else
				{
					$message = $messageObj->thanks_message;
				}

				$message = str_replace('[PLAN_TITLE]', $rowPlan->title, $message);
				break;
		}

		$subscriptionDetail = OSMembershipHelper::getEmailContent($config, $rowSubscriber);
		$message            = str_replace('[SUBSCRIPTION_DETAIL]', $subscriptionDetail, $message);
		$replaces           = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'buildTags',
			[$rowSubscriber, $config]
		);

		$replaces['plan_title'] = $rowPlan->title;

		$message = OSMembershipHelper::replaceUpperCaseTags($message, $replaces);

		$message = OSMembershipHelperHtml::processConditionalText($message);

		$this->message = HTMLHelper::_('content.prepare', $message);

		$trackingCode = trim($rowPlan->conversion_tracking_code) ?: $config->conversion_tracking_code;

		if ($trackingCode)
		{
			$filterInput = InputFilter::getInstance();

			$replaces['amount']                 = $filterInput->clean($replaces['amount'], 'float');
			$replaces['discount_amount']        = $filterInput->clean($replaces['discount_amount'], 'float');
			$replaces['tax_amount']             = $filterInput->clean($replaces['tax_amount'], 'float');
			$replaces['gross_amount']           = $filterInput->clean($replaces['gross_amount'], 'float');
			$replaces['payment_processing_fee'] = $filterInput->clean($replaces['payment_processing_fee'], 'float');
			$replaces['tax_rate']               = $filterInput->clean($replaces['tax_rate'], 'float');

			$trackingCode = OSMembershipHelper::replaceUpperCaseTags($trackingCode, $replaces);
		}

		$params = $this->getParams();
		$params->def('page_title', Text::_('OSM_SUBSCRIPTION_COMPLETE_PAGE_TITLE'));

		$this->setDocumentMetadata($params);

		$this->conversionTrackingCode = $trackingCode;
		$this->rowSubscriber          = $rowSubscriber;
		$this->replaces               = $replaces;

		$this->setLayout('default');
	}
}
