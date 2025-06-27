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
class EShopControllerLanguage extends BaseController
{

	public function save()
	{
		$model = $this->getModel('language');
		$input = new EshopRADInput();
		$post  = $input->post->getData(ESHOP_RAD_INPUT_ALLOWRAW);
		$model->save($post);
		$lang = $post['lang'];
		$item = $post['item'];
		$url  = 'index.php?option=com_eshop&view=language&lang=' . $lang . '&item=' . $item;
		$msg  = Text::_('ESHOP_TRANSLATION_SAVED');
		$this->setRedirect($url, $msg);
	}

	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eshop&view=dashboard');
	}
}