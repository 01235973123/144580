<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewOption extends EShopViewForm
{

	public function _buildListArray(&$lists, $item)
	{
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', 'Select', 'Select');
		$options[]            = HTMLHelper::_('select.option', 'Radio', 'Radio');
		$options[]            = HTMLHelper::_('select.option', 'Checkbox', 'Checkbox');
		$options[]            = HTMLHelper::_('select.option', 'Text', 'Text');
		$options[]            = HTMLHelper::_('select.option', 'Textarea', 'Textarea');
		$options[]            = HTMLHelper::_('select.option', 'File', 'File');
		$options[]            = HTMLHelper::_('select.option', 'Date', 'Date');
		$options[]            = HTMLHelper::_('select.option', 'Datetime', 'Datetime');
		$lists['option_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'option_type',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select"',
				'list.select'        => $item->option_type,
			]
		);
		if ($item->id)
		{
			$lists['option_values'] = EShopHelper::getOptionValues($item->id);
		}
		else
		{
			$lists['option_values'] = [];
		}
		parent::_buildListArray($lists, $item);
	}
}