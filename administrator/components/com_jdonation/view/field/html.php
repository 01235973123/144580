<?php

/**
 * @version        5.4
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class DonationViewFieldHtml extends OSFViewItem
{
	protected function prepareView()
	{
		parent::prepareView();
		$db         = Factory::getContainer()->get('db');
		$query      = $db->getQuery(true);
		$config     = DonationHelper::getConfig();
		$fieldTypes = array('Text', 'Textarea', 'List', 'Checkboxes', 'Radio', 'Date', 'Heading', 'File', 'Message', 'Countries', 'State', 'SQL','PHP');
		$options    = [];
		$options[]  = HTMLHelper::_('select.option', -1, Text::_('JD_SELECT_FIELD_TYPE'));
		$options    = [];
		foreach ($fieldTypes as $fieldType)
		{
			$options[] = HTMLHelper::_('select.option', $fieldType, $fieldType);
		}
		if ($this->item->is_core)
		{
			$readOnly = ' readonly="true" ';
		}
		else
		{
			$readOnly = '';
		}
		$this->lists['fieldtype'] = HTMLHelper::_('select.genericlist', $options, 'fieldtype', 'class="input-medium form-select ilarge" ' . $readOnly, 'value', 'text',
			$this->item->fieldtype);

		//Build campaign selection
		$query->select('id, title')
			->from('#__jd_campaigns')
			->order('title');
		$db->setQuery($query);
		if($this->item->id > 0)
		{
			$options                     = [];
			$options[]                   = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CAMPAIGN'), 'id', 'title');
			$options                     = array_merge($options, $db->loadObjectList());
			$this->lists ['campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'campaign_id', 'class="input-large form-select ilarge"', 'id', 'title', $this->item->campaign_id);
		}
		else
		{
			$options                     = [];
			$options                     = $db->loadObjectList();
			$this->lists ['campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'campaign_ids[]', 'class="input-large form-control ilarge" multiple', 'id', 'title');
		}

		$integration = $config->cb_integration;
		if ($integration)
		{
			$options   = [];
			$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_FIELD'));
			if ($integration == 1 || $integration == 2)
			{
				$query->clear();
				if ($integration == 1)
				{
					$query->select('name AS `value`, name AS `text`')
						->from('#__comprofiler_fields')
						->where('`table`="#__comprofiler"');
				}
				else
				{
					$query->select('fieldcode AS `value`, fieldcode AS `text`')
						->from('#__community_fields')
						->where('published=1 AND fieldcode != ""');
				}
				$db->setQuery($query);
				$options = array_merge($options, $db->loadObjectList());
			}
			elseif ($integration == 3)
			{
				$fields = array(
					'address1',
					'address2',
					'city',
					'region',
					'country',
					'postal_code',
					'phone',
					'website',
					'favoritebook',
					'aboutme',
					'dob');
				foreach ($fields as $field)
				{
					$options[] = HTMLHelper::_('select.option', $field, $field);
				}
			}
            elseif ($integration == 4)
            {
                $lang = Factory::getApplication()->getLanguage();
                $extension = 'com_easysocial';
                $base_dir = JPATH_ADMINISTRATOR;
                $language_tag = 'en-GB';
                $reload = true;
                $lang->load($extension, $base_dir, $language_tag, $reload);
                $sql = 'SELECT id AS `value`, title AS `text` FROM #__social_fields WHERE state=1 AND title != "" AND `unique_key` NOT LIKE "HEADER%"';
                $db->setQuery($sql);
                $fields = $db->loadObjectList();
                foreach($fields as $field){
                    $options[] = HTMLHelper::_('select.option', $field->value, Text::_($field->text));
                }
            }
            elseif ($integration == 5)
            {
                $lang = Factory::getApplication()->getLanguage();
                $extension = 'com_jsn';
                $base_dir = JPATH_ADMINISTRATOR;
                $language_tag = 'en-GB';
                $reload = true;
                $lang->load($extension, $base_dir, $language_tag, $reload);
                $fields = array_keys($db->getTableColumns('#__jsn_users'));
                $fields = array_diff($fields, array('id', 'params'));
                foreach ($fields as $field)
                {
                    $options[] = HTMLHelper::_('select.option', $field, $field);
                }
            }
			$this->lists['field_mapping'] = HTMLHelper::_('select.genericlist', $options, 'field_mapping', 'class="input-medium form-select ilarge"', 'value', 'text',
				$this->item->field_mapping);
		}

		$options                            = [];
		$options[]                          = HTMLHelper::_('select.option', 0, Text::_('None'));
		$options[]                          = HTMLHelper::_('select.option', 1, Text::_('Integer Number'));
		$options[]                          = HTMLHelper::_('select.option', 2, Text::_('Number'));
		$options[]                          = HTMLHelper::_('select.option', 3, Text::_('Email'));
		$options[]                          = HTMLHelper::_('select.option', 4, Text::_('Url'));
		$options[]                          = HTMLHelper::_('select.option', 5, Text::_('Phone'));
		$options[]                          = HTMLHelper::_('select.option', 6, Text::_('Past Date'));
		$options[]                          = HTMLHelper::_('select.option', 7, Text::_('Ip'));
		$options[]                          = HTMLHelper::_('select.option', 8, Text::_('Min size'));
		$options[]                          = HTMLHelper::_('select.option', 9, Text::_('Max size'));
		$options[]                          = HTMLHelper::_('select.option', 10, Text::_('Min integer'));
		$options[]                          = HTMLHelper::_('select.option', 11, Text::_('Max integer'));
		$this->lists['datatype_validation'] = HTMLHelper::_('select.genericlist', $options, 'datatype_validation', 'class="ilarge input-medium form-select"', 'value', 'text',
			$this->item->datatype_validation);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('JD_FULL_WIDTH'));
		$options[] = HTMLHelper::_('select.option', 'jd-one-half', Text::_('1/2'));
		$options[] = HTMLHelper::_('select.option', 'jd-one-third', Text::_('1/3'));
		$options[] = HTMLHelper::_('select.option', 'jd-two-thirds', Text::_('2/3'));
		$options[] = HTMLHelper::_('select.option', 'jd-one-quarter', Text::_('1/4'));
		$options[] = HTMLHelper::_('select.option', 'jd-two-quarters', Text::_('2/4'));
		$options[] = HTMLHelper::_('select.option', 'jd-three-quarters', Text::_('3/4'));

		$this->lists['container_size']		= HTMLHelper::_(
			'select.genericlist',
			$options,
			'container_size',
			'class="form-select ilarge input-large"',
			'value',
			'text',
			$this->item->container_size
		);

		$this->lists['multiple']			= HTMLHelper::_('select.booleanlist', 'multiple', ' class="input-medium form-select" ', $this->item->multiple);
		$this->lists['required']			= HTMLHelper::_('select.booleanlist', 'required', '', $this->item->required);
		$this->fieldCampaign				= (int) DonationHelper::getConfigValue('field_campaign');
		$config								= DonationHelper::getConfig();
		$this->bootstrapHelper				= new DonationHelperBootstrap($config->twitter_bootstrap_version);
	}
}
