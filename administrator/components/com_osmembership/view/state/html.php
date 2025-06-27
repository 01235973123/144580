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

class OSMembershipViewStateHtml extends MPFViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$db = $this->model->getDbo();
		$db->setQuery(
			$db->getQuery(true)
				->select('id, name')
				->from('#__osmembership_countries')
				->where('published = 1')
				->order('name')
		);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, ' - ' . Text::_('OSM_SELECT_COUNTRY') . ' - ', 'id', 'name');
		$options   = array_merge($options, $db->loadObjectList());

		$this->lists['country_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'country_id',
			'class="form-select"',
			'id',
			'name',
			$this->item->country_id
		);
		$this->lists['country_id'] = OSMembershipHelperHtml::getChoicesJsSelect($this->lists['country_id']);
	}
}
