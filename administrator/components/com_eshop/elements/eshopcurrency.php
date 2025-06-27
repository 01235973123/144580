<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldEshopCurrency extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshopcurrency';

	public function getInput()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('currency_code, currency_name')
			->from('#__eshop_currencies')
			->order('currency_name');
		$db->setQuery($query);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('Select Currency'), 'currency_code', 'currency_name');
		$options   = array_merge($options, $db->loadObjectList());

		return HTMLHelper::_(
			'select.genericlist',
			$options,
			$this->name,
			'class="input-xlarge form-select"',
			'currency_code',
			'currency_name',
			$this->value
		);
	}
}