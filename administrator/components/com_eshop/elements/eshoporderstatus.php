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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldEshoporderstatus extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshoporderstatus';

	public function getInput()
	{
		$orderStatus    = EShopHelper::getConfigValue('order_status_id');
		$completeStatus = EShopHelper::getConfigValue('complete_status_id');

		$options   = [];
		$options[] = HTMLHelper::_(
			'select.option',
			$orderStatus,
			EShopHelper::getOrderStatusName($orderStatus, ComponentHelper::getParams('com_languages')->get('site', 'en-GB'))
		);
		$options[] = HTMLHelper::_(
			'select.option',
			$completeStatus,
			EShopHelper::getOrderStatusName($completeStatus, ComponentHelper::getParams('com_languages')->get('site', 'en-GB'))
		);

		$orderStatus = HTMLHelper::_(
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

		return $orderStatus;
	}
}