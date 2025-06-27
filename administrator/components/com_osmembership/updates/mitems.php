<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

return [
	[
		'name'     => 'invite_group_members_email_subject',
		'title'    => 'Default Join Group Invitation Email Subject',
		'title_en' => 'Default Join Group Invitation Email Subject',
		'type'     => 'text',
		'group'    => '6',
	],
	[
		'name'     => 'invite_group_members_message',
		'title'    => 'Default Join Group Invitation Email Message',
		'title_en' => 'Default Join Group Invitation Email Message',
		'type'     => 'editor',
		'group'    => '6',
	],
	[
		'name'     => 'admin_new_group_member_email_subject',
		'title'    => 'Admin New Group Member Added Email Subject',
		'title_en' => 'Admin New Group Member Added Email Subject',
		'type'     => 'text',
		'group'    => '6',
	],
	[
		'name'     => 'admin_new_group_member_email_body',
		'title'    => 'Admin New Group Member Added Email Body',
		'title_en' => 'Admin New Group Member Added Email Body',
		'type'     => 'editor',
		'group'    => '6',
	],
	[
		'name'         => 'offline_payment_reminder_email_subject',
		'title'        => 'Offline Payment Reminder Email Subject',
		'title_en'     => 'Offline Payment Reminder Email Subject',
		'type'         => 'text',
		'group'        => '5',
		'translatable' => 1,
	],
	[
		'name'         => 'offline_payment_reminder_email_body',
		'title'        => 'Offline Payment Reminder Email Body',
		'title_en'     => 'Offline Payment Reminder Email Body',
		'type'         => 'editor',
		'group'        => '5',
		'translatable' => 1,
	],
	[
		'name'         => 'user_email_subject_offline',
		'title'        => 'OSM_USER_EMAIL_SUBJECT_OFFLINE',
		'title_en'     => 'User Email Subject Offline Payment',
		'type'         => 'text',
		'group'        => '1',
		'translatable' => 1,
	],
	[
		'name'         => 'user_renew_email_subject_offline',
		'title'        => 'OSM_RENEW_USER_EMAIL_SUBJECT_OFFLINE',
		'title_en'     => 'Subscription Renewal User Email Subject Offline Payment',
		'type'         => 'text',
		'group'        => '2',
		'translatable' => 1,
	],
	[
		'name'         => 'user_upgrade_email_subject_offline',
		'title'        => 'OSM_UPGRADE_USER_EMAIL_SUBJECT_OFFLINE',
		'title_en'     => 'Subscription Upgrade User Email Subject Offline Payment',
		'type'         => 'text',
		'group'        => '3',
		'translatable' => 1,
	],
];