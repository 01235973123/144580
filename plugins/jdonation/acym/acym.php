<?php

/**
 * @version        5.4.5
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2018 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;


class plgJDonationAcym extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public function __construct(& $subject, $config)
	{
		if (!file_exists(JPATH_ROOT . '/components/com_acym/acym.php'))
		{
			return;
		}
		parent::__construct($subject, $config);
	}
	
	function onAfterStoreDonor($row) 
	{			
		$db = Factory::getDbo();
        $show_newsletter_subscription = DonationHelper::getConfigValue('show_newsletter_subscription');
        if($show_newsletter_subscription == 1 && $row->newsletter_subscription == 1) 
		{
            $subscriber = 1;
        }
		elseif($show_newsletter_subscription == 0)
		{
            $subscriber = 1;
        }
		else
		{
            $subscriber = 0;
        }
		
		$sql  = 'SELECT params FROM #__extensions WHERE folder = "jdonation" AND `element` = "acym"' ;
		$db->setQuery($sql) ;
		$params = $db->loadResult() ;
		//$params = new JParameter($params) ;			
		$params = new Registry($params);
		
		if($subscriber == 1)
		{
			if (!MailHelper::isEmailAddress($row->email))
			{
				return;
			}

            $listIds = trim($params->get('list_ids', ''));
			if($listIds != '')
			{
				$listIds = explode(',', $listIds);
				require_once JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php';
				$query = $db->getQuery(true);
				/* @var acymuserClass $userClass */
				if (class_exists(\AcyMailing\Classes\UserClass::class))
				{
					$userClass = new \AcyMailing\Classes\UserClass();
				}
				else
				{
					/* @var acymUserClass $userClass */
					$userClass = acym_get('class.user');
				}
				$userClass->checkVisitor = false;
				if (method_exists($userClass, 'getOneByEmail'))
				{
					$subId = $userClass->getOneByEmail($row->email);
				}
				else
				{
					$subId = $userClass->getUserIdByEmail($row->email);
				}

				if (!$subId)
				{
					$myUser = new stdClass();
					$myUser->email = $row->email;
					$myUser->name = trim($row->first_name . ' ' . $row->last_name);
					$myUser->cms_id = $row->user_id;
					$subId = $userClass->save($myUser);
				}

				if (is_object($subId))
				{
					$subId = $subId->id;
				}
				$userClass->subscribe($subId, $listIds);
			}
		}
	}
}	
