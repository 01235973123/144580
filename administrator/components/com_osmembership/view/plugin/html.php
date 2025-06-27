<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

class OSMembershipViewPluginHtml extends MPFViewItem
{
	/**
	 * The plugin form
	 *
	 * @var Form
	 */
	protected $form;

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$registry = new Registry();
		$registry->loadString($this->item->params ?? '{}');
		$data = new stdClass();
		$data->params = $registry->toArray();

		$form = Form::getInstance(
			'osmembership',
			JPATH_ROOT . '/components/com_osmembership/plugins/' . $this->item->name . '.xml',
			[],
			false,
			'//config'
		);

		$form->bind($data);

		$this->form = $form;
	}

	/**
	 * Add toolbar button
	 *
	 * @return void
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

		if ($this->item->id)
		{
			$toolbarTitle = $languagePrefix . '_' . $this->name . '_EDIT';
		}
		else
		{
			$toolbarTitle = $languagePrefix . '_' . $this->name . '_NEW';
		}

		ToolbarHelper::title(Text::_(strtoupper($toolbarTitle)));

		if ($canDo->get('core.edit') || ($canDo->get('core.create')))
		{
			ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('save', 'JTOOLBAR_SAVE');
		}

		if ($this->item->id)
		{
			ToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			ToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
		}
	}
}
