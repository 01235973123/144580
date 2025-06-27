<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class OSMembershipViewLanguageHtml extends MPFViewHtml
{
	/**
	 * List of select lists
	 * @var array
	 */

	protected $lists;
	/**
	 * All language items
	 *
	 * @var array
	 */

	protected $items;

	/**
	 * Model state
	 *
	 * @var MPFModelState
	 */
	protected $state;

	/**
	 * Set data and render the view
	 *
	 * @return void
	 */
	public function display()
	{
		$this->state = $this->model->getState();
		$languages   = $this->model->getSiteLanguages();

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('Select Language'));

		foreach ($languages as $language)
		{
			$options[] = HTMLHelper::_('select.option', $language, $language);
		}

		$lists['filter_language'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_language',
			'class="form-select"',
			'value',
			'text',
			$this->state->filter_language
		);

		$options              = [];
		$options[]            = HTMLHelper::_('select.option', '', Text::_('--Select Item--'));
		$options[]            = HTMLHelper::_(
			'select.option',
			'com_osmembership',
			Text::_('Membership Pro - Frontend')
		);
		$options[]            = HTMLHelper::_(
			'select.option',
			'admin.com_osmembership',
			Text::_('Membership Pro - Backend')
		);
		$options[]            = HTMLHelper::_(
			'select.option',
			'admin.com_osmembershipcommon',
			Text::_('Membership Pro - Common')
		);
		$options[]            = HTMLHelper::_(
			'select.option',
			'admin.com_osmembership.sys',
			Text::_('Membership Pro - System')
		);
		$lists['filter_item'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_item',
			'class="form-select"',
			'value',
			'text',
			$this->state->filter_item
		);

		$this->items = $this->model->getData();
		$this->lists = $lists;

		$this->addToolbar();

		parent::display();
	}

	/**
	 * Add view's toolbar buttons
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('Translation Management'), 'generic.png');
		ToolbarHelper::addNew('new_item', 'New Item');
		ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('save');
		ToolbarHelper::cancel('cancel');
	}
}
