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

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerQuote extends EShopAdminController
{
	/**
	 *
	 * Function to download attached file for quote
	 */
	public function downloadFile()
	{
		$input = Factory::getApplication()->input;
		$id    = $input->getInt('id');
		$model = $this->getModel('Quote');
		$model->downloadFile($id);
	}
}