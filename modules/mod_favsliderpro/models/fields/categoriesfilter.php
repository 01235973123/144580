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

require_once(JPATH_ROOT.'/modules/mod_favsliderpro/helpers/hikashophelper.php');

class JFormFieldCategoriesFilter extends JFormFieldList
{

	protected $type = 'CategoriesFilter';

	protected function getOptions()
	{

		$db = JFactory::getDBO();

        $hikashophelper = new HikaShopHelper();

        $options = array();
		$options[] = JHtml::_('select.option', 0, JText::_("No Category Filtering"));

        $tables_list = $db->getTableList();
        $tables_prefix = $db->getPrefix();

        if (!in_array($tables_prefix.'hikashop_category',$tables_list)) { return $options; }

		$query = "SELECT category_id, category_name FROM #__hikashop_category WHERE category_published = 1 AND category_type = 'product' ORDER BY category_name ASC";
        $db->setQuery($query);
        $results = $db->LoadObjectList();

		foreach($results as $result)
		{

            $cnt = $hikashophelper->get_cat_products_cnt($result->category_id);

            if ($cnt > 0) {

			    $options[] = JHtml::_('select.option', $result->category_id, $result->category_name);

            }

		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;

	}
}
