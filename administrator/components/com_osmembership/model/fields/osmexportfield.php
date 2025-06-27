<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

if (version_compare(JVERSION, '4.4.99', '>'))
{
	JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField');
}
else
{
	FormHelper::loadFieldClass('list');
}

class JFormFieldOSMExportField extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'osmexportfield';

	/**
	 * @var string
	 */
	protected $layout = 'joomla.form.field.list-fancy-select';

	/**
	 * Get list of options for this field
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('name, title')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('hide_on_export = 0')
			->whereNotIn('fieldtype', ['Heading', 'Message'], ParameterType::STRING)
			->order('title');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_FIELD'));

		if ($config->get('export_id', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'id', Text::_('OSM_ID'));
		}

		if ($config->get('export_category', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'category', Text::_('OSM_CATEGORY'));
		}

		if ($config->get('export_plan', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'plan', Text::_('OSM_PLAN'));
		}

		if ($config->get('export_user_id', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'user_id', Text::_('OSM_USER_ID'));
		}

		if ($config->get('export_username', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'username', Text::_('OSM_USERNAME'));
		}

		foreach ($rowFields as $rowField)
		{
			$options[] = HTMLHelper::_('select.option', $rowField->name, $rowField->title);
		}

		$exportFields = [
			'created_date'      => 'OSM_CREATED_DATE',
			'payment_date'      => 'OSM_PAYMENT_DATE',
			'from_date'         => 'OSM_SUBSCRIPTION_START_DATE',
			'to_date'           => 'OSM_SUBSCRIPTION_END_DATE',
			'published'         => 'OSM_PUBLISHED',
			'amount'            => 'OSM_NET_AMOUNT',
			'tax_amount'        => 'OSM_TAX_AMOUNT',
			'discount_amount'   => 'OSM_DISCOUNT_AMOUNT',
			'gross_amount'      => 'OSM_GROSS_AMOUNT',
			'payment_method'    => 'OSM_PAYMENT_METHOD',
			'transaction_id'    => 'OSM_TRANSACTION_ID',
			'membership_id'     => 'OSM_MEMBERSHIP_ID',
			'subscription_type' => 'OSM_SUBSCRIPTION_TYPE',
		];

		foreach ($exportFields as $name => $title)
		{
			if ($config->get('export_' . $name, 1))
			{
				$options[] = HTMLHelper::_('select.option', $name, Text::_($title));
			}
		}

		if ($config->activate_invoice_feature && $config->get('export_invoice_number', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'invoice_number', Text::_('OSM_INVOICE_NUMBER'));
		}

		if ($config->enable_coupon && $config->get('export_coupon', 1))
		{
			$options[] = HTMLHelper::_('select.option', 'coupon_code', Text::_('OSM_COUPON'));
		}

		return $options;
	}
}
