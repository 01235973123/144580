<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldGroupedList', '\\Joomla\\CMS\\Form\\Field\\GroupedlistField');
}
else
{
	FormHelper::loadFieldClass('GroupedList');
}

class JFormFieldMPMenuItems extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'mpmenuitems';

	/**
	 * Return list of options for the field
	 *
	 * @return array
	 */
	public function getGroups()
	{
		$component = ComponentHelper::getComponent('com_osmembership');
		$menus     = Factory::getApplication()->getMenu('site');

		$attributes = ['component_id'];
		$values     = [$component->id];
		$items      = $menus->getItems($attributes, $values);

		$groups = [];

		foreach ($items as $item)
		{
			if ($item->language !== '*')
			{
				$lang = ' (' . $item->language . ')';
			}
			else
			{
				$lang = '';
			}

			$groups[$item->menutype][] = HTMLHelper::_('select.option', $item->id, str_repeat('- ', $item->level) . $item->title . $lang);
		}

		array_unshift($groups, [HTMLHelper::_('select.option', 0, Text::_('--Menu Item--'))]);

		return $groups;
	}
}
