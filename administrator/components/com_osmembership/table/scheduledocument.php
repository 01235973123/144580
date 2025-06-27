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
 * OSMembershipTableScheduleDocument table
 *
 * @property $id
 * @property $plan_id
 * @property $number_days
 * @property $ordering
 */

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class OSMembershipTableScheduledocument extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__osmembership_scheduledocuments', 'id', $db);
	}
}
