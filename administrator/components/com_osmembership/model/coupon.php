<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class OSMembershipModelCoupon extends MPFModelAdmin
{
	/**
	 * @param   OSMembershipTableCoupon  $row
	 * @param   MPFInput                 $input
	 * @param   bool                     $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		parent::beforeStore($row, $input, $isNew);

		// Convert expired date from custom format to Y-m-d format
		$config     = OSMembershipHelper::getConfig();
		$dateFormat = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d')) . ' H:i:s';

		$dateFields = [
			'valid_from',
			'valid_to',
		];

		foreach ($dateFields as $field)
		{
			$dateValue = $input->getString($field);

			if (!$dateValue)
			{
				continue;
			}

			try
			{
				$date = DateTime::createFromFormat($dateFormat, $dateValue);

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
	}

	/**
	 * Store custom fields mapping with plans.
	 *
	 * @param   Table     $row
	 * @param   MPFInput  $input
	 * @param   bool      $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		$db         = $this->getDbo();
		$query      = $db->getQuery(true);
		$assignment = $input->getInt('assignment', 0);

		$planIds = array_filter(ArrayHelper::toInteger($input->get('plan_id', [], 'array')));

		if ($assignment == 0)
		{
			$row->plan_id = 0;
		}
		else
		{
			$row->plan_id = 1;
		}

		$row->store(); // Store the plan_id field

		if (!$isNew)
		{
			$query->delete('#__osmembership_coupon_plans')
				->where('coupon_id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}

		if ($row->plan_id != 0 && count($planIds))
		{
			$query->clear();

			foreach ($planIds as $planId)
			{
				$planId = $assignment * $planId;
				$query->values("$row->id, $planId");
			}

			$query->insert('#__osmembership_coupon_plans')
				->columns('coupon_id, plan_id');
			$db->setQuery($query);

			$db->execute();
		}
	}

	/**
	 * Get list of subscription records which use the current coupon code
	 *
	 * @return array
	 */
	public function getSubscriptions()
	{
		if ($this->state->id)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('id, first_name, last_name, email, created_date, amount')
				->from('#__osmembership_subscribers')
				->where('coupon_id = ' . $this->state->id)
				->order('id');
			$db->setQuery($query);

			return $db->loadObjectList();
		}

		return [];
	}

	/**
	 * @param $file
	 * @param $fileName
	 *
	 * @return int
	 * @throws Exception
	 */
	public function import($file, $fileName = '')
	{
		$coupons = OSMembershipHelperData::getDataFromFile($file, $fileName);

		// Get list of plans
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id, title')
			->from('#__osmembership_plans');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$plans = [];

		foreach ($rows as $row)
		{
			$plans[StringHelper::strtolower($row->title)] = $row->id;
		}

		$imported = 0;

		if (count($coupons))
		{
			$executeInsert = false;

			$query->clear()
				->insert('#__osmembership_coupon_plans')
				->columns(['plan_id', 'coupon_id']);

			foreach ($coupons as $coupon)
			{
				if (empty($coupon['code']) || empty($coupon['discount']))
				{
					continue;
				}

				$row = $this->getTable();

				if (!empty($coupon['id']))
				{
					$row->load($coupon['id']);
				}

				// Get plan Ids
				$planTitles = StringHelper::strtolower($coupon['plan']);
				$planTitles = explode(',', $planTitles);
				$planIds    = [];

				foreach ($planTitles as $planTitle)
				{
					$planIds[] = $plans[$planTitle] ?? 0;
				}

				$planIds = array_filter($planIds);

				if (count($planIds))
				{
					$coupon['plan_id'] = 1;
				}
				else
				{
					$coupon['plan_id'] = 0;
				}

				if ($coupon['valid_from'])
				{
					if ($coupon['valid_from'] instanceof DateTime)
					{
						$coupon ['valid_from'] = $coupon['valid_from']->format('Y-m-d');
					}
					else
					{
						$coupon ['valid_from'] = HTMLHelper::date($coupon['valid_from'], 'Y-m-d');
					}
				}
				else
				{
					$coupon ['valid_from'] = '';
				}

				if ($coupon['valid_to'])
				{
					if ($coupon['valid_to'] instanceof DateTime)
					{
						$coupon ['valid_to'] = $coupon['valid_to']->format('Y-m-d');
					}
					else
					{
						$coupon ['valid_to'] = HTMLHelper::date($coupon['valid_to'], 'Y-m-d');
					}
				}
				else
				{
					$coupon ['valid_to'] = '';
				}

				$row->bind($coupon, ['id']);
				$row->check();
				$row->store();

				if (count($planIds) > 0)
				{
					foreach ($planIds as $planId)
					{
						$query->values(implode(',', [$planId, $row->id]));
					}

					$executeInsert = true;
				}

				$imported++;
			}

			if ($executeInsert)
			{
				$db->setQuery($query)
					->execute();
			}
		}

		return $imported;
	}

	/**
	 * Generate batch coupon
	 *
	 * @param   MPFInput  $input
	 */
	public function batch($input)
	{
		$db            = $this->getDbo();
		$query         = $db->getQuery(true);
		$numberCoupon  = $input->getInt('number_coupon', 50);
		$charactersSet = $input->getString('characters_set');
		$prefix        = $input->getString('prefix');
		$length        = $input->getInt('length');
		$assignment    = $input->getInt('assignment', 0);

		if (!$length)
		{
			$length = 20;
		}

		$data                      = [];
		$data['discount']          = $input->getFloat('discount', 0);
		$data['coupon_type']       = $input->getInt('coupon_type', 0);
		$data['times']             = $input->getInt('times', 0);
		$data['apply_for']         = $input->getInt('apply_for', 0);
		$data['subscription_type'] = $input->getString('subscription_type', '');
		$data['note']              = $input->getString('note', '');
		$data['assignment']        = $assignment;

		$planIds = array_filter(ArrayHelper::toInteger($input->get('plan_id', [], 'array')));

		if ($assignment == 0)
		{
			$data['plan_id'] = 0;
		}
		else
		{
			$data['plan_id'] = 1;
		}

		// Convert expired date from custom format to Y-m-d format
		$config     = OSMembershipHelper::getConfig();
		$dateFormat = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d')) . ' H:i:s';

		$dateFields = [
			'valid_from',
			'valid_to',
		];

		foreach ($dateFields as $field)
		{
			$dateValue    = $input->getString($field);
			$data[$field] = $dateValue;

			if (!$dateValue)
			{
				continue;
			}

			try
			{
				$date = DateTime::createFromFormat($dateFormat, $dateValue);

				if ($date !== false)
				{
					$data[$field] = $date->format('Y-m-d H:i:s');
				}
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}

		$data['used']       = 0;
		$data ['published'] = $input->getInt('published');

		for ($i = 0; $i < $numberCoupon; $i++)
		{
			$salt       = static::genRandomCoupon($length, $charactersSet);
			$couponCode = $prefix . $salt;

			/* @var OSMembershipTableCoupon $row */
			$row          = $this->getTable();
			$data['code'] = $couponCode;
			$row->bind($data);
			$row->check();
			$row->store();

			if ($row->plan_id != 0 && count($planIds))
			{
				$query->clear();

				foreach ($planIds as $planId)
				{
					$query->values("$row->id, $planId");
				}

				$query->insert('#__osmembership_coupon_plans')
					->columns('coupon_id, plan_id');
				$db->setQuery($query);

				$db->execute();
			}
		}
	}

	/**
	 * Generate random Coupon
	 *
	 * @param   int     $length
	 * @param   string  $charactersSet
	 *
	 * @return string
	 */
	private static function genRandomCoupon($length = 8, $charactersSet = '')
	{
		$salt = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		if ($charactersSet)
		{
			$salt = $charactersSet;
		}

		$base     = strlen($salt);
		$makePass = '';

		/*
		 * Start with a cryptographic strength random string, then convert it to
		 * a string with the numeric base of the salt.
		 * Shift the base conversion on each character so the character
		 * distribution is even, and randomize the start shift so it's not
		 * predictable.
		 */
		$random = Crypt::genRandomBytes($length + 1);
		$shift  = ord($random[0]);

		for ($i = 1; $i <= $length; ++$i)
		{
			$makePass .= $salt[($shift + ord($random[$i])) % $base];
			$shift    += ord($random[$i]);
		}

		return $makePass;
	}

	/**
	 * Delete coupon relation data after coupon deleted
	 *
	 * @param   array  $cid
	 */
	protected function afterDelete($cid)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->delete('#__osmembership_coupon_plans')
			->whereIn('coupon_id', $cid);
		$db->setQuery($query)
			->execute();
	}
}
