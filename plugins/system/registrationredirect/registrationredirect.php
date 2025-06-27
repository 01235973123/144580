<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgSystemRegistrationRedirect extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRoute' => 'onAfterRoute',
		];
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		if (!$this->app->isClient('site'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Redirect users to Membership Pro when someone tries to register for other extensions
	 *
	 * @return void
	 */
	public function onAfterRoute(Event $event): void
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$input  = $this->app->input;
		$option = $input->getCmd('option');
		$task   = $input->getCmd('task');
		$view   = $input->getCmd('view');
		$layout = $input->getCmd('layout', 'default');

		// Registration redirect
		if (($option == 'com_users' && $view == 'registration' && $layout == 'default' && empty($task))
			|| ($option == 'com_comprofiler' && ($task == 'registers' || $view == 'registers'))
			|| ($option == 'com_community' && $view == 'register')
			|| ($option == 'com_users' && $task == 'registration.register')
		)
		{
			$url = $this->params->get('redirect_url', OSMembershipHelper::getViewUrl(['categories', 'plans', 'plan', 'register']));

			if (!$url)
			{
				$Itemid = OSMembershipHelper::getItemid();
				$url    = Route::_('index.php?option=com_osmembership&view=plans&Itemid=' . $Itemid);
			}

			$this->app->redirect($url);
		}

		// In case users enter email to login, we can convert it to username if needed
		$config = OSMembershipHelper::getConfig();

		if (!empty($config->use_email_as_username) && $option == 'com_users' && $task == 'user.login')
		{
			$method   = $input->getMethod();
			$username = $input->$method->get('username', '', 'USERNAME');

			if (MailHelper::isEmailAddress($username))
			{
				$db    = $this->db;
				$query = $db->getQuery(true)
					->select('*')
					->from('#__users')
					->where('(username = ' . $this->db->quote($username) . ' OR email=' . $this->db->quote($username) . ')');
				$this->db->setQuery($query);
				$user = $this->db->loadObject();

				if ($user && ($user->username != $username))
				{
					$input->$method->set('username', $user->username);
				}
			}
		}
	}
}
