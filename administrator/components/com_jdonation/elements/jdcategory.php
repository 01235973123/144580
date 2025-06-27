<?php
/**
 * @package            Joomla
 * @subpackage         Joom Donation
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2024 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField');
}
else
{
	FormHelper::loadFieldClass('list');
}

class JFormFieldJDCategory extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'category';

	protected function getInput()
	{
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$this->layout = 'joomla.form.field.list-fancy-select';
		}

		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__jd_categories')
			->where('published = 1');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JD_CATEGORY'));
		foreach ($rows as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->id, $item->title);
		}
		//return $options;

		return HTMLHelper::_('select.genericlist', $options, $this->name, array(
				'option.text.toHtml' => false,
				'list.attr'          => 'class="form-select" ',
				'option.text'        => 'text',
				'option.key'         => 'value',
				'list.select'        => $this->value
			));
	}
}
