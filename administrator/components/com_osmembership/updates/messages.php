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
	'new_subscription_admin_sms'                => 'User [FIRST_NAME] [LAST_NAME] subscribed for plan [PLAN_TITLE].',
	'new_subscription_renewal_admin_sms'        => 'User [FIRST_NAME] [LAST_NAME] renewed his/her subscription for for plan [PLAN_TITLE].',
	'new_subscription_upgrade_admin_sms'        => 'User [FIRST_NAME] [LAST_NAME] upgraded his/her subscription from [FROM_PLAN_TITLE] to [PLAN_TITLE].',
	'first_reminder_sms'                        => 'First Reminder: Your subscription for [PLAN_TITLE] will be expired on [EXPIRE_DATE].',
	'second_reminder_sms'                       => 'Second Reminder: Your subscription for [PLAN_TITLE] will be expired on [EXPIRE_DATE].',
	'third_reminder_sms'                        => 'Third Reminder: Your subscription for [PLAN_TITLE] will be expired on [EXPIRE_DATE].',
	'subscription_payment_form_message'         => '<p>Please enter information on the form below to process payment for subscription <strong>#[ID]</strong> for plan <strong>[PLAN_TITLE]</strong></p>',
	'subscription_payment_admin_email_subject'  => 'Payment For Subscription #[ID] was completed',
	'subscription_payment_admin_email_body'     => '<p>Dear Administrator</p>
<p>Payment for subscription <strong>#[ID] </strong>was completed. Below is subscription details:</p>
<p>[SUBSCRIPTION_DETAIL]</p>
<p>Regards,</p>
<p>Website Administrator Team</p>',
	'subscription_payment_user_email_subject'   => 'Payment For Subscription #[ID] Confirmation',
	'subscription_payment_user_email_body'      => '<p>Dear <strong>[FIRST_NAME] [LAST_NAME]</strong></p>
<p>Payment for your subscription #[ID] for plan [PLAN_TITLE] was completed. You now have an active subscription and can access to restricted resource on our website. Below is the subscription detail:</p>
<p>[SUBSCRIPTION_DETAIL]</p>
<p>Regards,</p>
<p>Website Administrator Team</p>',
	'subscription_payment_thanks_message'       => '<p>Thanks for completing payment for subscription #[ID] for plan [PLAN_TITLE]. The subscription is now active and subscriber can access to restricted resource on our website.</p>
<p>Regards,</p>
<p>Website Administrator Team</p>',
	'request_payment_email_subject'             => 'Payment Request For Subscription For Plan [PLAN_TITLE]',
	'request_payment_email_body'                => '<p>Dear [FIRST_NAME] [LAST_NAME]</p>
<p>Thanks for subscribing for our plan [PLAN_TITLE]. You haven\'t made payment for the subscription yet, so please click on this link [PAYMENT_LINK] to process payment for the subscription. The amount you have to pay is [GROSS_AMOUNT]. Once payment is processed, your subscription will become active and you can start accessing to restricted resources on our website.</p>
<p>Regards,</p>
<p>Website Administrator Team</p>',
	'join_group_form_message'                   => '<p>Please enter information in the form below to become a group member of plan <strong>[PLAN_TITLE] </strong>by group admin <strong>[GROUP_ADMIN_NAME]</strong></p>',
	'join_group_complete_message'               => '<p>Dear [FIRST_NAME] [LAST_NAME]</p>
<p>You have just registered as a group member of plan [PLAN_TITLE] by group admin [GROUP_ADMIN_NAME]</p>
<p>You can now login to your account on our website to restricted resources available to subscribers of that plan</p>
<p>Regards,</p>
<p>Company Name</p>',
	'join_group_user_email_subject'             => 'You are now a group member of plan [PLAN_TITLE]',
	'join_group_user_email_body'                => '<p>Dear [FIRST_NAME] [LAST_NAME]</p>
<p>You have just registered as a group member of plan [PLAN_TITLE] by group admin [GROUP_ADMIN_NAME]</p>
<p>You can now login to your account on our website to restricted resources available to subscribers of that plan</p>
<p>Regards,</p>
<p>Company Name</p>',
	'join_group_group_admin_email_subject'      => '[FIRST_NAME] [LAST_NAME] joined your group of plan [PLAN_TITLE]',
	'join_group_group_admin_email_body'         => '<p>Dear [GROUP_ADMIN_NAME]</p>
<p>User [FIRST_NAME] [LAST_NAME] just registered to become group member of plan [PLAN_TITLE] which you are group admin. Below is the details information of the subscription:</p>
<p>[SUBSCRIPTION_DETAIL]</p>
<p>Regards,</p>
<p>Company Name</p>',
	'admin_subscription_approved_email_subject' => '',
	'admin_subscription_approved_email_body'    => '<p>Dear Administrator</p>
<p>User <strong>[APPROVAL_USERNAME]</strong> has just approved the subscription of <strong>[FIRST_NAME] [LAST_NAME]</strong> for plan <strong>[PLAN_TITLE]</strong>. The subscription detail is as follow:</p>
<p>[SUBSCRIPTION_DETAIL]</p>
<p>Regards,</p>
<p>Company Name</p>',
	'invite_group_members_email_subject'        => 'Invitation to join my group',
	'invite_group_members_message'              => '<p>Hello</p>
<p>I would like to invite you to join my group membership for plan [PLAN_TITLE]. Please click on the the link [JOIN_GROUP_LINK] to join my group.</p>',
	'admin_new_group_member_email_subject'      => 'New Group Member Added To Plan [PLAN_TITLE] By [GROUP_ADMIN_NAME]',
	'admin_new_group_member_email_body'         => '<p>Dear Administrator</p>
<p>New Group Member [FIRST_NAME] [LAST_NAME] was added to plan [PLAN_TITLE] by group admin [GROUP_ADMIN_NAME]</p>
<p>Regards,</p>
<p>[COMPANY_NAME]</p>',
	'offline_payment_reminder_email_subject'      => 'Offline Payment reminder for your subscription for plan [PLAN_TITLE]',
	'offline_payment_reminder_email_body'         => '<p>Dear <strong>[FIRST_NAME], [LAST_NAME]</strong></p>
<p>You subscribed for our subscription plan <strong>[PLAN_TITLE]</strong> using offline payment method but has not made payment yet. The payment amount is <strong>[GROSS_AMOUNT].</strong></p>
<p>Please send the offline payment via our bank account. Information of our bank account is as follows:</p>
<p><strong>Account Holder Name, Bank Name, Account Number XXXYYYZZZZ</strong></p>
<p>Regards,</p>
<p>Website Administrator Team</p>',
];