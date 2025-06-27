<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

/**
 * Subscriber table
 *
 * @property $id
 * @property $plan_id
 * @property $user_id
 * @property $coupon_id
 * @property $avatar
 * @property $first_name
 * @property $last_name
 * @property $organization
 * @property $address
 * @property $address2
 * @property $city
 * @property $state
 * @property $zip
 * @property $country
 * @property $phone
 * @property $fax
 * @property $email
 * @property $comment
 * @property $created_date
 * @property $payment_date
 * @property $from_date
 * @property $to_date
 * @property $invoice_number
 * @property $is_profile
 * @property $profile_id
 * @property $membership_id
 * @property $act
 * @property $published
 * @property $setup_fee
 * @property $tax_rate
 * @property $amount
 * @property $tax_amount
 * @property $discount_amount
 * @property $gross_amount
 * @property $payment_processing_fee
 * @property $payment_method
 * @property $transaction_id
 * @property $language
 * @property $plan_main_record
 * @property $plan_subscription_from_date
 * @property $plan_subscription_to_date
 * @property $plan_subscription_status
 * @property $subscription_id
 * @property $upgrade_option_id
 * @property $renew_option_id
 * @property $payment_currency
 * @property $trial_payment_amount
 * @property $payment_amount
 * @property $params
 * @property $group_admin_id
 * @property $first_reminder_sent
 * @property $second_reminder_sent
 * @property $third_reminder_sent
 * @property $is_free_trial
 * @property $receiver_email
 * @property $payment_made
 * @property $gateway_customer_id
 * @property $auto_subscribe_processed
 * @property $parent_id
 * @property $refunded
 * @property $process_payment_for_subscription
 */

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class OSMembershipTableSubscriber extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__osmembership_subscribers', 'id', $db);

		// Handle searchable field which are checkboxes
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('name')
			->from('#__osmembership_fields')
			->where('is_searchable = 1')
			->where('(fieldtype = "Checkboxes" OR (fieldtype="List" AND multiple = 1))');
		$db->setQuery($query);

		$this->_jsonEncode = $db->loadColumn();
	}

	public function bind($src, $ignore = [])
	{
		foreach ($this->_jsonEncode as $field)
		{
			if (isset($src[$field]) && is_array($src[$field]))
			{
				$src[$field] = json_encode($src[$field], JSON_UNESCAPED_UNICODE);
			}
		}

		$ret = parent::bind($src, $ignore);

		$inputFilter = InputFilter::getInstance();

		$amountFields = [
			'amount',
			'tax_amount',
			'discount_amount',
			'gross_amount',
			'tax_rate',
			'setup_fee',
			'payment_processing_fee',
		];

		foreach ($amountFields as $field)
		{
			$this->$field = $inputFilter->clean($this->$field, 'FLOAT');
		}

		return $ret;
	}
}
