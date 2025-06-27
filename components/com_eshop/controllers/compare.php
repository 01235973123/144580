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
class EShopControllerCompare extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 *
	 * Function to add a product into the compare
	 */
	public function add()
	{
		$productId = $this->input->getInt('product_id', 0);

		/* @var EShopModelCompare $model */
		$model = $this->getModel('Compare');
		$json  = $model->add($productId);

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to remove a product from the compare
	 */
	public function remove()
	{
		$session   = Factory::getApplication()->getSession();
		$compare   = $session->get('compare');
		$productId = $this->input->getInt('product_id', 0);
		$key       = array_search($productId, $compare);

		if ($key !== false)
		{
			unset($compare[$key]);
		}

		$session->set('compare', $compare);
		$session->set('success', Text::_('ESHOP_COMPARE_REMOVED_MESSAGE'));

		$json             = [];
		$json['redirect'] = Route::_(EShopRoute::getViewRoute('compare'));

		$this->sendJsonResponse($json);
	}
}