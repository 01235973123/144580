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
use Joomla\CMS\Router\Route;

class EShopModelCompare extends EShopModel
{

	/**
	 *
	 * Constructor
	 * @since 1.5
	 */
	public function __construct($config = [])
	{
		parent::__construct();
	}

	public function add($productId)
	{
		$json    = [];
		$user    = Factory::getUser();
		$session = Factory::getApplication()->getSession();
		$compare = $session->get('compare');
		if (!$compare)
		{
			$compare = [];
		}
		$productInfo = EShopHelper::getProduct($productId, Factory::getLanguage()->getTag());
		if (is_object($productInfo))
		{
			if (!in_array($productId, $compare))
			{
				if (count($compare) >= 4)
				{
					array_shift($compare);
				}
				$compare[] = $productId;
				$session->set('compare', $compare);
			}
			$viewProductLink            = Route::_(EShopRoute::getProductRoute($productId, EShopHelper::getProductCategory($productId)));
			$viewCompareLink            = Route::_(EShopRoute::getViewRoute('compare'));
			$message                    = '<div class="compare-message">' . sprintf(
					Text::_('ESHOP_ADD_TO_COMPARE_SUCCESS_MESSAGE'),
					$viewProductLink,
					$productInfo->product_name,
					$viewCompareLink
				) . '</div>';
			$json['success']['message'] = '<h1>' . Text::_('ESHOP_PRODUCT_COMPARE') . '</h1>' . $message;
		}

		return $json;
	}
}