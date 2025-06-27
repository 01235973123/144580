<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

if (!empty($this->editor))
{
    $fields = '';

	if (file_exists(JPATH_ROOT . '/components/com_eshop/fields.xml'))
	{
		$fields = file_get_contents(JPATH_ROOT . '/components/com_eshop/fields.xml');
	}

	echo $this->editor->display('product_fields', $fields, '100%', '550', '75', '8', false, null, null, null, array('syntax' => 'xml'));
}