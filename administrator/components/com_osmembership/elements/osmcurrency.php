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
use Joomla\CMS\Language\Text;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField');
}
else
{
	FormHelper::loadFieldClass('list');
}

class JFormFieldOSMCurrency extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'osmcurrency';

	protected function getOptions()
	{
		$currencies = require_once JPATH_ROOT . '/components/com_osmembership/helper/currencies.php';
		$options    = [];
		$options[]  = HTMLHelper::_('select.option', '', Text::_('Select Currency'));

		foreach ($currencies as $code => $title)
		{
			$options[] = HTMLHelper::_('select.option', $code, $title);
		}

		return $options;
	}
}
