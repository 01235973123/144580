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
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;

/**
 * HTML View class for the Membership Pro component
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipViewCancelHtml extends MPFViewHtml
{
	/**
	 * Flag to mark this view does not have an associate model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * The cancel message
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */

	public function display()
	{
		$id = $this->input->getInt('id');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('published')
			->from('#__osmembership_subscribers')
			->where('id = ' . $id);
		$db->setQuery($query);
		$published = (int) $db->loadResult();

		// Fix PayPal redirect users to cancel page although payment success
		if ($published === 1)
		{
			Factory::getApplication()->redirect(
				Route::_(
					'index.php?option=com_osmembership&view=complete&Itemid=' . $this->input->getInt('Itemid'),
					false
				)
			);
		}

		$messageObj  = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($messageObj->{'cancel_message' . $fieldSuffix}))
		{
			$message = $messageObj->{'cancel_message' . $fieldSuffix};
		}
		else
		{
			$message = $messageObj->cancel_message;
		}

		$this->message = $message;

		$this->setLayout('default');

		parent::display();
	}
}
