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
use Joomla\CMS\Language\Text;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewField extends EShopViewForm
{

	public function _buildListArray(&$lists, $item)
	{
		$fieldTypes = ['Text', 'Textarea', 'List', 'Checkboxes', 'Radio', 'Countries', 'Zone'];
		$options    = [];
		$options[]  = HTMLHelper::_('select.option', -1, Text::_('ESHOP_FIELD_TYPE'));
		$options    = [];
		foreach ($fieldTypes as $fieldType)
		{
			$options[] = HTMLHelper::_('select.option', $fieldType, $fieldType);
		}
		if ($item->is_core)
		{
			$disabled = " disabled ";
		}
		else
		{
			$disabled = '';
		}
		$lists['fieldtype'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'fieldtype',
			'class="input-large form-control" onchange="changeField(this.value)" ' . $disabled,
			'value',
			'text',
			$item->fieldtype
		);

		$validateRules = [
			'numeric'         => Text::_('ESHOP_NUMERIC'),
			'integer'         => Text::_('ESHOP_INTEGER'),
			'float'           => Text::_('ESHOP_FLOAT'),
			'max_len,32'      => Text::_('ESHOP_MAX_LENGTH'),
			'min_len,1'       => Text::_('ESHOP_MIN_LENGTH'),
			'exact_len,10'    => Text::_('ESHOP_EXACT_LENGTH'),
			'max_numeric,100' => Text::_('ESHOP_MAX_NUMERIC'),
			'min_numeric,1'   => Text::_('ESHOP_MIN_NUMERIC'),
			'valid_email'     => Text::_('ESHOP_VALID_EMAIL'),
			'valid_url'       => Text::_('ESHOP_VALID_URL'),
		];
		$options       = [];
		$options[]     = HTMLHelper::_('select.option', '', Text::_('ESHOP_NONE'));
		foreach ($validateRules as $rule => $title)
		{
			$options[] = HTMLHelper::_('select.option', $rule, $title);
		}
		$lists['validation_rule'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'validation_rule[]',
			' class="input-large form-control" multiple size="10" onclick="buildValidationString();"',
			'value',
			'text',
			$item->validation_rule ? explode('|', $item->validation_rule) : ''
		);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 'A', Text::_('ESHOP_ALL'));
		$options[]                = HTMLHelper::_('select.option', 'B', Text::_('ESHOP_BILLING_ADDRESS'));
		$options[]                = HTMLHelper::_('select.option', 'S', Text::_('ESHOP_SHIPPING_ADDRESS'));
		$lists['address_type']    = HTMLHelper::_(
			'select.genericlist',
			$options,
			'address_type',
			'class="input-large form-control" ' . $disabled,
			'value',
			'text',
			$item->address_type
		);
		if (in_array($item->name, EShopModelField::$protectedFields))
		{
			$disabled = " disabled ";
		}
		else
		{
			$disabled = "";
		}
		$lists['required'] = HTMLHelper::_(
			'select.booleanlist',
			'required',
			' class="input-xlarge form-select" onclick="buildValidationString();" ' . $disabled,
			$item->required
		);
		$lists['multiple'] = HTMLHelper::_('select.booleanlist', 'multiple', 'class="input-xlarge form-select"' . $disabled, $item->multiple);
	}
}