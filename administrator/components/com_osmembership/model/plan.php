<?php
/**
 * @package         Joomla
 * @subpackage      Membership Pro
 * @author          Tuan Pham Ngoc
 * @copyright       Copyright (C) 2012 - 2025 Ossolution Team
 * @license         GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use OSSolution\MembershipPro\Admin\Event\Plans\PlansAfterDelete;

/**
 * OSemmbership Component Plan Model
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipModelPlan extends MPFModelAdmin
{
	/**
	 * This model process events, so we need to set triggerEvents to true
	 *
	 * @var bool
	 */
	protected $triggerEvents = true;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = [])
	{
		$config['event_after_save'] = 'onAfterSaveSubscriptionPlan';

		parent::__construct($config);
	}

	/**
	 * Initialize the plan data for adding new record
	 */
	protected function initData()
	{
		parent::initData();

		$this->data->enable_renewal               = 1;
		$this->data->activate_member_card_feature = 1;
		$this->data->subscription_length_unit     = 'M';
	}

	/**
	 * Pre-process data, delete old thumbnail if required, upload new thumbnail
	 *
	 * @param             $row
	 * @param   MPFInput  $input
	 * @param   bool      $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		// Remove space character in PayPal payment plugin parameters
		$input->set('paypal_email', trim($input->getString('paypal_email')));

		// For recurring subscriptions, it could not be lifetime subscription and fixed expiration date
		if ($input->getInt('recurring_subscription'))
		{
			$input->set('lifetime_membership', 0);
			$input->set('expired_date', '');
		}

		// Delete the old thumbnail if required
		$thumbPath = JPATH_ROOT . '/media/com_osmembership/';

		if (!$isNew && $input->has('del_thumb') && $row->thumb)
		{
			if (is_file(Path::clean($thumbPath . $row->thumb)))
			{
				File::delete($thumbPath . $row->thumb);
			}

			$input->set('thumb', '');
		}

		// Process uploading thumb image
		$thumbImage = $input->files->get('thumb_image');

		if ($thumbImage['name'])
		{
			$fileExt        = StringHelper::strtoupper(OSMembershipHelper::getFileExt($thumbImage['name']));
			$supportedTypes = ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP', 'BMP'];

			if (in_array($fileExt, $supportedTypes))
			{
				if (is_file(Path::clean($thumbPath . StringHelper::strtolower($thumbImage['name']))))
				{
					$fileName = time() . '_' . StringHelper::strtolower($thumbImage['name']);
				}
				else
				{
					$fileName = StringHelper::strtolower($thumbImage['name']);
				}

				$fileName = File::makeSafe($fileName);

				File::upload($_FILES['thumb_image']['tmp_name'], $thumbPath . $fileName);

				$input->set('thumb', $fileName);
			}
		}

		$paymentMethods = $input->get('payment_methods', [], 'array');

		if (empty($paymentMethods[0]))
		{
			$input->set('payment_methods', '');
		}
		else
		{
			$input->set('payment_methods', implode(',', $paymentMethods));
		}

		$input->set(
			'send_first_reminder',
			$input->getInt('send_first_reminder') * $input->getInt('send_first_reminder_time', 1)
		);
		$input->set(
			'send_second_reminder',
			$input->getInt('send_second_reminder') * $input->getInt('send_second_reminder_time', 1)
		);
		$input->set(
			'send_third_reminder',
			$input->getInt('send_third_reminder') * $input->getInt('send_third_reminder_time', 1)
		);

		if ($input->exists('send_fourth_reminder'))
		{
			$input->set(
				'send_fourth_reminder',
				$input->getInt('send_fourth_reminder') * $input->getInt('send_fourth_reminder_time', 1)
			);
		}

		if ($input->exists('send_fifth_reminder'))
		{
			$input->set(
				'send_fifth_reminder',
				$input->getInt('send_fifth_reminder') * $input->getInt('send_fifth_reminder_time', 1)
			);
		}

		if ($input->exists('send_sixth_reminder'))
		{
			$input->set(
				'send_sixth_reminder',
				$input->getInt('send_sixth_reminder') * $input->getInt('send_sixth_reminder_time', 1)
			);
		}

		// Convert expired date from custom format to Y-m-d format
		$config     = OSMembershipHelper::getConfig();
		$dateFormat = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d'));

		if ($expiredDate = $input->getString('expired_date'))
		{
			try
			{
				$date = DateTime::createFromFormat($dateFormat, $expiredDate);

				if ($date !== false)
				{
					$input->set('expired_date', $date->format('Y-m-d'));
				}
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}

		$dateTimeFormat = $dateFormat . ' H:i:s';
		$dateTimeFields = [
			'publish_up',
			'publish_down',
		];

		foreach ($dateTimeFields as $field)
		{
			$dateValue = $input->getString($field);

			if (!$dateValue)
			{
				continue;
			}

			try
			{
				$date = DateTime::createFromFormat($dateTimeFormat, $dateValue);

				if ($date !== false)
				{
					$input->set($field, $date->format('Y-m-d H:i:s'));
				}
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}

		// Store plan custom fields
		if ($input->has('fields'))
		{
			$customFields = $input->get('fields', [], 'array');

			$input->set('custom_fields', json_encode($customFields, JSON_UNESCAPED_UNICODE));
		}

		// Make sure there is only one field to control number of group members
		$numberMembersType = $input->getInt('number_members_type', 0);

		if ($numberMembersType > 0)
		{
			$input->set('number_group_members', 0);
		}
		else
		{
			$input->set('number_members_field', 0);
		}

		$params = new Registry($row->params);
		$params->set('subscription_start_date_option', $input->get('subscription_start_date_option', '0'));
		$params->set('subscription_start_date_field', $input->get('subscription_start_date_field', ''));
		$params->set('subscription_start_date', $input->get('subscription_start_date', ''));
		$params->set('number_fields_per_row', $input->getInt('number_fields_per_row', 0));
		$params->set('subscription_form_layout', $input->getString('subscription_form_layout', ''));
		$params->set('form_format', $input->getString('form_format', ''));

		$row->params = $params->toString();
	}

	/**
	 * Store extra data like renew options, upgrade options
	 *
	 * @param   OSMembershipTablePlan  $row
	 * @param   MPFInput               $input
	 * @param   bool                   $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		$data = $input->getData();

		// Store renew options, renewal discounts and upgrade options for the plan
		$this->storeRenewOptions($row, $data, $isNew);
		$this->storeEarlyRenewalDiscounts($row, $data, $isNew);
		$this->storeUpgradeOptions($row, $data, $isNew);
	}

	/**
	 * Store plan renew options
	 *
	 * @param   OSMembershipTablePlan  $row
	 * @param   array                  $data
	 * @param   bool                   $isNew
	 */
	protected function storeRenewOptions($row, $data, $isNew)
	{
		$db             = $this->getDbo();
		$renewOptions   = isset($data['renew_options']) && is_array(
			$data['renew_options']
		) ? $data['renew_options'] : [];
		$renewOptionIds = [];
		$ordering       = 0;

		foreach ($renewOptions as $renewOption)
		{
			if (!isset($renewOption['renew_option_length']) || $renewOption['renew_option_length'] <= 0)
			{
				continue;
			}

			// Prevent renewOptions data being moved to new event on saveAsCopy
			if ($isNew)
			{
				$renewOption['id'] = 0;
			}

			/* @var OSMembershipTableRenewoption $rowRenewOption */
			$rowRenewOption = new OSMembershipTableRenewoption($db);
			$rowRenewOption->bind($renewOption);

			$length = $renewOption['renew_option_length'];

			switch ($renewOption['renew_option_length_unit'])
			{
				case 'W':
					$numberDays = $length * 7;
					break;
				case 'M':
					$numberDays = $length * 30;
					break;
				case 'Y':
					$numberDays = $length * 365;
					break;
				default:
					$numberDays = $length;
					break;
			}

			$rowRenewOption->number_days = $numberDays;
			$rowRenewOption->plan_id     = $row->id;
			$rowRenewOption->ordering    = $ordering++;
			$rowRenewOption->store();
			$renewOptionIds[] = $rowRenewOption->id;
		}

		if (!$isNew)
		{
			$query = $db->getQuery(true);
			$query->delete('#__osmembership_renewrates')
				->where('plan_id = ' . $row->id);

			if (count($renewOptionIds))
			{
				$query->whereNotIn('id', $renewOptionIds);
			}

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Store early renewal discounts options
	 *
	 * @param   OSMembershipTablePlan  $row
	 * @param   array                  $data
	 * @param   bool                   $isNew
	 */
	protected function storeEarlyRenewalDiscounts($row, $data, $isNew)
	{
		$renewalDiscounts   = isset($data['renewal_discounts']) && is_array(
			$data['renewal_discounts']
		) ? $data['renewal_discounts'] : [];
		$renewalDiscountIds = [];
		$db                 = $this->getDbo();

		foreach ($renewalDiscounts as $renewalDiscount)
		{
			if (!isset($renewalDiscount['number_days']) || $renewalDiscount['number_days'] === '')
			{
				continue;
			}

			// Prevent renewOptions data being moved to new event on saveAsCopy
			if ($isNew)
			{
				$renewalDiscount['id'] = 0;
			}

			$rowRenewalDiscount = new OSMembershipTableRenewaldiscount($db);
			$rowRenewalDiscount->bind($renewalDiscount);
			$rowRenewalDiscount->plan_id = $row->id;
			$rowRenewalDiscount->store();
			$renewalDiscountIds[] = $rowRenewalDiscount->id;
		}

		if (!$isNew)
		{
			$query = $db->getQuery(true);
			$query->delete('#__osmembership_renewaldiscounts')
				->where('plan_id = ' . $row->id);

			if (count($renewalDiscountIds))
			{
				$query->whereNotIn('id', $renewalDiscountIds);
			}

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Store upgrade options
	 *
	 * @param   OSMembershipTablePlan  $row
	 * @param   array                  $data
	 * @param   bool                   $isNew
	 */
	protected function storeUpgradeOptions($row, $data, $isNew)
	{
		$db               = $this->getDbo();
		$upgradeOptions   = isset($data['upgrade_options']) && is_array(
			$data['upgrade_options']
		) ? $data['upgrade_options'] : [];
		$upgradeOptionIds = [];
		$ordering         = 0;

		foreach ($upgradeOptions as $upgradeOption)
		{
			if (!isset($upgradeOption['to_plan_id']) || $upgradeOption['to_plan_id'] <= 0)
			{
				continue;
			}

			// Prevent renewOptions data being moved to new event on saveAsCopy
			if ($isNew)
			{
				$upgradeOption['id'] = 0;
			}

			$rowUpgradeOption = new OSMembershipTableUpgradeoption($db);
			$rowUpgradeOption->bind($upgradeOption);
			$rowUpgradeOption->from_plan_id = $row->id;
			$rowUpgradeOption->ordering     = $ordering++;
			$rowUpgradeOption->store();
			$upgradeOptionIds[] = $rowUpgradeOption->id;
		}

		if (!$isNew)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__osmembership_upgraderules')
				->where('from_plan_id = ' . $row->id);

			if (count($upgradeOptionIds))
			{
				$query->whereNotIn('id', $upgradeOptionIds);
			}

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Delete the related records before deleting the actual record
	 *
	 * @param   array  $cid
	 */
	protected function beforeDelete($cid)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__osmembership_articles')
			->whereIn('plan_id', $cid);
		$db->setQuery($query);
		$db->execute();

		//Delete from URL tables as well
		if (PluginHelper::isEnabled('osmembership', 'urls'))
		{
			$query->clear()
				->delete('#__osmembership_urls')
				->whereIn('plan_id', $cid);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Override delete method to trigger onPlansAfterDelete for action logs
	 *
	 * @param   array  $cid
	 *
	 * @throws Exception
	 */
	public function delete($cid = [])
	{
		parent::delete($cid);

		$event = new PlansAfterDelete(['context' => $this->context, 'pks' => $cid]);

		Factory::getApplication()->triggerEvent($event->getName(), $event);
	}
}
