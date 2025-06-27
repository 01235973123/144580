<?php



/**
 * @package     OSF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2009 - 2023 Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
/**
 * Joomla CMS Item View Class. This class is used to display details information of an item
 * or display form allow add/editing items
 *
 * @package     OSF
 * @subpackage  View
 * @since       1.0
 */
class OSFViewItem extends OSFViewHtml
{

	/**
	 * The model state.
	 *
	 * @var OSFModelState
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
	 * @var Array
	 */
	protected $lists;

	/**
	 * Method to display the view
	 * 
	 * @see OSFViewHtml::display()
	 */
	public function display()
	{
		$this->prepareView();
		parent::display();
	}

	/**
	 * Method to prepare all the data for the view before it is displayed
	 */
	protected function prepareView()
	{
		$this->state = $this->model->getState();
		$this->item = $this->model->getData();		
		if (property_exists($this->item, 'published'))
		{
			$this->lists['published'] = HTMLHelper::_('select.booleanlist', 'published', ' ', $this->item->published);
		}
		if (property_exists($this->item, 'access'))
		{
			$this->lists['access'] = HTMLHelper::_('access.level', 'access', $this->item->access, ' class="form-select" ', false);
		}
		
		if (property_exists($this->item, 'language'))
		{
			$this->lists['language'] = HTMLHelper::_('select.genericlist', HTMLHelper::_('contentlanguage.existing', true, true), 'language', ' ', 'value', 'text', $this->item->language);
		}
		
		if ($this->isAdminView)
		{
			$this->addToolbar();
		}

		$this->languages = DonationHelper::getLanguages();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$helperClass = $this->classPrefix . 'Helper';
		if (is_callable($helperClass . '::getActions'))
		{
			$canDo = call_user_func(array($helperClass, 'getActions'), $this->name, $this->state);
		}
		else
		{
			$canDo = call_user_func(array('OSFHelper', 'getActions'), $this->option, $this->name, $this->state);
		}
		if ($this->item->id)
		{
			$toolbarTitle = $this->languagePrefix . '_' . $this->name . '_EDIT';
		}
		else
		{
			$toolbarTitle = $this->languagePrefix . '_' . $this->name . '_NEW';
		}
		ToolBarHelper::title(Text::_(strtoupper($toolbarTitle)));
		if ($canDo->get('core.edit') || ($canDo->get('core.create')))
		{
			ToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
			ToolBarHelper::save('save', 'JTOOLBAR_SAVE');
		}
		
		if ($canDo->get('core.create'))
		{
			ToolBarHelper::custom('save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		
		if ($this->item->id && $canDo->get('core.create'))
		{
			ToolbarHelper::save2copy('save2copy');
		}
		
		if ($this->item->id)
		{
			ToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			ToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
		}
	}
}
