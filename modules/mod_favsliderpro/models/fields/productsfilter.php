<?php

/**
*   FavSlider Pro
*
*   Responsive and customizable Joomla!3 module
*
*   @version        1.1
*   @link           http://extensions.favthemes.com/favsliderpro
*   @author         FavThemes - http://www.favthemes.com
*   @copyright      Copyright (C) 2012-2017 FavThemes.com. All Rights Reserved.
*   @license        Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldProductsFilter extends JFormFieldList
{

	protected $type = 'ProductsFilter';

	protected function getOptions()
	{

		$db = JFactory::getDBO();

        $options = array();
		$options[] = JHtml::_('select.option', 0, JText::_("No Product Filtering"));

        $tables_list = $db->getTableList();
        $tables_prefix = $db->getPrefix();

        if (!in_array($tables_prefix.'hikashop_product',$tables_list)) { return $options; }

		$query = "SELECT product_id,product_name,product_code FROM #__hikashop_product WHERE product_published = 1 AND product_type = 'main' ORDER BY product_name ASC";
        $db->setQuery($query);
        $results = $db->LoadObjectList();

		foreach($results as $result)
		{
			$options[] = JHtml::_('select.option', $result->product_id, $result->product_name.' ('.$result->product_code.')');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;

	}
}
