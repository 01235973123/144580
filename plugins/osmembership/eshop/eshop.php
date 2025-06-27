<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2014 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class plgOSMembershipEshop extends CMSPlugin
{

	private $canRun = false;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_eshop/eshop.php'))
		{
			$this->canRun = true;
			require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
			require_once JPATH_ROOT . '/components/com_eshop/helpers/api.php';
			Factory::getLanguage()->load('plg_osmembership_eshop', JPATH_ADMINISTRATOR);
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmembership/tables');
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eshop/tables');
		}
	}

	/**
	 * Render settings from
	 *
	 * @param   PlanOSMembership  $row
	 */
	public function onEditSubscriptionPlan($row)
	{
		if (!$this->canRun)
		{
			return;
		}

		ob_start();
		$this->_drawSettingForm($row);
		$form = ob_get_clean();

		return ['title' => Text::_('PLG_OSMEMBERSHIP_ESHOP_CUSTOMER_GROUPS_SETTINGS'), 'form' => $form];
	}

	/**
	 * Store setting into database
	 *
	 * @param   PlanOsMembership  $row
	 * @param   Boolean           $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveSubscriptionPlan($context, $row, $data, $isNew)
	{
		if (!$this->canRun)
		{
			return;
		}

		$params = new Registry($row->params);
		$params->set('eshop_customer_group_id', (int) $data['eshop_customer_group_id']);
		$row->params = $params->toString();
		$row->store();
	}

	/**
	 * Create customer record if needed
	 *
	 * @param   SubscriberOsmembership  $row
	 */
	public function onAfterStoreSubscription($row)
	{
		if ($this->canRun && $row->user_id)
		{
			$db        = Factory::getDbo();
			$rowFields = OSMembershipHelper::getProfileFields($row->plan_id, true, $row->language);
			$data      = OSMembershipHelper::getProfileData($row, $row->plan_id, $rowFields);
			//Map fields in Membership Pro with Fields in Eshop
			if (!isset($data['country']) || !$data['country'])
			{
				$country = OSMembershipHelper::getConfigValue('default_country');
			}
			else
			{
				$country = $data['country'];
			}
			$query = $db->getQuery(true);
			$query->select('iso_code_3')
				->from('#__eshop_countries')
				->where('country_name=' . $db->quote($country));
			$db->setQuery($query);
			$data['country_code'] = $db->loadResult();
			$fieldsMapping        = [
				'first_name'   => 'firstname',
				'last_name'    => 'lastname',
				'organization' => 'company',
				'address'      => 'address_1',
				'address2'     => 'address_2',
				'phone'        => 'telephone',
				'zip'          => 'postcode',
				'fax'          => 'fax',
				'city'         => 'city',
				'email'        => 'email',
			];
			foreach ($fieldsMapping as $membershipProField => $eshopField)
			{
				if (isset($data[$membershipProField]))
				{
					$data[$eshopField] = $data[$membershipProField];
				}
			}
			if (isset($data['state']))
			{
				$query->clear();
				$query->select('state_3_code')
					->from('#__osmembership_states AS a')
					->innerJoin('#__osmembership_countries AS b ON a.country_id=b.country_id')
					->where('a.state_name=' . $db->quote($data['state']))
					->where('b.name=' . $db->quote($country));
				$db->setQuery($query);
				$data['zone_code'] = $db->loadResult();
			}
			EShopAPI::addCustomer($row->user_id, $data);
		}
	}

	/**
	 * Run when a membership activated
	 *
	 * @param   PlanOsMembership  $row
	 */
	public function onMembershipActive($row)
	{
		if ($this->canRun && $row->user_id)
		{
			$config = OSMembershipHelper::getConfig();

			if ($config->create_account_when_membership_active)
			{
				$this->onAfterStoreSubscription($row);
			}

			$plan = Table::getInstance('Osmembership', 'Plan');
			$plan->load($row->plan_id);
			$params          = new Registry($plan->params);
			$customerGroupId = (int) $params->get('eshop_customer_group_id');

			if ($customerGroupId)
			{
				EShopAPI::setCustomerGroup($row->user_id, $customerGroupId);
			}
		}
	}

	/**
	 * Run when a membership expired
	 *
	 * @param   PlanOsMembership  $row
	 */
	public function onMembershipExpire($row)
	{
		if ($this->canRun && $row->user_id)
		{
			$plan = Table::getInstance('Osmembership', 'Plan');
			$plan->load($row->plan_id);
			$activePlans = OSMembershipHelper::getActiveMembershipPlans($row->user_id, [$row->id]);
			$db          = Factory::getDbo();
			$query       = $db->getQuery(true);
			$query->select('params')
				->from('#__osmembership_plans')
				->where('id IN  (' . implode(',', $activePlans) . ')')
				->order('price DESC');
			$db->setQuery($query);
			$rowPlans        = $db->loadObjectList();
			$customerGroupId = (int) EShopHelper::getConfigValue('customergroup_id');

			if (count($rowPlans))
			{
				foreach ($rowPlans as $rowPlan)
				{
					$planParams          = new Registry($rowPlan->params);
					$planCustomerGroupId = (int) $planParams->get('eshop_customer_group_id');

					if ($planCustomerGroupId)
					{
						$customerGroupId = $planCustomerGroupId;
						break;
					}
				}
			}

			EShopAPI::setCustomerGroup($row->user_id, $customerGroupId);
		}
	}

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   object  $row
	 *
	 */
	public function _drawSettingForm($row)
	{
		$params          = new Registry($row->params);
		$customerGroupId = (int) $params->get('eshop_customer_group_id', 0);
		$options         = [];
		$options[]       = HTMLHelper::_('select.option', 0, Text::_('PLG_OSMEMBERSHIP_ESHOP_SELECT_GROUP'), 'id', 'customergroup_name');
		$options         = array_merge($options, EShopAPI::getCustomerGroups());

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}
}