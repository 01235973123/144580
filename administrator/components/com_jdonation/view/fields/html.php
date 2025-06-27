<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ();
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class DonationViewFieldsHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id, title')
			->from('#__jd_campaigns')
			->order('title');
		$db->setQuery($query);

		
		$optionArr					= array();
		$optionArr[]				= HTMLHelper::_('select.option','-1',Text::_('JS_REQUIRE_STATUS'));
		$optionArr[]				= HTMLHelper::_('select.option','1',Text::_('JNO'));
		$optionArr[]				= HTMLHelper::_('select.option','2',Text::_('JYES'));
		$this->lists['require']	= HTMLHelper::_('select.genericlist',$optionArr,'require_status','class="input-medium form-select ilarge"  onchange="submit();" ','value','text',$this->state->require_status);

		$optionArr					= array();
		$optionArr[]				= HTMLHelper::_('select.option','-1',Text::_('JD_CORE_FIELD'));
		$optionArr[]				= HTMLHelper::_('select.option','1',Text::_('JNO'));
		$optionArr[]				= HTMLHelper::_('select.option','2',Text::_('JYES'));
		$this->lists['core_field']	= HTMLHelper::_('select.genericlist',$optionArr,'is_core_field','class="input-medium form-select ilarge"  onchange="submit();" ','value','text',$this->state->is_core_field);

		$optionArr					= array();
		$optionArr[]				= HTMLHelper::_('select.option','-1',Text::_('JD_PUBLISH_STATUS'));
		$optionArr[]				= HTMLHelper::_('select.option','1',Text::_('JNO'));
		$optionArr[]				= HTMLHelper::_('select.option','2',Text::_('JYES'));
		$this->lists['published']	= HTMLHelper::_('select.genericlist',$optionArr,'published','class="input-medium form-select ilarge"  onchange="submit();" ','value','text',$this->state->published);

		$options                    = array();
		$fieldTypes					= array('Text', 'Textarea', 'List', 'Checkboxes', 'Radio', 'Date', 'Heading', 'File', 'Message', 'Countries', 'State', 'SQL','PHP');
		$options[]					= HTMLHelper::_('select.option', '', Text::_('JD_SELECT_FIELD_TYPE'));
		foreach ($fieldTypes as $fieldType)
		{
			$options[]				= HTMLHelper::_('select.option', $fieldType, $fieldType);
		}
		$this->lists['field_type']			= HTMLHelper::_('select.genericlist', $options, 'field_type', 'onchange="submit();" class="ilarge input-medium form-select"', 'value', 'text', $this->state->field_type);

		$options                            = array();
		$options []                         = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CAMPAIGN'), 'id', 'title');
		$options                            = array_merge($options, $db->loadObjectList());
		$this->lists ['filter_campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_campaign_id', ' onchange="submit();" ', 'id', 'title', $this->state->filter_campaign_id);

		if($this->state->field_type != '' || (int)$this->state->published > 0 || (int)$this->state->is_core_field > 0 || (int)$this->state->require_status > 0)
		{
			$this->showFilterForm = "js-stools-container-filters-visible";
		}
		else
		{
			$this->showFilterForm = "";
		}


		$this->bootstrapHelper				= new DonationHelperBootstrap($config->twitter_bootstrap_version);
		$this->fieldCampaign                = (int) DonationHelper::getConfigValue('field_campaign');
	}
}
