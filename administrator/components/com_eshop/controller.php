<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Updater\Updater;
use Joomla\Component\Installer\Administrator\Model\UpdateModel;

/**
 * EShop controller
 *
 * @package		Joomla
 * @subpackage	EShop
 * @since 1.5
 */
class EShopController extends BaseController
{
	/**
	 * Constructor function
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);							
	}
	
	/**
	 * Display information
	 *
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$input = Factory::getApplication()->input;
		$task = $this->getTask();
		$view = $input->get('view', '');
		if (!$view)
		{
			$input->set('view', 'dashboard');
		}
		EShopHelper::renderSubmenu($input->get('view', 'configuration'));
		parent::display();
		
		if (EShopHelper::getConfigValue('show_eshop_copyright', 1))
		{
		    EShopHelper::displayCopyRight();
		}
	}
	
	/**
	 * 
	 * Function to install sample data
	 */
	public function installSampleData()
	{
		$mainframe = Factory::getApplication();
		$db = Factory::getDbo();
		$sampleSql = JPATH_ADMINISTRATOR.'/components/com_eshop/sql/sample.eshop.sql';
		EShopHelper::executeSqlFile($sampleSql);
		
		$mainframe->enqueueMessage(Text::_('ESHOP_INSTALLATION_DONE'));
		$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
	}

	/**
	 * 
	 * Function to check if extension is up to date or not
	 * @return 0: error, 1: Up to date, 2: Out of date
	 */
	public function checkUpdate()
	{
		// Get the caching duration.
		$params        = ComponentHelper::getComponent('com_installer')->getParams();
		$cache_timeout = (int) $params->get('cachetimeout', 6);
		$cache_timeout = 3600 * $cache_timeout;

		// Get the minimum stability.
		$minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);

		if (EShopHelper::isJoomla4())
		{
			/* @var \Joomla\Component\Installer\Administrator\Model\UpdateModel $model */
			$model = Factory::getApplication()->bootComponent('com_installer')->getMVCFactory()
				->createModel('Update', 'Administrator', ['ignore_request' => true]);
		}
		else
		{
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models');

			/** @var InstallerModelUpdate $model */
			$model = BaseDatabaseModel::getInstance('Update', 'InstallerModel');
		}

		$model->purge();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where('`type` = "package"')
			->where('`element` = "pkg_eshop"');
		$db->setQuery($query);
		$eid = (int) $db->loadResult();
		$result['status'] = 0;
		if ($eid)
		{
			$ret = Updater::getInstance()->findUpdates($eid, $cache_timeout, $minimum_stability);
			if ($ret)
			{
				$model->setState('list.start', 0);
				$model->setState('list.limit', 0);
				$model->setState('filter.extension_id', $eid);
				$updates          = $model->getItems();
				$result['status'] = 2;
				if (count($updates))
				{
					$result['message'] = Text::sprintf('ESHOP_UPDATE_CHECKING_UPDATE_FOUND', $updates[0]->version);
				}
				else
				{
					$result['message'] = Text::sprintf('ESHOP_UPDATE_CHECKING_UPDATE_FOUND', null);
				}
			}
			else
			{
				$result['status']  = 1;
				$result['message'] = Text::_('ESHOP_UPDATE_CHECKING_UP_TO_DATE');
			}
		}
		echo json_encode($result);
		Factory::getApplication()->close();
	}
}