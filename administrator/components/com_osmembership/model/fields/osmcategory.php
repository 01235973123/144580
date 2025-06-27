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
use Joomla\Database\DatabaseDriver;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField');
}
else
{
	FormHelper::loadFieldClass('list');
}

class JFormFieldOSMCategory extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'osmcategory';

	protected $layout = 'joomla.form.field.list-fancy-select';

	protected function getOptions()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select($db->quoteName('id', 'value'))
			->select($db->quoteName('title', 'text'))
			->from('#__osmembership_categories')
			->where('published = 1')
			->order('title');
		$db->setQuery($query);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('Select Category'));

		return array_merge($options, $db->loadObjectList());
	}
}
