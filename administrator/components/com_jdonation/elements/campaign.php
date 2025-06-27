<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

jimport ( 'joomla.html.html' );
jimport ( 'joomla.form.formfield' );
class JFormFieldCampaign extends FormField
{
	
	/**
	 * The form field type.
	 *
	 * @var string
	 * @since 1.6
	 */
	protected $type = 'Campaign';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return string field input markup.
	 * @since 1.6
	 */
	protected function getInput()
	{
		// Get some field values from the form.
		$campaignId = ( int ) $this->value;
		$db = Factory::getDbo ();
        $query = $db->getQuery(true);
		$options = array ();
		$options [] = HTMLHelper::_ ( 'select.option', 0, Text::_ ( 'Select Campaign' ), 'id', 'title' );
        $query->select('id, title')
            ->from('#__jd_campaigns')
            ->where('published = 1')
            ->order('title');
		$db->setQuery ($query);
		$options = array_merge ( $options, $db->loadObjectList () );
		return HTMLHelper::_ ( 'select.genericlist', $options, $this->name, ' class="input-large form-select" ', 'id', 'title', $campaignId );
	}
}
