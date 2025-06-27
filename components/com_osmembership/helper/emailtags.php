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
use Joomla\Database\DatabaseDriver;

class OSMembershipHelperEmailtags
{
	/**
	 * Get tags related to event information
	 *
	 * @return array
	 */
	public static function getPlanTags()
	{
		$tags = [
			'plan_short_description',
			'plan_description',
			'plan_id',
			'plan_title',
			'plan_alias',
			'plan_price',
			'plan_duration',
			'plan_duration',
			'category',
			'PLAN_URL',
		];

		if (file_exists(JPATH_ROOT . '/components/com_osmembership/fields.xml')
			&& filesize(JPATH_ROOT . '/components/com_osmembership/fields.xml') > 0)
		{
			$xml = simplexml_load_file(JPATH_ROOT . '/components/com_osmembership/fields.xml');

			if ($xml !== false)
			{
				$fields = $xml->fields->fieldset->children();

				foreach ($fields as $field)
				{
					$tags[] = (string) $field->attributes()->name;
				}
			}
		}

		return array_map('strtoupper', $tags);
	}

	/**
	 * Get tags related to subscrition information
	 *
	 * @return array
	 */
	public static function getSubscriptionTags()
	{
		$tags = [
			'SUBSCRIPTION_DETAIL',
			'id',
			'user_id',
			'profile_id',
			'name',
			'country_code',
			'subscription_id',
			'amount',
			'discount_amount',
			'tax_amount',
			'gross_amount',
			'payment_processing_fee',
			'currency',
			'amount_with_currency',
			'discount_amount_with_currency',
			'tax_amount_with_currency',
			'gross_amount_with_currency',
			'payment_processing_fee_with_currency',
			'tax_rate',
			'from_date',
			'to_date',
			'end_date',
			'created_date',
			'created_hour',
			'created_minute',
			'date', // Show current date
			'published',
			'payment_method_name',
			'payment_method', // Show title of payment method
			'vies_registered',
			'payment_link',
			'payment_date',
			'free_tax_rate_text',
			'avatar',
			'username',
			'coupon_code',
			'transaction_id',
			'membership_id',
			'item_name',
			'invoice_number',
			'refunded',
			'subscription_status',
			'plan_subscription_status',
			'payment_status',
			'total_payment_amount',
			'join_group_link',
			'group_members',
			'user_ip',
		];

		// Get list of custom fields
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('name')
			->from('#__osmembership_fields')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$tags = array_merge($db->loadColumn(), $tags);

		return array_map('strtoupper', $tags);
	}

	/**
	 * Get tags which can be used in SMS messages
	 *
	 * @return array
	 */
	public static function getSMSTags()
	{
		static $tags;

		if ($tags !== null)
		{
			return $tags;
		}

		$tags = [
			'plan_id',
			'plan_title',
		];

		// Get list of custom fields
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('name')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('id < 13')
			->order('ordering');
		$db->setQuery($query);
		$tags = array_merge($tags, $db->loadColumn());

		$tags = array_merge($tags, [
			'from_date',
			'to_date',
			'created_date',
			'end_date',
			'from_plan_title', // For upgrade subscription only
		]);

		return array_map('strtoupper', $tags);
	}
}
