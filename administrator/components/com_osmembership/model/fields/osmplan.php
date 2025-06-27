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
use Joomla\Utilities\ArrayHelper;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField');
}
else
{
	FormHelper::loadFieldClass('list');
}

class JFormFieldOSMPlan extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'osmplan';

	protected $layout = 'joomla.form.field.list-fancy-select';

	protected function getOptions()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'title'], ['value', 'text']))
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('title');
		$db->setQuery($query);
		$options = [];

		if (!$this->multiple)
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('Select Plan'));
		}

		// Backward compatible when the field is converted from text
		if ($this->multiple && is_string($this->value))
		{
			$this->value = ArrayHelper::toInteger(explode(',', $this->value));
		}

		return array_merge($options, $db->loadObjectList());
	}
}
