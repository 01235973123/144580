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
class EShopControllerOrders extends BaseController
{
	/**
	 *
	 * Function to process batch orders
	 */
	public function batch()
	{
		$input = new EshopRADInput();
		$post  = $input->post->getData(ESHOP_RAD_INPUT_ALLOWRAW);
		$model = $this->getModel('orders');
		$ret   = $model->batch($post);

		if ($ret)
		{
			$msg = Text::_('ESHOP_BATCH_ORDER_SUCCESSFULLY');
		}
		else
		{
			$msg = Text::_('ESHOP_BATCH_ORDER_ERROR');
		}

		$this->setRedirect('index.php?option=com_eshop&view=orders', $msg);
	}

	/**
	 *
	 * Cancel function
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eshop&view=orders');
	}
}