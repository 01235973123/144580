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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldEshopProducts extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshopproducts';

	public function getInput()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, b.product_name AS title')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');
		$db->setQuery($query);

		return HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			$this->name . '[]',
			'class="input-xlarge form-select" multiple="multiple"',
			'id',
			'title',
			$this->value
		);
	}
}
