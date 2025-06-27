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
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerCurrency extends BaseController
{
	/**
	 * Change currency
	 */
	public function change()
	{
		$app     = Factory::getApplication();
		$session = $app->getSession();

		$currencyCode = $this->input->post->getString('currency_code', null);

		if (!$session->get('currency_code') || $session->get('currency_code') != $currencyCode)
		{
			$session->set('currency_code', $currencyCode);
		}

		$cookieCurrencyCode = $this->input->cookie->getString('currency_code');

		if (!$cookieCurrencyCode || $cookieCurrencyCode != $currencyCode)
		{
			setcookie('currency_code', $currencyCode, time() + 60 * 60 * 24 * 30);

			$this->input->cookie->set('currency_code', $currencyCode);
		}

		$return = base64_decode($this->input->getString('return'));

		$app->redirect($return);
	}
}