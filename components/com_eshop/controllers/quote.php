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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

require_once __DIR__ . '/jsonresponse.php';

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerQuote extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 *
	 * Function to add a product to the quote
	 */
	public function add()
	{
		$quote = new EShopQuote();
		$json  = [];

		$productId = $this->input->getInt('id');
		$quantity  = $this->input->getInt('quantity', 0);

		if ($quantity <= 0)
		{
			$quantity = 1;
		}

		$options = $this->input->get('options', [], 'array');
		$options = array_filter($options);

		//Validate options first
		$productOptions = EShopHelper::getProductOptions($productId, Factory::getLanguage()->getTag());

		for ($i = 0; $n = count($productOptions), $i < $n; $i++)
		{
			$productOption = $productOptions[$i];

			if ($productOption->required && empty($options[$productOption->product_option_id]))
			{
				$json['error']['option'][$productOption->product_option_id] = $productOption->option_name . ' ' . Text::_('ESHOP_REQUIRED');
			}
		}

		if (!$json)
		{
			$product = EShopHelper::getProduct($productId, Factory::getLanguage()->getTag());
			$quote->add($productId, $quantity, $options);
			$viewProductLink = Route::_(EShopRoute::getProductRoute($productId, EShopHelper::getProductCategory($productId)));
			$viewQuoteLink   = Route::_(EShopRoute::getViewRoute('quote'));

			$message                    = '<div>' . sprintf(
					Text::_('ESHOP_ADD_TO_QUOTE_SUCCESS_MESSAGE'),
					$viewProductLink,
					$product->product_name,
					$viewQuoteLink
				) . '</div>';
			$json['success']['message'] = $message;
			$json['time']               = time();
		}
		else
		{
			$json['redirect'] = Route::_(EShopRoute::getProductRoute($productId, EShopHelper::getProductCategory($productId)));
		}

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to update quantity of a product in the quote
	 */
	public function update()
	{
		$session = Factory::getApplication()->getSession();
		$session->set('success', Text::_('ESHOP_QUOTE_UPDATE_MESSAGE'));

		$key      = $this->input->getString('key');
		$quantity = $this->input->getInt('quantity');

		$quote = new EShopQuote();
		$quote->update($key, $quantity);
	}

	/**
	 *
	 * Function to update quantity of all products in the quote
	 */
	public function updates()
	{
		$session = Factory::getApplication()->getSession();
		$session->set('success', Text::_('ESHOP_QUOTE_UPDATE_MESSAGE'));

		$key      = $this->input->get('key', [], 'array');
		$quantity = $this->input->get('quantity', [], 'array');

		$quote = new EShopQuote();
		$quote->updates($key, $quantity);
	}

	/**
	 *
	 * Function to remove a product from the quote
	 */
	public function remove()
	{
		$key   = $this->input->getString('key');
		$quote = new EShopQuote();

		$quote->remove($key);

		Factory::getApplication()->getSession()->set('success', Text::_('ESHOP_QUOTE_REMOVED_MESSAGE'));
	}

	/**
	 * Function to process quote
	 */
	public function processQuote()
	{
		$post = $this->input->post->getArray();

		/* @var EShopModelQuote $model */
		$model = $this->getModel('Quote');
		$json  = $model->processQuote($post);

		$this->sendJsonResponse($json);
	}
}