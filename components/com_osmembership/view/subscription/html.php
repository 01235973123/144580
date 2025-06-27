<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class OSMembershipViewSubscriptionHtml extends MPFViewHtml
{
	/**
	 * The subscription record
	 *
	 * @var stdClass
	 */
	protected $item;

	/**
	 * The subscription edit form
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Display view
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display()
	{
		$user  = Factory::getApplication()->getIdentity();
		$model = $this->getModel();
		$item  = $model->getData();

		if ($item->user_id != $user->id && !$user->authorise('core.admin', 'com_osmembership'))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('OSM_INVALID_ACTION'));
			$app->redirect(Uri::root(), 403);
		}

		//Form
		$rowFields = OSMembershipHelper::getProfileFields($item->plan_id, true, $item->language);
		$data      = OSMembershipHelper::getProfileData($item, $item->plan_id, $rowFields);
		$form      = new MPFForm($rowFields);
		$form->setData($data)->bindData();
		$form->buildFieldsDependency(false);

		$this->config = OSMembershipHelper::getConfig();
		$this->item   = $item;
		$this->form   = $form;

		parent::display();
	}
}
