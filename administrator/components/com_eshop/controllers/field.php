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
use Joomla\Utilities\ArrayHelper;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerField extends EShopAdminController
{

	/**
	 * Constructor function
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
		$this->registerTask('un_required', 'required');
	}

	public function required()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', []);
		$cid   = ArrayHelper::toInteger($cid);
		$task  = $this->getTask();
		if ($task == 'required')
		{
			$state = 1;
		}
		else
		{
			$state = 0;
		}
		$model = $this->getModel('Field');
		$model->required($cid, $state);
		$msg = Text::_('ESHOP_FIELD_REQUIRED_STATE_UPDATED');
		$this->setRedirect(Route::_('index.php?option=com_eshop&view=fields', false), $msg);
	}


}