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
class EShopControllerProducts extends BaseController
{
	/**
	 *
	 * Function to process batch products
	 */
	public function batch()
	{
		$input = new EshopRADInput();
		$post  = $input->post->getData(ESHOP_RAD_INPUT_ALLOWRAW);
		$model = $this->getModel('products');
		$ret   = $model->batch($post);

		if ($ret)
		{
			$msg = Text::_('ESHOP_BATCH_PRODUCT_SUCCESSFULLY');
		}
		else
		{
			$msg = Text::_('ESHOP_BATCH_PRODUCT_ERROR');
		}

		$this->setRedirect('index.php?option=com_eshop&view=products', $msg);
	}

	/**
	 *
	 * Function to save products inventory
	 */
	public function saveInventory()
	{
		$input = new EshopRADInput();
		$post  = $input->post->getData(ESHOP_RAD_INPUT_ALLOWRAW);
		$model = $this->getModel('products');
		$ret   = $model->saveInventory($post);

		if ($ret)
		{
			$msg = Text::_('ESHOP_SAVE_INVENTORY_SUCCESSFULLY');
		}
		else
		{
			$msg = Text::_('ESHOP_SAVE_INVENTORY_ERROR');
		}

		$this->setRedirect('index.php?option=com_eshop&view=products', $msg);
	}

	/**
	 *
	 * Cancel function
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eshop&view=products');
	}
}