<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField');
}
else
{
	FormHelper::loadFieldClass('list');
}

class JFormFieldOSMDiscountType extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'osmdiscounttype';

	protected function getOptions()
	{
		$config    = OSMembershipHelper::getConfig();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, '%');
		$options[] = HTMLHelper::_('select.option', 1, $config->currency_symbol);

		return $options;
	}
}
