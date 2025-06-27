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
class EShopControllerConfiguration extends BaseController
{
	/**
	 * Save the category
	 *
	 */
	public function save()
	{
		$input = new EshopRADInput();
		$post  = $input->getData(ESHOP_RAD_INPUT_ALLOWRAW);

		if (!isset($post['customer_group_display']))
		{
			$post['customer_group_display'] = '';
		}

		if (!isset($post['sort_options']))
		{
			$post['sort_options'] = '';
		}

		$model = $this->getModel('configuration');
		$ret   = $model->store($post);

		if ($ret)
		{
			$msg = Text::_('ESHOP_CONFIGURATION_SAVED');
		}
		else
		{
			$msg = Text::_('ESHOP_CONFIGURATION_SAVING_ERROR');
		}

		$this->setRedirect('index.php?option=com_eshop&view=configuration', $msg);
	}

	/**
	 * Cancel the configuration
	 *
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eshop&view=dashboard');
	}
}