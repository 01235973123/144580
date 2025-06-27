<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class DonationController extends OSFControllerAdmin
{

	/**
	 * Display information
	 */
	function display($cachable = false, array $urlparams = Array())
	{
		$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle('com_jdonation.style', Uri::root() . 'administrator/components/com_jdonation/assets/css/style.css');
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$wa->registerAndUseStyle('com_jdonation.style4',Uri::root() . 'administrator/components/com_jdonation/assets/css/style4.css');
		}
		parent::display($cachable, $urlparams);
		if ($this->input->getCmd('format', 'html') == 'html')
		{
			DonationHelper::displayCopyRight();
		}
	}

	public function download_file()
	{

		$filePath = 'media/com_jdonation/files';
		$fileName = $this->input->get('file_name', '', 'none');
		if (file_exists(JPATH_ROOT . '/' . $filePath . '/' . $fileName))
		{
			while (@ob_end_clean()) ;
			DonationHelper::processDownload(JPATH_ROOT . '/' . $filePath . '/' . $fileName, $fileName);
			exit();
		}
		else
		{
			$this->app->enqueueMessage(Text::_('JD_FILE_NOT_EXIST'));
			$this->app->redirect('index.php?option=com_jdonation');
		}
	}

	public function retrieveSatispayKey()
	{
		$db = Factory::getContainer()->get('db');
		require_once(JPATH_ROOT . '/components/com_jdonation/payments/satispay/init.php');
		$db->setQuery("Select * from #__jd_payment_plugins where name like 'os_satispay'");
		$plugin = $db->loadObject();
		if($plugin->id > 0)
		{
			$pluginParams		= new Registry($plugin->params);
			$satisfy_mode		= $pluginParams->get('satisfy_mode',0);
			$activation_code	= $pluginParams->get('activation_code','');
			if($activation_code != "")
			{
				if($satisfy_mode == 0)
				{
					\SatispayGBusiness\Api::setSandbox(true);
				}
				else
				{
					\SatispayGBusiness\Api::setSandbox(false);
				}
				$authentication = \SatispayGBusiness\Api::authenticateWithToken($activation_code);
				$publicKey		= $authentication->publicKey;
				$privateKey		= $authentication->privateKey;
				$keyId			= $authentication->keyId;
				if($publicKey != "" && $privateKey != "" && $keyId != "")
				{
					$pluginParams->set('publicKey', $publicKey);
					$pluginParams->set('privateKey', $privateKey);
					$pluginParams->set('keyId', $keyId);
					$params = $pluginParams->toString();
					$db->setQuery("Update #__jd_payment_plugins set `params` = ".$db->quote($params)." where name like 'os_satispay'");
					$db->execute();
					echo "Done now, you can close this window";
				}
			}
		}
		return;
		
	}

	public function upgrade()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/install.jdonation.php';
		com_jdonationInstallerScript::updateDatabaseSchema();
	}

	/**
	 * Check to see the installed version is up to date or not
	 *
	 * @return int 0 : error, 1 : Up to date, 2 : outof date
	 */
	public function check_update()
	{
		$component     = ComponentHelper::getComponent('com_installer');
		$params        = $component->params;
		$cache_timeout = (int) $params->get('cachetimeout', 6);
		$cache_timeout = 3600 * $cache_timeout;
		
		// Get the minimum stability.
		$minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);

		if (DonationHelper::isJoomla4())
		{
			/* @var \Joomla\Component\Installer\Administrator\Model\UpdateModel $model */
			$model = $this->app->bootComponent('com_installer')->getMVCFactory()
				->createModel('Update', 'Administrator', ['ignore_request' => true]);
		}
		else
		{
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models');

			/** @var InstallerModelUpdate $model */
			$model = BaseDatabaseModel::getInstance('Update', 'InstallerModel');
		}

		$model->purge();

		$db = Factory::getContainer()->get('db');
		$document = Factory::getApplication()->getDocument();
		$installedVersion = DonationHelper::getInstalledVersion();
		$db    = Factory::getContainer()->get('db');

		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where('`type` = "package"')
			->where('`element` = "pkg_joomdonation"');
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
					$style = '#update-check span{
							font-weight:bold;
							color:red;
						}';
					$result['status']  = 2;
					$result['message'] = Text::sprintf('JD_UPDATE_CHECKING_UPDATEFOUND', $updates[0]->version);
				}
				else
				{
					$style = '#update-check span{
							font-weight:bold;
							color:green;
						}';
					$result['status']  = 2;
					$result['message'] = Text::sprintf('JD_UPDATE_CHECKING_UPDATEFOUND', null);
				}
			}
			else
			{
				$style = '#update-check span{
							font-weight:bold;
							color:green;
						}';
				$document->addStyleDeclaration($style);
				$result['status']  = 1;
				$result['message'] = Text::_('JD_UPDATE_CHECKING_UPTODATE');
			}
		}
		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * Fix "Row size too large" issue
	 */
	public function fix_row_size()
	{
		$db = Factory::getContainer()->get('db');

		$tables = [
			'#__jd_campaigns',
			'#__jd_donors',			
		];

		foreach ($tables as $table)
		{
			$query = "ALTER TABLE `$table` ROW_FORMAT = DYNAMIC";
			$db->setQuery($query)
				->execute();
		}
	}


	/**
	 * Download donation receipt
	 *
	 */
	public function download_receipt()
	{
		DonationHelper::noindex();
		$user = Factory::getApplication()->getIdentity();
		$f	 = $this->input->getInt('f', 0);
		if (!$user && $f == 0)
		{
			return;
		}
		$id  = $this->input->getInt('id');
		$db = Factory::getContainer()->get('db');
		$row = new DonationTableDonor($db); //Table::getInstance('Donor', 'DonationTable');
		$row->load($id);

		//Validation is OK, we can now process download the receipt
		DonationHelper::downloadInvoice($id);
	}

	public function gotojddasboard()
	{
		Factory::getApplication()->redirect('index.php?option=com_jdonation');
	}
} 
