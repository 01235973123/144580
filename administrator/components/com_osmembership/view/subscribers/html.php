<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class OSMembershipViewSubscribersHtml extends MPFViewList
{
	/**
	 * Core fields
	 *
	 * @var array
	 */
	protected $coreFields;

	/**
	 * Component config data
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('name')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('is_core = 1');
		$db->setQuery($query);
		$this->coreFields = $db->loadColumn();
		$this->config     = OSMembershipHelper::getConfig();
	}

	/**
	 * Method to add toolbar buttons
	 */
	protected function addToolbar()
	{
		$helperClass = $this->viewConfig['class_prefix'] . 'Helper';

		if (is_callable($helperClass . '::getActions'))
		{
			$canDo = call_user_func([$helperClass, 'getActions'], $this->name, $this->state);
		}
		else
		{
			$canDo = call_user_func(['MPFHelper', 'getActions'],
				$this->viewConfig['option'],
				$this->name,
				$this->state);
		}

		$languagePrefix = $this->viewConfig['language_prefix'];

		ToolbarHelper::title(
			Text::_(strtoupper($languagePrefix . '_' . $this->name . '_MANAGEMENT')),
			'link ' . $this->name
		);

		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			ToolbarHelper::editList('edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences($this->viewConfig['option']);
		}
	}
}
