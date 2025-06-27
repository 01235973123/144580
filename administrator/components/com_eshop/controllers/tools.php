<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerTools extends BaseController
{
	/**
	 *
	 * Migrate customers from Joomla core users
	 */
	public function migrateFromJoomla()
	{
		$model = $this->getModel('tools');
		$model->migrateFromJoomla();
		$this->setRedirect('index.php?option=com_eshop&view=customers', Text::_('ESHOP_MIGRATE_FROM_JOOMLA_SUCESS'));
	}

	/**
	 *
	 * Migrate customers from Membership Pro subscribers
	 */
	public function migrateFromMembershipPro()
	{
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/osmembership.php'))
		{
			$model = $this->getModel('tools');
			$model->migrateFromMembershipPro();
			$this->setRedirect('index.php?option=com_eshop&view=customers', Text::_('ESHOP_MIGRATE_FROM_MEMBERSHIP_SUCCESS'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_MIGRATE_FROM_MEMBERSHIP_NOT_INSTALL'));
		}
	}

	/**
	 *
	 * Clean data
	 */
	public function cleanData()
	{
		$model = $this->getModel('tools');
		$model->cleanData();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_CLEAN_DATA_SUCCESS'));
	}

	/**
	 *
	 * Add sample data
	 */
	public function addSampleData()
	{
		$model = $this->getModel('tools');
		$model->addSampleData();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_ADD_SAMPLE_DATA_SUCCESS'));
	}

	/**
	 *
	 * Function to synchronize data
	 */
	public function synchronizeData()
	{
		$model = $this->getModel('tools');
		$model->synchronizeData();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_SYNCHRONIZE_DATA_SUCCESS'));
	}

	/**
	 *
	 * Function to migrate virtuemart
	 */
	public function migrateVirtuemart()
	{
		$model = $this->getModel('tools');
		$model->migrateVirtuemart();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_MIGRATE_VIRTUEMART_SUCCESS'));
	}

	/**
	 *
	 * Reset hits data
	 */
	public function resetHits()
	{
		$model = $this->getModel('tools');
		$model->resetHits();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_RESET_HITS_SUCCESS'));
	}

	/**
	 *
	 * Purge urls data
	 */
	public function purgeUrls()
	{
		$model = $this->getModel('tools');
		$model->purgeUrls();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_PURGE_URLS_SUCCESS'));
	}

	/**
	 *
	 * Clean Orders Data
	 */
	public function cleanOrders()
	{
		$model = $this->getModel('tools');
		$model->cleanOrders();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_RESET_ORDERS_SUCCESS'));
	}
	
	/**
	 *
	 * Function to migrate j2store
	 */
	public function migrateJ2store()
	{
		$model = $this->getModel('tools');
		$model->migrateJ2store();
		$this->setRedirect('index.php?option=com_eshop&view=dashboard', Text::_('ESHOP_MIGRATE_J2STORE_SUCCESS'));
	}
}