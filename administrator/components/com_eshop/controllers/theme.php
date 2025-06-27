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
class EShopControllerTheme extends EShopAdminController
{
	/**
	 * Install a theme package
	 */
	public function install()
	{
		$theme = $this->input->files->get('theme_package', null, 'raw');
		$model = $this->getModel('theme');

		try
		{
			$model->install($theme);
			$this->setRedirect(Route::_('index.php?option=com_eshop&view=themes', false), Text::_('ESHOP_THEME_INSTALLED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_eshop&view=themes', false), Text::_('ESHOP_THEME_INSTALL_ERROR'));
		}
	}
}