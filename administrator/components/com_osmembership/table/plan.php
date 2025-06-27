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
 * Plan table
 *
 * @property $id
 * @property $title
 * @property $price
 * @property $setup_fee
 * @property $currency
 * @property $subscription_length
 * @property $subscription_length_unit
 * @property $lifetime_membership
 * @property $expired_date
 * @property $prorated_signup_cost
 * @property $recurring_subscription
 * @property $trial_duration
 * @property $trial_duration_unit
 * @property $trial_amount
 * @property $number_payments
 * @property $free_plan_subscription_status
 * @property $params
 */

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class OSMembershipTablePlan extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__osmembership_plans', 'id', $db);
	}
}
