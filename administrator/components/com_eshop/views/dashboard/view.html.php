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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Updater\Updater;
use Joomla\Component\Installer\Administrator\Model\UpdateModel;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewDashboard extends HtmlView
{
	/**
	 *
	 * @var $shopStatistics
	 */
	protected $shopStatistics;

	/**
	 *
	 * @var $recentOrders
	 */
	protected $recentOrders;

	/**
	 *
	 * @var $recentReviews
	 */
	protected $recentReviews;

	/**
	 *
	 * @var $topSales
	 */
	protected $topSales;

	/**
	 *
	 * @var $topHits
	 */
	protected $topHits;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $defaultCurrency
	 */
	protected $defaultCurrency;

	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	/**
	 *
	 * @var $model
	 */
	protected $model;

	/**
	 * Update result
	 *
	 * @var array
	 */
	protected $updateResult = [];

	public function display($tpl = null)
	{
		HTMLHelper::_('jquery.framework');
		$db                  = Factory::getDbo();
		$query               = $db->getQuery(true);
		$currency            = EShopCurrency::getInstance();
		$defaultCurrencyCode = EShopHelper::getConfigValue('default_currency_code');
		$query->select('*')
			->from('#__eshop_currencies')
			->where('currency_code = ' . $db->quote($defaultCurrencyCode));
		$db->setQuery($query);
		$defaultCurrency       = $db->loadObject();
		$this->shopStatistics  = $this->get('ShopStatistics');
		$this->recentOrders    = $this->get('RecentOrders');
		$this->recentReviews   = $this->get('RecentReviews');
		$this->topSales        = $this->get('TopSales');
		$this->topHits         = $this->get('TopHits');
		$this->currency        = $currency;
		$this->defaultCurrency = $defaultCurrency;
		$nullDate              = $db->getNullDate();
		$this->nullDate        = $nullDate;
		$this->model           = $this->getModel();

		$this->updateResult = $this->checkUpdate();

		if ($this->updateResult['status'] == 2 && EShopHelper::getConfigValue('show_eshop_update', 1))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('ESHOP_UPDATE_AVAILABLE', 'index.php?option=com_installer&view=update', $this->updateResult['version'])
			);
		}

		parent::display($tpl);
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
			/* @var UpdateModel $model */
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

		$result['status']  = 0;
		$result['version'] = '';

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
					$result['version'] = $updates[0]->version;
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

		return $result;
	}
}