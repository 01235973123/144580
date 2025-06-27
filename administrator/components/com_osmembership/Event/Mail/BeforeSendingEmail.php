<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Admin\Event\Mail;

use Joomla\CMS\Mail\Mail;

class BeforeSendingEmail extends \MPFEventBase
{
	protected $requiredArguments = ['mailer', 'subject', 'body'];

	/**
	 * Constructor
	 *
	 * @param   array  $arguments
	 */
	public function __construct(array $arguments = [])
	{
		parent::__construct('onMPBeforeSendingEmail', $arguments);
	}

	/**
	 * Get the subscription record associated with the email
	 *
	 * @return ?\OSMembershipTableSubscriber
	 */
	public function getSubscriptionRecord()
	{
		return $this->getArgument('row');
	}

	/**
	 * Get mailer object
	 *
	 * @return Mail
	 */
	public function getMailerObject()
	{
		return $this->getArgument('mailer');
	}

	/**
	 * Get subject of the email
	 *
	 * @return string
	 */
	public function getEmailSubject(): string
	{
		return $this->getArgument('subject');
	}

	/**
	 * Get email body
	 *
	 * @return string
	 */
	public function getEmailBody(): string
	{
		return $this->getArgument('body');
	}
}