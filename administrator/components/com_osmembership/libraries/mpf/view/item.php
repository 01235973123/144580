<?php

/**
 * @package     MPF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Joomla CMS Item View Class. This class is used to display details information of an item
 * or display form allow add/editing items
 *
 * @package     MPF
 * @subpackage  View
 * @since       2.0
 *
 * @property MPFModelAdmin $model
 */
class MPFViewItem extends MPFViewHtml
{
	use MPFViewForm;

	/**
	 * The languages use on site exclude default language
	 *
	 * @var array
	 */
	protected $languages;

	/**
	 * The model state.
	 *
	 * @var MPFModelState
	 */
	protected $state;

	/**
	 * The record which is being added/edited
	 *
	 * @var Object
	 */
	protected $item;

	/**
	 * The array which keeps list of "list" options which will be displayed on the form
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * Method to prepare all the data for the view before it is displayed
	 */
	protected function prepareView()
	{
		$this->state = $this->model->getState();
		$this->item  = $this->model->getData();

		if (property_exists($this->item, 'published'))
		{
			$this->lists['published'] = OSMembershipHelperHtml::getBooleanInput('published', $this->item->published);
		}

		if (property_exists($this->item, 'access'))
		{
			$this->lists['access'] = HTMLHelper::_('access.level', 'access', $this->item->access, 'class="form-select"', false);
		}

		if (property_exists($this->item, 'language'))
		{
			$this->lists['language'] = HTMLHelper::_(
				'select.genericlist',
				HTMLHelper::_('contentlanguage.existing', true, true),
				'language',
				'class="form-select"',
				'value',
				'text',
				$this->item->language
			);
		}

		if ($this->isAdminView)
		{
			$this->addToolbar();
		}

		$this->languages = OSMembershipHelper::getLanguages();
	}

	/**
	 * Add toolbar buttons for add/edit item form
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
			$canDo = call_user_func(['MPFHelper', 'getActions'], $this->viewConfig['option'], $this->name, $this->state);
		}

		if ($this->isAdminView)
		{
			if ($this->item->id)
			{
				$toolbarTitle = $this->viewConfig['language_prefix'] . '_' . $this->name . '_EDIT';
			}
			else
			{
				$toolbarTitle = $this->viewConfig['language_prefix'] . '_' . $this->name . '_NEW';
			}

			ToolbarHelper::title(Text::_(strtoupper($toolbarTitle)));
		}

		if (($canDo->get('core.edit') || ($canDo->get('core.create'))) && !in_array('save', $this->hideButtons))
		{
			ToolbarHelper::apply('apply');
			ToolbarHelper::save('save');
		}

		if ($canDo->get('core.create') && !in_array('save2new', $this->hideButtons))
		{
			ToolbarHelper::custom('save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if ($this->item->id && $canDo->get('core.create') && !in_array('save2copy', $this->hideButtons))
		{
			ToolbarHelper::save2copy('save2copy');
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

	/**
	 * Get form action
	 *
	 * @return string
	 */
	protected function getFormAction(): string
	{
		$url = 'index.php?option=' . $this->option . '&view=' . $this->name;

		if ($this->item->id > 0)
		{
			$url .= '&id=' . $this->item->id;
		}

		return Route::_($url, false);
	}
}
