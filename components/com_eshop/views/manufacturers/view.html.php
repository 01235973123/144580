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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewManufacturers extends EShopView
{
	/**
	 *
	 * @var $warning
	 */
	protected $warning;

	/**
	 *
	 * @var $config
	 */
	protected $config;

	/**
	 *
	 * @var $items
	 */
	protected $items;

	/**
	 *
	 * @var $manufacturersPerRow
	 */
	protected $manufacturersPerRow;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

	/**
	 *
	 * @var $params
	 */
	protected $params;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app     = Factory::getApplication();
		$session = $app->getSession();
		$params  = $app->getParams();
		$model   = $this->getModel();

		$this->setPageTitle($params->get('page_title', ''));

		$session->set('continue_shopping_url', Uri::getInstance()->toString());

		if ($session->get('warning'))
		{
			$this->warning = $session->get('warning');
			$session->clear('warning');
		}

		$this->config              = EShopHelper::getConfig();
		$this->items               = $model->getData();
		$this->manufacturersPerRow = EShopHelper::getConfigValue('items_per_row', 3);
		$this->pagination          = $model->getPagination();
		$this->params              = $params;
		$this->bootstrapHelper     = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		$langCode = Factory::getLanguage()->getTag();

		foreach ($this->items as $item)
		{
			EShopHelper::$manufacturersAlias[$langCode . '.' . $item->id] = strlen($item->manufacturer_alias ?? '') > 0 ? $item->manufacturer_alias : $item->manufacturer_name;
		}

		parent::display($tpl);
	}
}