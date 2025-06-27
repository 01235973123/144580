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

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewQuote extends EShopView
{

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

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $showCaptcha
	 */
	protected $showCaptcha;

	/**
	 *
	 * @var $captcha
	 */
	protected $captcha;

	/**
	 *
	 * @var $captchaPlugin
	 */
	protected $captchaPlugin;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app      = Factory::getApplication();
		$document = $app->getDocument();

		if (!EShopHelper::isQuoteModeGlobal())
		{
			$app->getSession()->set('warning', Text::_('ESHOP_QUOTE_CART_MODE_OFF'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$menuItem = $app->getMenu()->getActive();

			if ($menuItem && (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'frontpage')))
			{
				$pathway = $app->getPathway();
				$pathUrl = EShopRoute::getViewRoute('frontpage');
				$pathway->addItem(Text::_('ESHOP_QUOTE_CART'), $pathUrl);
			}

			$document->addStyleSheet(Uri::root(true) . '/media/com_eshop/assets/colorbox/colorbox.css');

			$this->setPageTitle(Text::_('ESHOP_QUOTE_CART'));

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

			$document->addScriptDeclaration(EShopHtmlHelper::getZonesArrayJs());

			//Country list
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, country_name AS name')
				->from('#__eshop_countries')
				->where('published=1')
				->order('country_name');
			$db->setQuery($query);
			$options             = [];
			$options[]           = HTMLHelper::_('select.option', 0, Text::_('ESHOP_PLEASE_SELECT'), 'id', 'name');
			$options             = array_merge($options, $db->loadObjectList());
			$countryId           = EShopHelper::getConfigValue('country_id');
			$lists['country_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'country_id',
				' class="input-xlarge form-select" ',
				'id',
				'name',
				$countryId
			);

			//Zone list
			$query->clear()
				->select('id, zone_name')
				->from('#__eshop_zones')
				->where('country_id=' . (int) $countryId)
				->where('published=1')
				->order('zone_name');

			$db->setQuery($query);
			$options          = [];
			$options[]        = HTMLHelper::_('select.option', 0, Text::_('ESHOP_PLEASE_SELECT'), 'id', 'zone_name');
			$options          = array_merge($options, $db->loadObjectList());
			$lists['zone_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'zone_id',
				' class="input-xlarge form-select" ',
				'id',
				'zone_name',
				EShopHelper::getConfigValue('zone_id')
			);

			$this->lists = $lists;

			//Captcha
			$this->showCaptcha = false;

			if (EShopHelper::getConfigValue('enable_quote_captcha'))
			{
				$captchaPlugin = $app->get('captcha') ?: 'recaptcha';
				$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

				if ($plugin)
				{
					$this->showCaptcha = true;
					$this->captcha     = Captcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
				}
				else
				{
					$app->enqueueMessage(Text::_('ESHOP_CAPTCHA_IS_NOT_ACTIVATED'), 'error');
				}

				$this->captchaPlugin = $captchaPlugin;
			}

			$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

			parent::display($tpl);
		}
	}
}