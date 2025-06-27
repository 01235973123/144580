<?php

/**
 * @version		3.8
 * @package		Joomla
 * @subpackage	Joom Donation
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2009 - 2023 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;

class DonationViewPlugin extends HtmlView
{
	function display($tpl = null)
	{
		$item = $this->get ( 'Data' );
		$lists ['published'] = HTMLHelper::_ ( 'select.booleanlist', 'published', 'class="inputbox"', $item->published );
		$registry = new Registry ();
		$registry->loadString ( $item->params );
		$data = new stdClass ();
		$data->params = $registry->toArray ();
		$form = Form::getInstance ( 'jdonation', JPATH_ROOT . '/components/com_jdonation/payments/' . $item->name . '.xml', array (), false, '//config' );
		$form->bind ( $data );
		$this->item = $item;
		$this->lists = $lists;
		$this->form = $form;
		
		parent::display ( $tpl );
	}
}
