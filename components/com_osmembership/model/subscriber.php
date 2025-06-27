<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register(
	'OSMembershipModelSubscription',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/subscription.php'
);

class OSMembershipModelSubscriber extends OSMembershipModelSubscription
{
}
