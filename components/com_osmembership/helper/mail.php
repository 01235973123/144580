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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use OSSolution\MembershipPro\Admin\Event\Mail\BeforeSendingEmail;

class OSMembershipHelperMail
{
	/**
	 * Send email to super administrator and user
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   MPFConfig                    $config
	 */
	public static function sendEmails($row, $config)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendEmails'))
		{
			OSMembershipHelperOverrideMail::sendEmails($row, $config);

			return;
		}

		/* @var DatabaseDriver $db */
		$db          = Factory::getContainer()->get('db');
		$query       = $db->getQuery(true);
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		$query->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		if ($plan->category_id > 0)
		{
			$category = OSMembershipHelperDatabase::getCategory($plan->category_id);

			OSMembershipHelper::setPlanMessagesDataFromCategory($plan, $category, [
				'user_email_body',
				'user_email_body_offline',
				'admin_email_body',
				'user_renew_email_body',
				'user_renew_email_body_offline',
				'admin_renew_email_body',
				'user_upgrade_email_body',
				'user_upgrade_email_body_offline',
				'admin_upgrade_email_body',
			]);
		}

		if ($plan->notification_emails)
		{
			$config->notification_emails = $plan->notification_emails;
		}

		$mailer = static::getMailer($config);

		$message = OSMembershipHelper::getMessages();

		if ($row->act == 'upgrade')
		{
			static::sendMembershipUpgradeEmails($mailer, $row, $plan, $config, $message, $fieldSuffix);

			return;
		}

		if ($row->act == 'renew')
		{
			static::sendMembershipRenewalEmails($mailer, $row, $plan, $config, $message, $fieldSuffix);

			return;
		}

		$logEmails = static::loggingEnabled('new_subscription_emails', $config);

		$rowFields    = OSMembershipHelper::getProfileFields($row->plan_id);
		$emailContent = OSMembershipHelper::getEmailContent($config, $row, false, 'register');
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		$isOfflinePaymentSubscription = str_contains($row->payment_method, 'os_offline') && $row->published == 0;

