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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

class JFormFieldMpfields extends JFormFieldList
{
	protected $type = 'Mpfields';

	protected function getOptions()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('name, title')
			->from('#__osmembership_fields')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('Select Field'));

		foreach ($fields as $field)
		{
			$options[] = HTMLHelper::_('select.option', $field->name, $field->title);
		}

		// Some basic fields
		$options[] = HTMLHelper::_('select.option', 'id', Text::_('Subscription ID'));
		$options[] = HTMLHelper::_('select.option', 'username', Text::_('Username'));
		$options[] = HTMLHelper::_('select.option', 'email', Text::_('Email'));
		$options[] = HTMLHelper::_('select.option', 'user_id', Text::_('User ID'));
		$options[] = HTMLHelper::_('select.option', 'username', Text::_('User Name'));
		$options[] = HTMLHelper::_('select.option', 'created_date', Text::_('Created Date'));
		$options[] = HTMLHelper::_('select.option', 'from_date', Text::_('Subscription Start Date'));
		$options[] = HTMLHelper::_('select.option', 'to_date', Text::_('Subscription End Date'));

		return $options;
	}
}
