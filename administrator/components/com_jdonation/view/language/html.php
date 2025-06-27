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
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;

class DonationViewLanguageHtml extends OSFViewHtml
{
	public function display()
	{
        //JFactory::getApplication()->getDocument()->addStyleSheet(JUri::base(true) . '/components/com_jdonation/assets/css/style.css');
		$model      = $this->getModel();
		$state      = $model->getState();
        $search     = Factory::getApplication()->input->getString('search','');
        $limitstart = Factory::getApplication()->input->getInt('limitstart',0);
        $limit      = Factory::getApplication()->input->getInt('limit',100);
        $site       = Factory::getApplication()->input->getInt('site');
		$trans      = $model->getTrans($state->filter_language, $state->filter_item,$search,$limitstart,$limit,$site);
        $pagNav     = $model->getPagination($state->filter_item, $search, $limitstart, $limit, 0);
        $languages  = $model->getSiteLanguages();
		DonationHelperHtml::renderSubmenu('language');
		$options    = array();
		$options [] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_LANGUAGE'));
		foreach ($languages as $language)
		{
			$options [] = HTMLHelper::_('select.option', $language, $language);
		}
		$lists ['filter_language'] = HTMLHelper::_('select.genericlist', $options, 'filter_language','class="input-medium form-select" onchange="submit();" ', 'value', 'text', $state->filter_language);
		$options                   = array();
		$options []                = HTMLHelper::_('select.option', '', Text::_('--Select Item--'));
		$options []                = HTMLHelper::_('select.option', 'com_jdonation', Text::_('Joom Donation Component'));
		$langPath                  = JPATH_ROOT . '/language/en-GB/';
		if (is_file(Path::clean($langPath . 'mod_jdonation.ini')))
		{
			$options [] = HTMLHelper::_('select.option', 'mod_jdonation', Text::_('Donation module'));
		}
		if (is_file(Path::clean($langPath . 'en-GB.mod_jd_campaigns.ini')))
		{
			$options [] = HTMLHelper::_('select.option', 'mod_jd_campaigns', Text::_('Campaigns module'));
		}
		if (is_file(Path::clean($langPath . 'en-GB.mod_jd_donors.ini')))
		{
			$options [] = HTMLHelper::_('select.option', 'mod_jd_donors', Text::_('Donors module'));
		}
		if (is_file(Path::clean($langPath . 'en-GB.mod_jd_thermometer.ini')))
		{
			$options [] = HTMLHelper::_('select.option', 'mod_jd_thermometer', Text::_('Thermometer module'));
		}
		$lists ['filter_item'] = HTMLHelper::_('select.genericlist', $options, 'filter_item', ' onchange="this.form.submit();" style="width:250px;" class=" form-select"', 'value', 'text', $state->filter_item);
        $options = array() ;
        $options[] = HTMLHelper::_('select.option', 0, Text::_('Front-End Side')) ;
        $options[] = HTMLHelper::_('select.option', 1, Text::_('Back-End Side')) ;
        $lists['site'] = HTMLHelper::_('select.genericlist', $options, 'site', ' class="input-large form-select"  onchange="submit();" ', 'value', 'text', $site) ;
		$this->trans           = $trans;
		$this->lists           = $lists;
		$this->lang            = $state->filter_language;
		$this->item            = $state->filter_item;
        $this->pagNa           = $pagNav;
		$this->site			   = $site;
        $this->search          = $search;
		parent::display();
	}
}
