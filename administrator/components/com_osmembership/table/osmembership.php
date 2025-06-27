<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class PlanOsMembership extends Table
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

/**
 * Subscriber table
 */
class SubscriberOSMembership extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__osmembership_subscribers', 'id', $db);
	}
}

/**
 * Fieldvalue table
 */
class FieldValueOsMembership extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__osmembership_field_value', 'id', $db);
	}
}
