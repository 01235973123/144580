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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldEshopweight extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshopweight';

	public function getInput()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.weight_name AS text')
			->from('#__eshop_weights AS a')
			->innerJoin('#__eshop_weightdetails AS b ON (a.id = b.weight_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.weight_name');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$taxClass = HTMLHelper::_(
			'select.genericlist',
			$rows,
			$this->name,
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => 'class="input-xlarge form-select"',
				'list.select'        => $this->value,
			]
		);

		return $taxClass;
	}
}