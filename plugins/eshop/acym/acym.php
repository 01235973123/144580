<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class plgEshopAcym extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object    $subject
	 * @param   Registry  $config
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		Factory::getLanguage()->load('plg_eshop_acym', JPATH_ADMINISTRATOR);
	}

	/**
	 * Render settings form
	 *
	 * @param   EshopTableProduct  $row
	 *
	 * @return array
	 */
	public function onEditProduct($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);

		return [
			'title' => Text::_('PLG_ESHOP_ACYM_LIST_SETTINGS'),
			'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EshopTableProduct  $row
	 * @param   Boolean            $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveProduct($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$params = new Registry($row->params);

		if (isset($data['acymailing6_list_ids']) && count($data['acymailing6_list_ids']))
		{
			$params->set('acymailing6_list_ids', implode(',', $data['acymailing6_list_ids']));
		}
		else
		{
			$params->set('acymailing6_list_ids', '');
		}

		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Run when registration record stored to database
	 *
	 * @param   EshopTableOrder  $row
	 */
	public function onAfterCompleteOrder($row)
	{
		$session = Factory::getApplication()->getSession();

		if (!$this->canRun($row) || !$session->get('newsletter_interest'))
		{
			return;
		}

		//Get order products
		$query = $this->db->getQuery(true);
		$query->select('p.params')
			->from('#__eshop_products AS p')
			->innerJoin('#__eshop_orderproducts AS op ON (p.id = op.product_id)')
			->where('order_id = ' . intval($row->id));
		$this->db->setQuery($query);
		$products = $this->db->loadObjectList();

		for ($i = 0; $n = count($products), $i < $n; $i++)
		{
			$params  = new Registry($products[$i]->params);
			$listIds = $params->get('acymailing6_list_ids', '');

			if ($listIds != '')
			{
				$listIds = explode(',', $listIds);
				$this->subscribeToAcyMailingLists($row, $listIds);
			}

			$listIds = explode(',', $params->get('acymailing6_list_ids', ''));
		}
	}

	/**
	 * @param   EshopTableOrder  $row
	 * @param   array            $listIds
	 */
	private function subscribeToAcyMailingLists($row, $listIds)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php';

		/* @var acymuserClass $userClass */
		if (class_exists(\AcyMailing\Classes\UserClass::class))
		{
			$userClass = new \AcyMailing\Classes\UserClass();
		}
		else
		{
			/* @var acymUserClass $userClass */
			$userClass = acym_get('class.user');
		}
		
		$userClass->checkVisitor = false;

		if (method_exists($userClass, 'getOneByEmail'))
		{
			$subId = $userClass->getOneByEmail($row->email);
		}
		else
		{
			$subId = $userClass->getUserIdByEmail($row->email);
		}

		if (!$subId)
		{
			$myUser         = new stdClass();
			$myUser->email  = $row->email;
			$myUser->name   = trim($row->firstname . ' ' . $row->lastname);
			$myUser->cms_id = $row->customer_id;

			$subId = $userClass->save($myUser);
		}

		if (is_object($subId))
		{
			$subId = $subId->id;
		}

		$userClass->subscribe($subId, $listIds);
	}

	/**
	 * Display form allows users to change settings on event add/edit screen
	 *
	 * @param   EshopTableProduct  $row
	 */
	private function drawSettingForm($row)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php';

		if ($row->id)
		{
			$params  = new Registry($row->params);
			$listIds = explode(',', $params->get('acymailing6_list_ids', ''));
		}
		else
		{
			$listIds = [];
		}

		if (class_exists(\AcyMailing\Classes\ListClass::class))
		{
			$listClass = new \AcyMailing\Classes\ListClass();
		}
		else
		{
			/* @var acymlistClass $listClass */
			$listClass = acym_get('class.list');
		}

		$allLists = $listClass->getAllWithIdName();


		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EshopTableProduct  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_acym/acym.php'))
		{
			return false;
		}

		return true;
	}
}
