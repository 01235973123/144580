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

class JFormFieldOSMFilterFields extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'osmfilterfields';

	/**
	 * Get options for the field
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('Select Fields'));

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_fields')
			->where('filterable = 1')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $field)
		{
			$options[] = HTMLHelper::_('select.option', $field->id, sprintf('[%d] - %s', $field->id, $field->title));
		}

		return $options;
	}
}
