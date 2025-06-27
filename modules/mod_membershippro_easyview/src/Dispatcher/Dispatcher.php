<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Module\EasyView\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_membershippro_easyview
 *
 * @since  4.2.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
	public function dispatch()
	{
		if (!file_exists(JPATH_ROOT . '/components/com_osmembership/osmembership.php'))
		{
			return;
		}

		$params = new Registry($this->module->params);

		$menuItemId = $params->get('menu_item_id', '0');

		if (!$menuItemId)
		{
			return;
		}

		$app = Factory::getApplication();

		$menuItem = $app->getMenu()->getItem($menuItemId);

		if (!$menuItem)
		{
			return;
		}

		$this->loadLanguage();

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$request              = $menuItem->query;
		$request['Itemid']    = $menuItem->id;
		$request['hmvc_call'] = 1;

		if (!isset($request['limitstart']))
		{
			$appInput   = $app->getInput();
			$start      = $appInput->get->getInt('start', 0);
			$limitStart = $appInput->get->getInt('limitstart', 0);

			if ($start && !$limitStart)
			{
				$limitStart = $start;
			}

			$request['limitstart'] = $limitStart;
		}

		$input  = new \MPFInput($request);
		$config = [
			'default_controller_class' => 'OSMembershipController',
			'default_view'             => 'plans',
			'class_prefix'             => 'OSMembership',
			'language_prefix'          => 'OSM',
			'remember_states'          => false,
			'ignore_request'           => false,
		];

		//Initialize the controller, execute the task (display) to display the view
		\MPFController::getInstance('com_osmembership', $input, $config)
			->execute();
	}

	/**
	 * Override loadLanguage method to load component language
	 *
	 * @return void
	 */
	protected function loadLanguage()
	{
		\OSMembershipHelper::loadLanguage();
	}
}