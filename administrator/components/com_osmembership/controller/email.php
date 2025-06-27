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

/**
 * Membership Pro controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipControllerEmail extends OSMembershipController
{
	public function delete_all()
	{
		/* @var DatabaseDriver $db */
		Factory::getContainer()->get('db')->truncateTable('#__osmembership_emails');

		$this->setRedirect('index.php?option=com_osmembership&view=emails');
	}
}
