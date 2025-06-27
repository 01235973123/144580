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
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopViewQuote extends EShopView
{
	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 *
	 * @var $items
	 */
	protected $items;

	/**
	 *
	 * @var $countProducts
	 */
	protected $countProducts;

	/**
	 *
	 * @var $quoteData
	 */
	protected $quoteData;
	
	/**
	 *
	 * Total Data object array, each element is an price price in the quote
	 * @var object array
	 */
	protected $totalData = null;
	
	/**
	 *
	 * Final total price of the quote
	 * @var float
	 */
	protected $total = null;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $success
	 */
	protected $success;

	public function display($tpl = null)
	{
		$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		switch ($this->getLayout())
		{
			case 'mini':
				$this->displayMini($tpl);
				break;
			case 'popout':
				$this->displayPopout($tpl);
				break;
			default:
				break;
		}
	}

	/**
	 *
	 * @param   string  $tpl
	 */
	protected function displayMini($tpl = null)
	{
		//Get quote data
		$quote               = new EShopQuote();
		$items               = $quote->getQuoteData();
		$countProducts       = $quote->countProducts();
		$this->items         = $items;
		$this->countProducts = $countProducts;

		parent::display($tpl);
	}

	protected function displayPopout($tpl = null)
	{
		$app = Factory::getApplication();
		$app->getDocument()->addStyleSheet(Uri::root(true) . '/media/com_eshop/assets/colorbox/colorbox.css');
		$session         = $app->getSession();
		$tax             = new EShopTax(EShopHelper::getConfig());
		$currency        = EShopCurrency::getInstance();
		$quoteData       = $this->get('QuoteData');
		$model			 = $this->getModel();
		$model->getCosts();
		$totalData		 = $model->getTotalData();
		$total			 = $model->getTotal();
		$this->quoteData = $quoteData;
		$this->totalData = $totalData;
		$this->total	 = $total;
		$this->tax       = $tax;
		$this->currency  = $currency;

		// Success message
		if ($session->get('success'))
		{
			$this->success = $session->get('success');

			$session->clear('success');
		}

		parent::display($tpl);
	}
}