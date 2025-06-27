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
use Joomla\CMS\Router\Route;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerPayment extends EShopAdminController
{
	/**
	 * Install a payment plugin
	 */
	public function install()
	{
		$plugin = $this->input->files->get('plugin_package', null, 'raw');
		$model  = $this->getModel('payment');

		try
		{
			$model->install($plugin);
			$this->setRedirect(Route::_('index.php?option=com_eshop&view=payments', false), Text::_('ESHOP_PLUGIN_INSTALLED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_eshop&view=payments', false), Text::_('ESHOP_PLUGIN_INSTALL_ERROR'));
		}
	}
}