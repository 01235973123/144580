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
use Joomla\CMS\Language\Text;

class JFormFieldEshopgeozone extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshopgeozone';

	public function getInput()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS value, geozone_name AS text')
			->from('#__eshop_geozones')
			->where('published = 1')
			->order('geozone_name');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ALL_ZONES'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$geoZone = HTMLHelper::_(
			'select.genericlist',
			$options,
			$this->name,
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => 'class="input-xlarge form-select"',
				'list.select'        => $this->value,
			]
		);

		return $geoZone;
	}
}