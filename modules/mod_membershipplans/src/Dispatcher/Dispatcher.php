<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Module\MembershipPlans\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_membershipplans
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

		$this->loadLanguage();

		$params = new Registry($this->module->params);

		$planIds = $params->get('plan_ids', '*');
		$layout  = $params->get('layout_type', 'default');

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$request = [
			'option'              => 'com_osmembership',
			'view'                => 'plans',
			'layout'              => $layout,
			'filter_plan_ids'     => $planIds,
			'limit'               => 0,
			'hmvc_call'           => 1,
			'Itemid'              => $params->get('item_id', \OSMembershipHelper::getItemid()),
			'recommended_plan_id' => $params->get('recommended_plan_id'),
			'number_columns'      => $params->get('number_columns', 0),
		];
		$input   = new \MPFInput($request);

		$config = [
			'default_controller_class' => 'OSMembershipController',
			'default_view'             => 'plans',
			'class_prefix'             => 'OSMembership',
			'language_prefix'          => 'OSM',
			'remember_states'          => false,
			'ignore_request'           => false,
		];

		//Initialize the controller, execute the task and perform redirect if needed
		\MPFController::getInstance('com_osmembership', $input, $config)
			->execute();
	}

	/**
	 * The module use component language, so we override loadLanguage method to load component
	 * language files
	 *
	 * @return void
	 */
	protected function loadLanguage()
	{
		\OSMembershipHelper::loadLanguage();
	}
}
