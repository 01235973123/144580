<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Module\Subscriptions\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_mp_subscriptions
 *
 * @since  4.2.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
	/**
	 * Get layout data for module
	 *
	 * @return array|false
	 */
	protected function getLayoutData()
	{
		$params = new Registry($this->module->params);

		$rows = $this->getLatestSubscriptions((int) $params->get('count', 10));

		// In case no records found, return false so that module is not being rendered
		if ($rows === [])
		{
			return false;
		}

		$data = parent::getLayoutData();

		$data['config']       = \OSMembershipHelper::getConfig();
		$data['rows']         = $rows;
		$data['showLastName'] = \OSMembershipHelper::isFieldPublished('last_name');

		return $data;
	}

	/**
	 * The module uses component language files, so we override loadLanguage method to
	 * load component language
	 *
	 * @return void
	 */
	protected function loadLanguage()
	{
		$language = Factory::getApplication()->getLanguage();
		$language->load('com_osmembershipcommon', JPATH_ADMINISTRATOR);
		$language->load('com_osmembership', JPATH_ADMINISTRATOR);
	}

	/**
	 * Get latest subscriptions
	 *
	 * @param   int  $count
	 *
	 * @return array
	 */
	private function getLatestSubscriptions(int $count): array
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		\JLoader::register(
			'OSmembershipModelSubscriptions',
			JPATH_ADMINISTRATOR . '/components/com_osmembership/model/subscriptions.php'
		);

		/* @var \OSMembershipModelSubscriptions $model */
		$model = \MPFModel::getTempInstance('Subscriptions', 'OSMembershipModel')
			->limitstart(0)
			->limit($count)
			->filter_order('tbl.created_date')
			->filter_order_Dir('DESC');

		return $model->getData();
	}
}