		// New Subscription Email Subject
		if ($fieldSuffix && $isOfflinePaymentSubscription
			&& trim($message->{'user_email_subject_offline' . $fieldSuffix}))
		{
			$subject = $message->{'user_email_subject_offline' . $fieldSuffix};
		}
		elseif ($isOfflinePaymentSubscription && trim($message->user_email_subject_offline))
		{
			$subject = $message->user_email_subject_offline;
		}
		elseif ($fieldSuffix && trim($plan->{'user_email_subject' . $fieldSuffix}))
		{
			$subject = $plan->{'user_email_subject' . $fieldSuffix};
		}
		elseif ($fieldSuffix && trim($message->{'user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_email_subject' . $fieldSuffix};
		}
		elseif (trim($plan->user_email_subject))
		{
			$subject = $plan->user_email_subject;
		}
		else
		{
			$subject = $message->user_email_subject;
		}

		// New Subscription Email Body
		if ($isOfflinePaymentSubscription)
		{
			$offlineSuffix = str_replace('os_offline', '', $row->payment_method);
			if ($offlineSuffix && $fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_email_body_offline' . $offlineSuffix . $fieldSuffix}
				))
			{
				$body = $message->{'user_email_body_offline' . $offlineSuffix . $fieldSuffix};
			}
			elseif ($offlineSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_email_body_offline' . $offlineSuffix}
				))
			{
				$body = $message->{'user_email_body_offline' . $offlineSuffix};
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$plan->{'user_email_body_offline' . $fieldSuffix}
				))
			{
				$body = $plan->{'user_email_body_offline' . $fieldSuffix};
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_email_body_offline' . $fieldSuffix}
				))
			{
				$body = $message->{'user_email_body_offline' . $fieldSuffix};
			}
			elseif (OSMembershipHelper::isValidMessage($plan->user_email_body_offline))
			{
				$body = $plan->user_email_body_offline;
			}
			else
			{
				$body = $message->user_email_body_offline;
			}
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($plan->{'user_email_body' . $fieldSuffix}))
		{
			$body = $plan->{'user_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'user_email_body' . $fieldSuffix}))
		{
			$body = $message->{'user_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->user_email_body))
		{
			$body = $plan->user_email_body;
		}
		else
		{
			$body = $message->user_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		$invoicePath = '';

		if ($row->invoice_number > 0
			&& ($config->send_invoice_to_customer || $config->send_invoice_to_admin))
		{
			$invoicePath = OSMembershipHelper::generateAndReturnInvoicePath($row);
		}

		if ($config->send_invoice_to_customer && $invoicePath)
		{
			$mailer->addAttachment($invoicePath);
		}

		// Generate and send member card to subscriber email
		if ($config->send_member_card_via_email
			&& $plan->activate_member_card_feature
			&& $row->published == 1)
		{
			$path = OSMembershipHelperSubscription::generatePlanMemberCard($row, $config);
			$mailer->addAttachment($path);
		}

		// Add documents from plan to subscription confirmation email if subscription is active
		if ($row->published == 1)
		{
			static::addSubscriptionDocuments($mailer, $row);
		}

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send(
				$mailer,
				array_merge([$row->email], self::getEmailsFromSubscriptionData($rowFields, $replaces)),
				$subject,
				$body,
				$logEmails,
				2,
				'new_subscription_emails',
				$row
			);

			$mailer->clearAllRecipients();
		}

		$mailer->clearAttachments();

		if ($config->send_invoice_to_admin && $invoicePath)
		{
			$mailer->addAttachment($invoicePath);
		}

		$emails = explode(',', $config->notification_emails);

		if ($fieldSuffix && strlen($message->{'admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_email_subject;
		}

		$subject = str_replace('[PLAN_TITLE]', $plan->title, $subject);

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'admin_email_body' . $fieldSuffix}))
		{
			$body = $message->{'admin_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->admin_email_body))
		{
			$body = $plan->admin_email_body;
		}
		else
		{
			$body = $message->admin_email_body;
		}

		$emailContent = OSMembershipHelper::getEmailContent($config, $row, true, 'register');

		$body = str_replace('[SUBSCRIPTION_DETAIL]', $emailContent, $body);

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if ($config->send_attachments_to_admin)
		{
			self::addAttachments($mailer, $rowFields, $replaces);
		}

		if ($row->email && MailHelper::isEmailAddress($row->email))
		{
			$mailer->addReplyTo($row->email);
		}

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'new_subscription_emails', $row);

		//After sending email, we can empty the user_password of subscription was activated
		if ($row->published == 1 && $row->user_password)
		{
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('user_password = ""')
				->where('id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Send email when subscriber upgrade their membership
	 *
	 * @param   Mail                         $mailer
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   stdClass                     $plan
	 * @param   MPFConfig                    $config
	 * @param   MPFConfig                    $message
	 * @param   string                       $fieldSuffix
	 */
	public static function sendMembershipRenewalEmails($mailer, $row, $plan, $config, $message, $fieldSuffix)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendMembershipRenewalEmails'))
		{
			OSMembershipHelperOverrideMail::sendMembershipRenewalEmails(
				$mailer,
				$row,
				$plan,
				$config,
				$message,
				$fieldSuffix
			);

			return;
		}

		if ($row->renew_option_id == OSM_DEFAULT_RENEW_OPTION_ID)
		{
			$numberDays = $plan->subscription_length;
		}
		else
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('number_days')
				->from('#__osmembership_renewrates')
				->where('id = ' . $row->renew_option_id);
			$db->setQuery($query);
			$numberDays = $db->loadResult();
		}

		$logEmails = static::loggingEnabled('subscription_renewal_emails', $config);

		// Get list of fields
		$rowFields = OSMembershipHelper::getProfileFields($row->plan_id);

		$emailContent = OSMembershipHelper::getEmailContent($config, $row, false, 'register');
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['number_days']         = $numberDays;
		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		$isOfflinePaymentSubscription = str_contains($row->payment_method, 'os_offline') && $row->published == 0;

		// Subscription Renewal Email Subject
		if ($fieldSuffix && $isOfflinePaymentSubscription
			&& trim($message->{'user_renew_email_subject_offline' . $fieldSuffix}))
		{
			$subject = $message->{'user_renew_email_subject_offline' . $fieldSuffix};
		}
		elseif ($isOfflinePaymentSubscription && trim($message->user_renew_email_subject_offline))
		{
			$subject = trim($message->user_renew_email_subject_offline);
		}
		elseif ($fieldSuffix && trim($plan->{'user_renew_email_subject' . $fieldSuffix}))
		{
			$subject = $plan->{'user_renew_email_subject' . $fieldSuffix};
		}
		elseif ($fieldSuffix && trim($message->{'user_renew_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_renew_email_subject' . $fieldSuffix};
		}
		elseif (trim($plan->user_renew_email_subject))
		{
			$subject = $plan->user_renew_email_subject;
		}
		else
		{
			$subject = $message->user_renew_email_subject;
		}

		// Subscription Renewal Email Body
		if ($isOfflinePaymentSubscription)
		{
			$offlineSuffix = str_replace('os_offline', '', $row->payment_method);
			if ($offlineSuffix && $fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_renew_email_body_offline' . $offlineSuffix . $fieldSuffix}
				))
			{
				$body = $message->{'user_renew_email_body_offline' . $offlineSuffix . $fieldSuffix};
			}
			elseif ($offlineSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_renew_email_body_offline' . $offlineSuffix}
				))
			{
				$body = $message->{'user_renew_email_body_offline' . $offlineSuffix};
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$plan->{'user_renew_email_body_offline' . $fieldSuffix}
				))
			{
				$body = $plan->{'user_renew_email_body_offline' . $fieldSuffix};
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_renew_email_body_offline' . $fieldSuffix}
				))
			{
				$body = $message->{'user_renew_email_body_offline' . $fieldSuffix};
			}
			elseif (OSMembershipHelper::isValidMessage($plan->user_renew_email_body_offline))
			{
				$body = $plan->user_renew_email_body_offline;
			}
			else
			{
				$body = $message->user_renew_email_body_offline;
			}
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($plan->{'user_renew_email_body' . $fieldSuffix}))
		{
			$body = $plan->{'user_renew_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'user_renew_email_body' . $fieldSuffix}))
		{
			$body = $message->{'user_renew_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->user_renew_email_body))
		{
			$body = $plan->user_renew_email_body;
		}
		else
		{
			$body = $message->user_renew_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		$invoicePath = '';

		if ($row->invoice_number > 0
			&& ($config->send_invoice_to_customer || $config->send_invoice_to_admin))
		{
			$invoicePath = OSMembershipHelper::generateAndReturnInvoicePath($row);
		}

		if ($config->send_invoice_to_customer && $invoicePath)
		{
			$mailer->addAttachment($invoicePath);
		}

		// Generate and send member card to subscriber email
		if ($config->send_member_card_via_email
			&& $plan->activate_member_card_feature
			&& $row->published == 1)
		{
			$path = OSMembershipHelperSubscription::generatePlanMemberCard($row, $config);
			$mailer->addAttachment($path);
		}

		if ($row->published)
		{
			static::addSubscriptionDocuments($mailer, $row);
		}

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send(
				$mailer,
				array_merge([$row->email], self::getEmailsFromSubscriptionData($rowFields, $replaces)),
				$subject,
				$body,
				$logEmails,
				2,
				'subscription_renewal_emails',
				$row
			);

			$mailer->clearAllRecipients();
		}

		$mailer->clearAttachments();

		if ($config->send_invoice_to_admin && $invoicePath)
		{
			$mailer->addAttachment($invoicePath);
		}


		$emails = explode(',', $config->notification_emails);

		if (strlen($message->{'admin_renw_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_renw_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_renw_email_subject;
		}

		$subject = str_replace('[PLAN_TITLE]', $plan->title, $subject);

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'admin_renew_email_body' . $fieldSuffix}))
		{
			$body = $message->{'admin_renew_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->admin_renew_email_body))
		{
			$body = $plan->admin_renew_email_body;
		}
		else
		{
			$body = $message->admin_renew_email_body;
		}

		if ($row->payment_method == 'os_offline_creditcard')
		{
			$emailContent                    = OSMembershipHelper::getEmailContent($config, $row, true, 'register');
			$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		//We will need to get attachment data here
		if ($config->send_attachments_to_admin)
		{
			static::addAttachments($mailer, $rowFields, $replaces);
		}

		if ($row->email && MailHelper::isEmailAddress($row->email))
		{
			$mailer->addReplyTo($row->email);
		}

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'subscription_renewal_emails', $row);
	}

	/**
	 * Send email when someone upgrade their membership
	 *
	 * @param   Mail                         $mailer
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   stdClass                     $plan
	 * @param   MPFConfig                    $config
	 * @param   MPFConfig                    $message
	 * @param   string                       $fieldSuffix
	 */
	public static function sendMembershipUpgradeEmails($mailer, $row, $plan, $config, $message, $fieldSuffix)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendMembershipUpgradeEmails'))
		{
			OSMembershipHelperOverrideMail::sendMembershipUpgradeEmails(
				$mailer,
				$row,
				$plan,
				$config,
				$message,
				$fieldSuffix
			);

			return;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select($db->quoteName('b.title' . $fieldSuffix, 'title'))
			->from('#__osmembership_upgraderules AS a')
			->innerJoin('#__osmembership_plans AS b ON a.from_plan_id = b.id')
			->where('a.id = ' . $row->upgrade_option_id);
		$db->setQuery($query);
		$planTitle = $db->loadResult();

		$logEmails = static::loggingEnabled('subscription_upgrade_emails', $config);

		$rowFields = OSMembershipHelper::getProfileFields($row->plan_id);

		$emailContent = OSMembershipHelper::getEmailContent($config, $row, false, 'register');
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		$replaces['plan_title']    = $planTitle;
		$replaces['to_plan_title'] = $plan->title;

		$isOfflinePaymentSubscription = str_contains($row->payment_method, 'os_offline') && $row->published == 0;

		// Subscription Upgrade Email Subject
		if ($fieldSuffix && $isOfflinePaymentSubscription && trim(
				$message->{'user_upgrade_email_subject_offline' . $fieldSuffix}
			))
		{
			$subject = $message->{'user_upgrade_email_subject_offline' . $fieldSuffix};
		}
		elseif ($isOfflinePaymentSubscription && trim($message->user_upgrade_email_subject_offline))
		{
			$subject = $message->user_upgrade_email_subject_offline;
		}
		elseif ($fieldSuffix && $message->{'user_upgrade_email_subject' . $fieldSuffix})
		{
			$subject = $message->{'user_upgrade_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->user_upgrade_email_subject;
		}

		// Subscription Renewal Email Body
		if ($isOfflinePaymentSubscription)
		{
			$offlineSuffix = str_replace('os_offline', '', $row->payment_method);
			if ($offlineSuffix && $fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_upgrade_email_body_offline' . $offlineSuffix . $fieldSuffix}
				))
			{
				$body = $message->{'user_upgrade_email_body_offline' . $offlineSuffix . $fieldSuffix};
			}
			elseif ($offlineSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_upgrade_email_body_offline' . $offlineSuffix}
				))
			{
				$body = $message->{'user_upgrade_email_body_offline' . $offlineSuffix};
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$plan->{'user_upgrade_email_body_offline' . $fieldSuffix}
				))
			{
				$body = $plan->{'user_upgrade_email_body_offline' . $fieldSuffix};
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_upgrade_email_body_offline' . $fieldSuffix}
				))
			{
				$body = $message->{'user_upgrade_email_body_offline' . $fieldSuffix};
			}
			elseif (OSMembershipHelper::isValidMessage($plan->user_upgrade_email_body_offline))
			{
				$body = $plan->user_upgrade_email_body_offline;
			}
			elseif (OSMembershipHelper::isValidMessage($message->user_upgrade_email_body_offline))
			{
				$body = $message->user_upgrade_email_body_offline;
			} // The conditions below is for keep backward compatible
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'user_upgrade_email_body' . $fieldSuffix}
				))
			{
				$body = $message->{'user_upgrade_email_body' . $fieldSuffix};
			}
			elseif (OSMembershipHelper::isValidMessage($plan->user_upgrade_email_body))
			{
				$body = $plan->user_upgrade_email_body;
			}
			else
			{
				$body = $message->user_upgrade_email_body;
			}
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($plan->{'user_upgrade_email_body' . $fieldSuffix}))
		{
			$body = $plan->{'user_upgrade_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'user_upgrade_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'user_upgrade_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->user_upgrade_email_body))
		{
			$body = $plan->user_upgrade_email_body;
		}
		else
		{
			$body = $message->user_upgrade_email_body;
		}

		$subject = str_replace('[TO_PLAN_TITLE]', $plan->title, $subject);
		$subject = str_replace('[PLAN_TITLE]', $planTitle, $subject);

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		$invoicePath = '';

		if ($row->invoice_number > 0
			&& ($config->send_invoice_to_customer || $config->send_invoice_to_admin))
		{
			$invoicePath = OSMembershipHelper::generateAndReturnInvoicePath($row);
		}

		if ($config->send_invoice_to_customer && $invoicePath)
		{
			$mailer->addAttachment($invoicePath);
		}

		// Generate and send member card to subscriber email
		if ($config->send_member_card_via_email
			&& $plan->activate_member_card_feature
			&& $row->published == 1)
		{
			$path = OSMembershipHelperSubscription::generatePlanMemberCard($row, $config);
			$mailer->addAttachment($path);
		}

		if ($row->published)
		{
			static::addSubscriptionDocuments($mailer, $row);
		}

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send(
				$mailer,
				array_merge([$row->email], self::getEmailsFromSubscriptionData($rowFields, $replaces)),
				$subject,
				$body,
				$logEmails,
				2,
				'subscription_upgrade_emails',
				$row
			);

			$mailer->clearAllRecipients();
		}

		$mailer->clearAttachments();

		if ($config->send_invoice_to_admin && $invoicePath)
		{
			$mailer->addAttachment($invoicePath);
		}

		//Send emails to notification emails

		$emails = explode(',', $config->notification_emails);

		if ($fieldSuffix && strlen($message->{'admin_upgrade_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_upgrade_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_upgrade_email_subject;
		}

		$subject = str_replace('[TO_PLAN_TITLE]', $plan->title, $subject);
		$subject = str_replace('[PLAN_TITLE]', $planTitle, $subject);

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'admin_upgrade_email_body' . $fieldSuffix}))
		{
			$body = $message->{'admin_upgrade_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->admin_upgrade_email_body))
		{
			$body = $plan->admin_upgrade_email_body;
		}
		else
		{
			$body = $message->admin_upgrade_email_body;
		}

		if ($row->payment_method == 'os_offline_creditcard')
		{
			$emailContent                    = OSMembershipHelper::getEmailContent($config, $row, true, 'register');
			$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		// Add attachments which subscriber upload to notification emails
		if ($config->send_attachments_to_admin)
		{
			static::addAttachments($mailer, $rowFields, $replaces);
		}

		if ($row->email && MailHelper::isEmailAddress($row->email))
		{
			$mailer->addReplyTo($row->email);
		}

		static::send($mailer, $emails, $subject, $body, $logEmails, 2, 'subscription_upgrade_emails', $row);
	}

	/**
	 * Send email to subscriber to inform them that their membership approved (and activated)
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public static function sendMembershipApprovedEmail($row)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendMembershipApprovedEmail'))
		{
			OSMembershipHelperOverrideMail::sendMembershipApprovedEmail($row);

			return;
		}

		OSMembershipHelper::loadSubscriptionLanguage($row);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		if ($plan->category_id > 0)
		{
			$category = OSMembershipHelperDatabase::getCategory($plan->category_id);

			OSMembershipHelper::setPlanMessagesDataFromCategory($plan, $category, ['subscription_approved_email_body']);
		}

		$config = OSMembershipHelper::getConfig();

		if (trim($plan->notification_emails))
		{
			$config->notification_emails = $plan->notification_emails;
		}

		$mailer = static::getMailer($config);

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);
		$rowFields   = OSMembershipHelper::getProfileFields($row->plan_id);

		$logEmails = static::loggingEnabled('subscription_approved_emails', $config);

		$emailContent = OSMembershipHelper::getEmailContent($config, $row);
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		if ($fieldSuffix && trim($plan->{'subscription_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $plan->{'subscription_approved_email_subject' . $fieldSuffix};
		}
		elseif ($fieldSuffix && trim($message->{'subscription_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'subscription_approved_email_subject' . $fieldSuffix};
		}
		elseif (trim($plan->subscription_approved_email_subject))
		{
			$subject = $plan->subscription_approved_email_subject;
		}
		else
		{
			$subject = $message->subscription_approved_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$plan->{'subscription_approved_email_body' . $fieldSuffix}
			))
		{
			$body = $plan->{'subscription_approved_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'subscription_approved_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'subscription_approved_email_body' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->subscription_approved_email_body))
		{
			$body = $plan->subscription_approved_email_body;
		}
		else
		{
			$body = $message->subscription_approved_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if (MailHelper::isEmailAddress($row->email))
		{
			// Generate paid invoice and send it to email
			if ($row->invoice_number > 0 && $config->send_invoice_to_customer)
			{
				$invoicePath = OSMembershipHelper::generateAndReturnInvoicePath($row);
				$mailer->addAttachment($invoicePath);
			}

			// Generate and send member card to subscriber email
			if ($config->send_member_card_via_email && $plan->activate_member_card_feature)
			{
				$path = OSMembershipHelperSubscription::generatePlanMemberCard($row, $config);
				$mailer->addAttachment($path);
			}

			// Add documents which is managed in documents management plugin to email if needed
			static::addSubscriptionDocuments($mailer, $row);

			// Process sending email
			static::send(
				$mailer,
				array_merge([$row->email], self::getEmailsFromSubscriptionData($rowFields, $replaces)),
				$subject,
				$body,
				$logEmails,
				2,
				'subscription_approved_emails',
				$row
			);

			$mailer->clearAllRecipients();
			$mailer->clearAttachments();
		}

		$emails = explode(',', $config->notification_emails);

		if ($fieldSuffix && strlen($message->{'admin_subscription_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_subscription_approved_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_subscription_approved_email_subject;
		}


		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'admin_subscription_approved_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'admin_subscription_approved_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->admin_subscription_approved_email_body;
		}

		if (!$subject)
		{
			return;
		}

		$user                          = Factory::getApplication()->getIdentity();
		$replaces['APPROVAL_USERNAME'] = $user->username;
		$replaces['APPROVAL_NAME']     = $user->name;
		$replaces['APPROVAL_EMAIL']    = $user->email;

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		static::send($mailer, $emails, $subject, $body, $logEmails, 2, 'subscription_approved_emails', $row);

		if ($row->published == 1 && $row->user_password)
		{
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('user_password = ""')
				->where('id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Send confirmation email to subscriber and notification email to admin when a recurring subscription cancelled
	 *
	 * @param $row
	 * @param $config
	 */
	public static function sendSubscriptionPaymentEmail($row, $config)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendSubscriptionPaymentEmail'))
		{
			OSMembershipHelperOverrideMail::sendSubscriptionPaymentEmail($row, $config);

			return;
		}

		// Load the frontend language file with subscription record language
		$lang = Factory::getApplication()->getLanguage();
		$tag  = $row->language;

		if (!$tag)
		{
			$tag = 'en-GB';
		}

		$lang->load('com_osmembership', JPATH_ROOT, $tag);

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$query->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		if ($plan->notification_emails)
		{
			$config->notification_emails = $plan->notification_emails;
		}

		$mailer = static::getMailer($config);

		$logEmails = static::loggingEnabled('subscription_payment_emails', $config);

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['SUBSCRIPTION_DETAIL'] = OSMembershipHelper::getEmailContent($config, $row);

		// Send confirmation email to subscriber
		if ($fieldSuffix && strlen($message->{'subscription_payment_user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'subscription_payment_user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->subscription_payment_user_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'subscription_payment_user_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'subscription_payment_user_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->subscription_payment_user_email_body;
		}

		if ($row->invoice_number > 0 && $config->send_invoice_to_customer)
		{
			$invoicePath = OSMembershipHelper::generateAndReturnInvoicePath($row);

			$mailer->addAttachment($invoicePath);
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'subscription_payment_emails');
			$mailer->clearAllRecipients();
			$mailer->clearAttachments();
		}

		//Send notification email to administrators
		$emails = explode(',', $config->notification_emails);

		if ($fieldSuffix && strlen($message->{'subscription_payment_admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'subscription_payment_admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->subscription_payment_admin_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'subscription_payment_admin_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'subscription_payment_admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->subscription_payment_admin_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'subscription_payment_emails', $row);
	}

	/**
	 * Send confirmation email to subscriber and notification email to admin when a recurring subscription cancelled
	 *
	 * @param $row
	 * @param $config
	 */
	public static function sendSubscriptionCancelEmail($row, $config)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendSubscriptionCancelEmail'))
		{
			OSMembershipHelperOverrideMail::sendSubscriptionCancelEmail($row, $config);

			return;
		}

		// Load the frontend language file with subscription record language
		$lang = Factory::getApplication()->getLanguage();
		$tag  = $row->language;

		if (!$tag)
		{
			$tag = 'en-GB';
		}

		$lang->load('com_osmembership', JPATH_ROOT, $tag);

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$query->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);

		if ($fieldSuffix)
		{
			OSMembershipHelperDatabase::getMultilingualFields($query, ['title'], $fieldSuffix);
		}

		$db->setQuery($query);
		$plan = $db->loadObject();

		if ($plan->notification_emails)
		{
			$config->notification_emails = $plan->notification_emails;
		}

		$mailer = static::getMailer($config);

		$logEmails = static::loggingEnabled('subscription_cancel_emails', $config);

		$replaces['plan_title'] = $plan->title;
		$replaces['first_name'] = $row->first_name;
		$replaces['last_name']  = $row->last_name;
		$replaces['email']      = $row->email;

		// Get latest subscription end date
		$query->clear()
			->select('MAX(to_date)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('plan_id = ' . $row->plan_id);
		$db->setQuery($query);
		$subscriptionEndDate = $db->loadResult();

		if ($subscriptionEndDate)
		{
			$subscriptionEndDate = HTMLHelper::_('date', $subscriptionEndDate, $config->date_format);
		}

		$replaces['SUBSCRIPTION_END_DATE'] = $subscriptionEndDate;

		// Common tags
		if ($config->common_tags)
		{
			$commonTags = json_decode($config->common_tags, true);

			foreach ($commonTags as $commonTag)
			{
				$tagName = $commonTag['name'];

				if (!is_string($tagName))
				{
					continue;
				}

				$tagName = OSMembershipHelper::normalizeCommonTagName($tagName);

				$replaces[$tagName] = $commonTag['value'];
			}
		}

		// Send confirmation email to subscribers
		if (strlen($message->{'user_recurring_subscription_cancel_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_recurring_subscription_cancel_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->user_recurring_subscription_cancel_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'user_recurring_subscription_cancel_body' . $fieldSuffix}
			))
		{
			$body = $message->{'user_recurring_subscription_cancel_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->user_recurring_subscription_cancel_body;
		}

		$subject = str_replace('[PLAN_TITLE]', $plan->title, $subject);

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'subscription_cancel_emails', $row);
			$mailer->clearAllRecipients();
		}

		//Send notification email to administrators
		$emails = explode(',', $config->notification_emails);

		if (strlen($message->{'admin_recurring_subscription_cancel_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_recurring_subscription_cancel_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_recurring_subscription_cancel_subject;
		}

		$subject = str_replace('[PLAN_TITLE]', $plan->title, $subject);

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'admin_recurring_subscription_cancel_body' . $fieldSuffix}
			))
		{
			$body = $message->{'admin_recurring_subscription_cancel_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->admin_recurring_subscription_cancel_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'subscription_cancel_emails', $row);
	}

	/**
	 * Send notification email to admin when someone update his profile
	 *
	 * @param $row
	 * @param $config
	 */
	public static function sendProfileUpdateEmail($row, $config, $updateFields = [])
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendProfileUpdateEmail'))
		{
			OSMembershipHelperOverrideMail::sendProfileUpdateEmail($row, $config);

			return;
		}

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if (strlen($message->{'profile_update_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'profile_update_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->profile_update_email_subject;
		}

		if (empty($subject))
		{
			return;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'profile_update_email_body' . $fieldSuffix}))
		{
			$body = $message->{'profile_update_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->profile_update_email_body;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		if ($plan->notification_emails)
		{
			$config->notification_emails = $plan->notification_emails;
		}

		$mailer = static::getMailer($config);

		$logEmails = static::loggingEnabled('profile_updated_emails', $config);

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		if (!empty($updateFields))
		{
			$replaces['profile_updated_details'] = OSMembershipHelperHtml::loadCommonLayout(
				'emailtemplates/tmpl/profile_updated.php',
				['fields' => $updateFields]
			);
		}
		else
		{
			$replaces['profile_updated_details'] = '';
		}

		// Get latest subscription end date
		$query->clear()
			->select('MAX(to_date)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('plan_id = ' . $row->plan_id);
		$db->setQuery($query);
		$subscriptionEndDate = $db->loadResult();

		if (!$subscriptionEndDate)
		{
			$subscriptionEndDate = date($config->date_format);
		}
		$replaces['SUBSCRIPTION_END_DATE'] = $subscriptionEndDate;
		$replaces['SUBSCRIPTION_DETAIL']   = OSMembershipHelper::getEmailContent($config, $row);
		$profileUrl                        = Uri::root(
			) . 'administrator/index.php?option=com_osmembership&view=subscriber&id=' . $row->profile_id;
		$replaces['profile_link']          = '<a href="' . $profileUrl . '">' . $profileUrl . '</a>';

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		$emails = explode(',', $config->notification_emails);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'profile_updated_emails', $row);
	}

	/**
	 * Method for sending first, second and third reminder emails
	 *
	 * @param   array   $rows
	 * @param   string  $bccEmail
	 * @param   int     $time
	 */
	public static function sendReminderEmails($rows, $bccEmail, $time = 1)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db        = Factory::getContainer()->get('db');
		$query     = $db->getQuery(true);
		$mailer    = static::getMailer($config);
		$bccEmails = explode(',', $bccEmail);

		$bccEmails      = array_map('trim', $bccEmails);
		$validBccEmails = [];

		foreach ($bccEmails as $bccEmail)
		{
			if (MailHelper::isEmailAddress($bccEmail))
			{
				$validBccEmails[] = $bccEmail;
			}
		}

		$query->select('*')
			->from('#__osmembership_plans');
		$db->setQuery($query);
		$plans = $db->loadObjectList('id');

		$query->clear()
			->select('*')
			->from('#__osmembership_categories');
		$db->setQuery($query);
		$categories = $db->loadObjectList('id');

		$fieldSuffixes = [];

		switch ($time)
		{
			case 2:
				$fieldPrefix = 'second_reminder_';
				$emailType   = 'second_reminder_emails';
				break;
			case 3:
				$fieldPrefix = 'third_reminder_';
				$emailType   = 'third_reminder_emails';
				break;
			case 4:
				$fieldPrefix = 'fourth_reminder_';
				$emailType   = 'fourth_reminder_emails';
				break;
			case 5:
				$fieldPrefix = 'fifth_reminder_';
				$emailType   = 'fifth_reminder_emails';
				break;
			case 6:
				$fieldPrefix = 'sixth_reminder_';
				$emailType   = 'sixth_reminder_emails';
				break;
			default:
				$fieldPrefix = 'first_reminder_';
				$emailType   = 'first_reminder_emails';
				break;
		}

		$message   = OSMembershipHelper::getMessages();
		$timeSent  = $db->quote(Factory::getDate()->toSql());
		$logEmails = static::loggingEnabled($emailType, $config);

		$fieldsToSetFromCategoryToPlan = [
			'first_reminder_email_body',
			'second_reminder_email_body',
			'third_reminder_email_body',
		];

		if (count($plans))
		{
			$plan = array_values($plans)[0];

			if (property_exists($plan, 'fourth_reminder_email_body'))
			{
				$fieldsToSetFromCategoryToPlan[] = 'fourth_reminder_email_body';
			}

			if (property_exists($plan, 'fifth_reminder_email_body'))
			{
				$fieldsToSetFromCategoryToPlan[] = 'fifth_reminder_email_body';
			}

			if (property_exists($plan, 'sixth_reminder_email_body'))
			{
				$fieldsToSetFromCategoryToPlan[] = 'sixth_reminder_email_body';
			}
		}

		foreach ($rows as $row)
		{
			// Check to see whether the subscriber renewed their subscription before, if Yes, stop sending reminder
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $row->plan_id)
				->where('published = 1')
				->where('id > ' . $row->id)
				->where('((user_id > 0 AND user_id = ' . (int) $row->user_id . ') OR email="' . $row->email . '")');
			$db->setQuery($query);
			$total = (int) $db->loadResult();

			if ($total)
			{
				$query->clear()
					->update('#__osmembership_subscribers')
					->set($db->quoteName($fieldPrefix . 'sent') . ' = 1 ')
					->where('id = ' . $row->id);
				$db->setQuery($query);
				$db->execute();

				continue;
			}

			$fieldSuffix = '';

			if ($row->language)
			{
				if (!isset($fieldSuffixes[$row->language]))
				{
					$fieldSuffixes[$row->language] = OSMembershipHelper::getFieldSuffix($row->language);
				}

				$fieldSuffix = $fieldSuffixes[$row->language];
			}

			$plan = $plans[$row->plan_id];

			if ($plan->category_id && isset($categories[$plan->category_id]))
			{
				$category = $categories[$plan->category_id];

				OSMembershipHelper::setPlanMessagesDataFromCategory($plan, $category, $fieldsToSetFromCategoryToPlan);
			}

			$rowFields = OSMembershipHelper::getProfileFields($row->plan_id);

			$replaces                = OSMembershipHelper::callOverridableHelperMethod(
				'Helper',
				'buildTags',
				[$row, $config]
			);
			$replaces['number_days'] = $row->number_days;
			$replaces['expire_date'] = HTMLHelper::_('date', $row->to_date, $config->date_format);

			if (strlen($plan->{$fieldPrefix . 'email_subject'}) > 0)
			{
				$subject = $plan->{$fieldPrefix . 'email_subject'};
			}
			elseif (strlen($message->{$fieldPrefix . 'email_subject' . $fieldSuffix}))
			{
				$subject = $message->{$fieldPrefix . 'email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->{$fieldPrefix . 'email_subject'};
			}

			if (self::isValidEmailBody($plan->{$fieldPrefix . 'email_body'}))
			{
				$body = $plan->{$fieldPrefix . 'email_body'};
			}
			elseif (self::isValidEmailBody($message->{$fieldPrefix . 'email_body' . $fieldSuffix}))
			{
				$body = $message->{$fieldPrefix . 'email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->{$fieldSuffix . 'email_body'};
			}

			$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
			$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

			if (MailHelper::isEmailAddress($row->email))
			{
				$receiptEmails = array_merge([$row->email], self::getEmailsFromSubscriptionData($rowFields, $replaces));

				if (count($validBccEmails))
				{
					$receiptEmails = array_merge($receiptEmails, $bccEmails);
				}

				static::send($mailer, $receiptEmails, $subject, $body, $logEmails, 2, $emailType);

				$mailer->clearAllRecipients();
			}

			$query->clear()
				->update('#__osmembership_subscribers')
				->set($fieldPrefix . 'sent = 1')
				->set($fieldPrefix . 'sent_at = ' . $timeSent)
				->where('id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Send subscription end email
	 *
	 * @param   array   $rows
	 * @param   string  $bccEmail
	 */
	public static function sendSubscriptionEndEmails($rows, $bccEmail)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db        = Factory::getContainer()->get('db');
		$query     = $db->getQuery(true);
		$mailer    = static::getMailer($config);
		$logEmails = static::loggingEnabled('subscription_end_emails', $config);

		$bccEmails = explode(',', $bccEmail);

		$bccEmails = array_map('trim', $bccEmails);

		foreach ($bccEmails as $bccEmail)
		{
			if (MailHelper::isEmailAddress($bccEmail))
			{
				$mailer->addBcc($bccEmail);
			}
		}

		// Get list of payment methods
		$query->select('name, title')
			->from('#__osmembership_plugins');
		$db->setQuery($query);
		$plugins = $db->loadObjectList('name');

		$query->clear()
			->select('*')
			->from('#__osmembership_plans');
		$db->setQuery($query);
		$plans = $db->loadObjectList('id');

		$fieldSuffixes = [];

		$message  = OSMembershipHelper::getMessages();
		$timeSent = $db->quote(Factory::getDate()->toSql());

		foreach ($rows as $row)
		{
			// Check to see whether the subscriber renewed their subscription before, if Yes, stop sending reminder
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $row->plan_id)
				->where('published = 1')
				->where('id > ' . $row->id)
				->where('((user_id > 0 AND user_id = ' . (int) $row->user_id . ') OR email="' . $row->email . '")');
			$db->setQuery($query);
			$total = (int) $db->loadResult();

			if ($total)
			{
				$query->clear()
					->update('#__osmembership_subscribers')
					->set($db->quoteName('subscription_end_sent') . ' = 1 ')
					->where('id = ' . $row->id);
				$db->setQuery($query);
				$db->execute();

				continue;
			}

			$fieldSuffix = '';

			if ($row->language)
			{
				if (!isset($fieldSuffixes[$row->language]))
				{
					$fieldSuffixes[$row->language] = OSMembershipHelper::getFieldSuffix($row->language);
				}

				$fieldSuffix = $fieldSuffixes[$row->language];
			}
			$plan                      = $plans[$row->plan_id];
			$planTitle                 = $plan->{'title' . $fieldSuffix};
			$replaces                  = [];
			$replaces['plan_title']    = $planTitle;
			$replaces['first_name']    = $row->first_name;
			$replaces['last_name']     = $row->last_name;
			$replaces['number_days']   = $row->number_days;
			$replaces['membership_id'] = OSMembershipHelper::formatMembershipId($row, $config);
			$replaces['expire_date']   = HTMLHelper::_('date', $row->to_date, $config->date_format);
			$replaces['gross_amount']  = OSMembershipHelper::formatAmount($row->gross_amount, $config);

			if (isset($plugins[$row->payment_method]))
			{
				$replaces['payment_method'] = $plugins[$row->payment_method]->title;
			}
			else
			{
				$replaces['payment_method'] = '';
			}
			if (!empty($plan->{'subscription_end_email_subject'}) > 0)
			{
				$subject = $plan->{'subscription_end_email_subject'};
			}
			elseif ($fieldSuffix && strlen($message->{'subscription_end_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'subscription_end_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->{'subscription_end_email_subject'};
			}
			if (self::isValidEmailBody($plan->{'subscription_end_email_body'}))
			{
				$body = $plan->{'subscription_end_email_body'};
			}
			elseif ($fieldSuffix && self::isValidEmailBody($message->{'subscription_end_email_body' . $fieldSuffix}))
			{
				$body = $message->{'subscription_end_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->{'subscription_end_email_body'};
			}

			$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
			$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

			if (MailHelper::isEmailAddress($row->email))
			{
				static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'subscription_end_emails');

				$mailer->clearAddresses();
			}
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('subscription_end_sent = 1')
				->set('subscription_end_sent_at = ' . $timeSent)
				->where('id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Send offline payment reminder email
	 *
	 * @param   array   $rows
	 * @param   string  $bccEmails
	 */
	public static function sendOfflinePaymentReminderEmails($rows, $bccEmails)
	{
		$config = OSMembershipHelper::getConfig();

		$mailer    = static::getMailer($config);
		$logEmails = static::loggingEnabled('send_offline_payment_reminder_emails', $config);

		$bccEmails = explode(',', $bccEmails);

		$bccEmails = array_map('trim', $bccEmails);

		foreach ($bccEmails as $bccEmail)
		{
			if (MailHelper::isEmailAddress($bccEmail))
			{
				$mailer->addBcc($bccEmail);
			}
		}

		$fieldSuffixes = [];

		$message = OSMembershipHelper::getMessages();

		foreach ($rows as $row)
		{
			$fieldSuffix = '';

			if ($row->language)
			{
				if (!isset($fieldSuffixes[$row->language]))
				{
					$fieldSuffixes[$row->language] = OSMembershipHelper::getFieldSuffix($row->language);
				}

				$fieldSuffix = $fieldSuffixes[$row->language];
			}

			$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

			if ($fieldSuffix && strlen($message->{'offline_payment_reminder_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'offline_payment_reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->{'offline_payment_reminder_email_subject'};
			}

			if ($fieldSuffix && self::isValidEmailBody(
					$message->{'offline_payment_reminder_email_body' . $fieldSuffix}
				))
			{
				$body = $message->{'offline_payment_reminder_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->{'offline_payment_reminder_email_body'};
			}

			$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
			$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

			if (MailHelper::isEmailAddress($row->email))
			{
				static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'offline_payment_reminder_emails');

				$mailer->clearAddresses();
			}
		}
	}

	/**
	 * Send email to user to inform them that he has just added as new member of a group
	 *
	 * @param   object  $row
	 */
	public static function sendNewGroupMemberEmail($row)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendNewGroupMemberEmail'))
		{
			OSMembershipHelperOverrideMail::sendNewGroupMemberEmail($row);

			return;
		}

		// Load frontend language file
		if ($row->language && $row->language != '*')
		{
			$lang = Factory::getApplication()->getLanguage();
			$lang->load('com_osmembership', JPATH_ROOT, $row->language);
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$config = OSMembershipHelper::getConfig();

		$mailer    = static::getMailer($config);
		$logEmails = static::loggingEnabled('new_group_member_emails', $config);

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		$query->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		$query->clear()
			->select('*')
			->from('#__users')
			->where('id = ' . (int) $row->group_admin_id);
		$db->setQuery($query);
		$groupAdmin = $db->loadObject();

		$emailContent = OSMembershipHelper::getEmailContent($config, $row);
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['plan_title']          = $plan->title;
		$replaces['group_admin_name']    = $groupAdmin->name;
		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		if ($row->password)
		{
			$replaces['password'] = $row->password;
		}
		else
		{
			$replaces['password'] = '';
		}

		if (strlen($message->{'new_group_member_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'new_group_member_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->new_group_member_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'new_group_member_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'new_group_member_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->new_group_member_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'new_group_member_email', $row);
		}

		$mailer->clearAllRecipients();

		// Send notification to administrator
		if (strlen($message->{'admin_new_group_member_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_new_group_member_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_new_group_member_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'admin_new_group_member_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'admin_new_group_member_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->admin_new_group_member_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if ($plan->notification_emails)
		{
			$config->notification_emails = $plan->notification_emails;
		}

		$emails = explode(',', $config->notification_emails);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'new_group_member_email', $row);
	}

	/**
	 * Method to send email to group admin and group member to inform them about joining group
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   MPFConfig                    $config
	 */
	public static function sendUserJoinGroupEmail($row, $config)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$config = OSMembershipHelper::getConfig();

		$mailer    = static::getMailer($config);
		$logEmails = static::loggingEnabled('user_join_group_notification_emails', $config);

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		$query->select('*')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		$emailContent = OSMembershipHelper::getEmailContent($config, $row);
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$query->clear()
			->select('*')
			->from('#__users')
			->where('id = ' . $row->group_admin_id);
		$db->setQuery($query);
		$groupAdmin = $db->loadObject();

		$replaces['plan_title']          = $plan->title;
		$replaces['group_admin_name']    = $groupAdmin->name;
		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		if ($fieldSuffix && strlen($message->{'join_group_user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'join_group_user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->join_group_user_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($message->{'join_group_user_email_body' . $fieldSuffix}))
		{
			$body = $message->{'join_group_user_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->join_group_user_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send(
				$mailer,
				[$row->email],
				$subject,
				$body,
				$logEmails,
				2,
				'user_join_group_notification_emails',
				$row
			);
		}

		$mailer->clearAllRecipients();

		// Now, send email to group admin
		if ($fieldSuffix && strlen($message->{'join_group_group_admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'join_group_user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->join_group_group_admin_email_subject;
		}

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'join_group_group_admin_email_body' . $fieldSuffix}
			))
		{
			$body = $message->{'join_group_group_admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->join_group_group_admin_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		if (MailHelper::isEmailAddress($groupAdmin->email))
		{
			$emails = [$groupAdmin->email];

			if ($plan->notification_emails)
			{
				$notificationEmails = $plan->notification_emails;
			}
			else
			{
				$notificationEmails = $config->notification_emails;
			}

			$notificationEmails = explode(',', $notificationEmails);

			foreach ($notificationEmails as $notificationEmail)
			{
				if (MailHelper::isEmailAddress($notificationEmail))
				{
					$emails[] = $notificationEmail;
				}
			}

			static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'user_join_group_notification_emails', $row);
		}
	}

	/**
	 * Send mass mail to selected subscriptions
	 *
	 * @param   array     $rows
	 * @param   array     $fields
	 * @param   string    $emailSubject
	 * @param   string    $emailMessage
	 * @param   string    $relyToEmail
	 * @param   string    $bccEmail
	 * @param   string    $attachmentFile
	 * @param   string    $fileName
	 * @param   MPFInput  $input
	 */
	public static function sendMassMails(
		$rows,
		$emailSubject,
		$emailMessage,
		$relyToEmail = '',
		$bccEmail = '',
		$attachmentFile = null,
		string $fileName = null,
		$input = null
	) {
		$config    = OSMembershipHelper::getConfig();
		$mailer    = static::getMailer($config);
		$logEmails = static::loggingEnabled('mass_mails', $config);

		if (MailHelper::isEmailAddress($relyToEmail))
		{
			$mailer->addReplyTo($relyToEmail);
		}

		if ($attachmentFile)
		{
			$mailer->addAttachment($attachmentFile, $fileName);
		}

		if ($input)
		{
			$secondAttachment = $input->files->get('attachment2', null, 'raw');
			$thirdAttachment  = $input->files->get('attachment3', null, 'raw');
			$fourthAttachment = $input->files->get('attachment4', null, 'raw');

			$allowedExtensions = OSMembershipHelper::getAllowedFileTypes();

			if ($secondAttachment['name'])
			{
				$fileName = File::makeSafe($secondAttachment['name']);
				$fileExt  = strtolower(OSMembershipHelper::getFileExt($fileName));

				if (in_array($fileExt, $allowedExtensions))
				{
					$mailer->addAttachment($secondAttachment['tmp_name'], $fileName);
				}
			}

			if ($thirdAttachment['name'])
			{
				$fileName = File::makeSafe($thirdAttachment['name']);
				$fileExt  = strtolower(OSMembershipHelper::getFileExt($fileName));

				if (in_array($fileExt, $allowedExtensions))
				{
					$mailer->addAttachment($thirdAttachment['tmp_name'], $fileName);
				}
			}

			if ($fourthAttachment['name'])
			{
				$fileName = File::makeSafe($fourthAttachment['name']);
				$fileExt  = strtolower(OSMembershipHelper::getFileExt($fileName));

				if (in_array($fileExt, $allowedExtensions))
				{
					$mailer->addAttachment($fourthAttachment['tmp_name'], $fileName);
				}
			}
		}

		if ($bccEmail && !MailHelper::isEmailAddress($bccEmail))
		{
			$bccEmail = '';
		}

		foreach ($rows as $row)
		{
			if (!MailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$subject = $emailSubject;
			$message = $emailMessage;

			$rowFields = OSMembershipHelper::getProfileFields($row->plan_id);

			$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

			$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
			$message = OSMembershipHelper::replaceCaseInsensitiveTags($message, $replaces);

			$message = OSMembershipHelper::convertImgTags($message);

			$receiptEmails = array_merge([$row->email], self::getEmailsFromSubscriptionData($rowFields, $replaces));

			if ($bccEmail)
			{
				$receiptEmails[] = $bccEmail;
			}

			$receiptEmails = array_values(array_unique($receiptEmails));

			static::send($mailer, $receiptEmails, $subject, $message, $logEmails, 2, 'mass_mails');

			$mailer->clearAllRecipients();
		}
	}

	/**
	 * Send offline recurring email to subscribers
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   MPFConfig                    $config
	 *
	 * @return void
	 */
	public static function sendOfflineRecurringEmail($row, $config)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideMail', 'sendOfflineRecurringEmail'))
		{
			OSMembershipHelperOverrideMail::sendOfflineRecurringEmail($row, $config);

			return;
		}

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		// Load frontend language file
		if ($row->language && $row->language != '*')
		{
			$lang = Factory::getApplication()->getLanguage();
			$lang->load('com_osmembership', JPATH_ROOT, $row->language);
		}

		$config      = OSMembershipHelper::getConfig();
		$mailer      = static::getMailer($config);
		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		$emailContent = OSMembershipHelper::getEmailContent($config, $row);
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['SUBSCRIPTION_DETAIL'] = $emailContent;

		if ($fieldSuffix && strlen($message->{'offline_recurring_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'offline_recurring_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->offline_recurring_email_subject;
		}

		if (OSMembershipHelper::isValidMessage($message->{'offline_recurring_email_body' . $fieldSuffix}))
		{
			$body = $message->{'offline_recurring_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->offline_recurring_email_body;
		}

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		//add Attachment
		if ($config->activate_invoice_feature && OSMembershipHelper::callOverridableHelperMethod(
				'Helper',
				'needToCreateInvoice',
				[$row]
			))
		{
			$invoicePath = OSMembershipHelper::generateInvoicePDF($row);
			$mailer->addAttachment($invoicePath);
		}

		static::send($mailer, [$row->email], $subject, $body, true, 2, 'offline_recurring_email', $row);
	}

	/**
	 * Send email to registrant to ask them to make payment for their registration
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   MPFConfig                    $config
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function sendRequestPaymentEmail($row, $config)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		// Load frontend language file
		if ($row->language && $row->language != '*')
		{
			$lang = Factory::getApplication()->getLanguage();
			$lang->load('com_osmembership', JPATH_ROOT, $row->language);
		}

		$mailer = static::getMailer($config);

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

		$emailContent = OSMembershipHelper::getEmailContent($config, $row);
		$replaces     = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['payment_link'] = Uri::root(
			) . 'index.php?option=com_osmembership&view=payment&transaction_id=' . $row->transaction_id . '&Itemid=' . OSMembershipHelperRoute::findView(
				'payment',
				OSMembershipHelper::getItemid()
			);

		if ($fieldSuffix && strlen($message->{'request_payment_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'request_payment_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->request_payment_email_subject;
		}

		if (OSMembershipHelper::isValidMessage($message->{'request_payment_email_body' . $fieldSuffix}))
		{
			$body = $message->{'request_payment_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->request_payment_email_body;
		}

		if (empty($subject))
		{
			throw new Exception('Please configure request payment email subject');
		}

		if (empty($body))
		{
			throw new Exception('Please configure request payment email body');
		}

		$body = str_replace('[SUBSCRIPTION_DETAIL]', $emailContent, $body);

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		static::send($mailer, [$row->email], $subject, $body, true, 2, 'request_payment_email', $row);
	}

	/**
	 * Send email to registrant to ask them to make payment for their registration
	 *
	 * @param   int     $planId
	 * @param   string  $subject
	 * @param   array   $emails
	 * @param   string  $message
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function sendJoinGroupMembershipInvite($planId, $emails, $subject, $message)
	{
		$config = OSMembershipHelper::getConfig();

		$mailer = static::getMailer($config);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('plan_id = ' . $planId)
			->where('user_id = ' . Factory::getApplication()->getIdentity()->id)
			->where('published = 1')
			->order('id');
		$db->setQuery($query);
		$row = $db->loadObject();

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$groupAdmin = Factory::getApplication()->getIdentity();

		$replaces['group_admin_name']     = $groupAdmin->name;
		$replaces['group_admin_username'] = $groupAdmin->username;
		$replaces['group_admin_email']    = $groupAdmin->email;

		$body = $message;

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		static::send($mailer, $emails, $subject, $body, true, 2, 'group_membership_invite_email');
	}

	/**
	 * @param   stdClass[]  $rows
	 * @param   Registry    $params
	 *
	 * @return void
	 */
	public static function sendIncompletePaymentSubscriptionsEmails($rows, $params)
	{
		$subject = $params->get('subject', '');
		$body    = $params->get('message', '');

		$config    = OSMembershipHelper::getConfig();
		$logEmails = static::loggingEnabled('icpr_notify_email', $config);

		$mailer = static::getMailer($config);

		$body = OSMembershipHelper::convertImgTags($body);

		if (trim($params->get('notification_emails')))
		{
			$emails = explode(',', $params->get('notification_emails'));
		}
		else
		{
			$emails = explode(',', $config->notification_emails);
		}


		$ids   = [];
		$links = [];

		foreach ($rows as $row)
		{
			$ids[]   = $row->id;
			$links[] = '<a href="' . Route::link(
					'administrator',
					'index.php?option=com_osmembership&view=subscription&id=' . $row->id,
					false,
					0,
					true
				) . '"><strong>' . $row->id . '</strong></a>';
		}

		$replaces = [
			'ids'   => implode(', ', $ids),
			'links' => implode(', ', $links),
		];

		$subject = OSMembershipHelper::replaceCaseInsensitiveTags($subject, $replaces);
		$body    = OSMembershipHelper::replaceCaseInsensitiveTags($body, $replaces);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'icps_notify_email');
	}

	/**
	 * Create and initialize mailer object from configuration data
	 *
	 * @param $config
	 *
	 * @return Mail
	 */
	public static function getMailer($config)
	{
		if (version_compare(JVERSION, '4.4.0', 'ge'))
		{
			$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		}
		else
		{
			$mailer = Factory::getMailer();
		}

		$mailer->isHtml(true);

		if (MailHelper::isEmailAddress($config->from_email))
		{
			$mailer->setSender([$config->from_email, trim($config->from_name)]);
		}

		if ($config->reply_to_email && MailHelper::isEmailAddress($config->reply_to_email))
		{
			$mailer->addReplyTo($config->reply_to_email);
		}
		elseif (Factory::getApplication()->get('replyto'))
		{
			$mailer->addReplyTo(
				Factory::getApplication()->get('replyto'),
				trim(Factory::getApplication()->get('replytoname'))
			);
		}

		// Set default notification emails
		if (empty($config->notification_emails))
		{
			if (MailHelper::isEmailAddress($config->from_email))
			{
				$config->notification_emails = $config->from_email;
			}
			else
			{
				$config->notification_emails = Factory::getApplication()->get('mailfrom');
			}
		}

		return $mailer;
	}

	/**
	 * Add file uploads to the mailer object
	 *
	 * @param   Mail   $mailer
	 * @param   array  $fields
	 * @param   array  $data
	 */
	public static function addAttachments($mailer, $fields, $data)
	{
		$attachmentsPath = JPATH_ROOT . '/media/com_osmembership/upload/';

		foreach ($fields as $field)
		{
			if ($field->fieldtype == 'File' && isset($data[$field->name]))
			{
				$fileName = $data[$field->name];

				if ($fileName && file_exists($attachmentsPath . '/' . $fileName))
				{
					$pos = strpos($fileName, '_');

					if ($pos !== false)
					{
						$originalFilename = substr($fileName, $pos + 1);
					}
					else
					{
						$originalFilename = $fileName;
					}

					$mailer->addAttachment($attachmentsPath . '/' . $fileName, $originalFilename);
				}
			}
		}
	}

	/**
	 * Method to add documents which is added to plans by Membership Pro - Documents plugin
	 *
	 * @param   Mail                         $mailer
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public static function addSubscriptionDocuments($mailer, $row)
	{
		$plugin = PluginHelper::getPlugin('osmembership', 'documents');

		// Plugin is not enabled
		if (!$plugin)
		{
			return;
		}

		$params = new Registry($plugin->params);

		if (!$params->get('send_documents_via_email'))
		{
			return;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('a.attachment')
			->from('#__osmembership_documents AS a')
			->where(
				'a.id IN (SELECT document_id FROM #__osmembership_plan_documents WHERE plan_id =  ' . $row->plan_id . ')'
			);
		$db->setQuery($query);
		$documents = $db->loadColumn();

		if (count($documents))
		{
			$path = OSMembershipHelper::getDocumentsPath();
			$path = Path::clean($path . '/');

			foreach ($documents as $document)
			{
				$documentPath = $path . $document;

				if (file_exists($documentPath))
				{
					$mailer->addAttachment($documentPath);
				}
			}
		}
	}

	/**
	 * Check if the given message is a valid email message
	 *
	 * @param $body
	 *
	 * @return bool
	 */
	public static function isValidEmailBody($body)
	{
		if (strlen(trim(strip_tags($body))) > 20)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get emails from subscription data
	 *
	 * @param   array  $rowFields
	 * @param   array  $data
	 */
	public static function getEmailsFromSubscriptionData($rowFields, $data)
	{
		$emails = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->receive_emails && isset($data[$rowField->name]) && MailHelper::isEmailAddress(
					$data[$rowField->name]
				))
			{
				$emails[] = $data[$rowField->name];
			}
		}

		return $emails;
	}

	/**
	 * Process sending after all the data has been initialized
	 *
	 * @param   Mail                         $mailer
	 * @param   array                        $emails
	 * @param   string                       $subject
	 * @param   string                       $body
	 * @param   bool                         $logEmails
	 * @param   int                          $sentTo
	 * @param   string                       $emailType
	 * @param   OSMembershipTableSubscriber  $row  Optional subscription record
	 */
	public static function send(
		$mailer,
		$emails,
		$subject,
		$body,
		$logEmails = false,
		$sentTo = 0,
		$emailType = '',
		$row = null
	) {
		if (empty($subject))
		{
			return;
		}

		$emails = array_unique($emails);

		$emails = array_map('trim', $emails);

		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			if (!MailHelper::isEmailAddress($emails[$i]))
			{
				unset($emails[$i]);
			}
		}

		if (count($emails) == 0)
		{
			return;
		}

		require_once JPATH_ROOT . '/components/com_osmembership/helper/html.php';

		$emails = array_values($emails);

		$email     = $emails[0];
		$bccEmails = [];

		$mailer->addRecipient($email);

		if (count($emails) > 1)
		{
			unset($emails[0]);
			$bccEmails = $emails;
			$mailer->addBcc($bccEmails);
		}

		// Trigger content plugin for email body
		$body      = OSMembershipHelper::convertImgTags($body);
		$emailBody = OSMembershipHelperHtml::loadSharedLayout(
			'emailtemplates/tmpl/container.php',
			['body' => $body, 'subject' => $subject]
		);
		$emailBody = OSMembershipHelperHtml::processConditionalText($emailBody);


		PluginHelper::importPlugin('osmembership');

		// Event trigger to allow modifying mail object before sending email
		$eventObj = new BeforeSendingEmail([
			'mailer'    => $mailer,
			'subject'   => $subject,
			'body'      => $emailBody,
			'sentTo'    => $sentTo,
			'emailType' => $emailType,
			'row'       => $row,
		]);

		Factory::getApplication()->triggerEvent($eventObj->getName(), $eventObj);

		// Get back the modified data if it is modified
		$subject   = $eventObj->getArgument('subject');
		$emailBody = $eventObj->getArgument('body');

		try
		{
			$mailer->setSubject($subject)
				->setBody($emailBody)
				->Send();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		if ($logEmails)
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/table/email.php';

			$db              = Factory::getContainer()->get('db');
			$row             = new OSMembershipTableEmail($db);
			$row->sent_at    = Factory::getDate()->toSql();
			$row->email      = $email;
			$row->subject    = $subject;
			$row->body       = $emailBody;
			$row->sent_to    = $sentTo;
			$row->email_type = $emailType;
			$row->store();

			if (count($bccEmails))
			{
				foreach ($bccEmails as $email)
				{
					$row->id    = 0;
					$row->email = $email;
					$row->store();
				}
			}
		}
	}

	/**
	 * Method to check if the given email type need to be logged
	 *
	 * @param   string     $emailType
	 * @param   MPFConfig  $config
	 *
	 * @return bool
	 */
	public static function loggingEnabled($emailType, $config)
	{
		if ($config->get('log_emails'))
		{
			return true;
		}

		if (!empty($config->log_email_types) && in_array($emailType, explode(',', $config->log_email_types)))
		{
			return true;
		}

		return false;
	}
}
