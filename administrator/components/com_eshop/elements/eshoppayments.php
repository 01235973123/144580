<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldEshoppayments extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshoppayments';

	public function getInput()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS value, title AS text')
			->from('#__eshop_payments')
			->where('published = 1')
			->order('title');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$payments = HTMLHelper::_(
			'select.genericlist',
			$rows,
			$this->name . '[]',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => 'class="input-xlarge form-select" multiple="multiple"',
				'list.select'        => $this->value,
			]
		);

		return $payments;
	}
}