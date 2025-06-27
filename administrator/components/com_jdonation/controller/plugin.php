<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

class DonationControllerPlugin extends DonationController
{

	/**
     * Install the payment plugin from selected package
     *
     */
	public function install()
	{
		$model = $this->getModel('plugin', array('ignore_request' => true));
		try
		{
			$model->install($this->input);
			$this->setMessage(Text::_('Plugin installed'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}
		$this->setRedirect($this->getViewListUrl());
	}

	/**
     * Uninstall the selected payment plugin
     */
	public function uninstall()
	{
		$model = $this->getModel('plugin', array('ignore_request' => true));
		$cid = $this->input->get('cid', array(), 'array');
		$pluginId = (int) $cid[0];
		try
		{
			$model->uninstall($pluginId);
			$this->setMessage(Text::_('The plugin was successfully uninstalled'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}
		$this->setRedirect($this->getViewListUrl());
	}

	function save_order_ajax()
	{
		$jinput			= Factory::getApplication()->input;
        $db				= Factory::getContainer()->get('db');
        $cid 			= $jinput->get( 'cid', array(), 'array' );
        $order			= $jinput->get( 'order', array(), 'array' );
        $row			= new DonationTablePlugin($db); //Table::getInstance('Plugin','DonationTable');
        $groupings		= array();
        // update ordering values
        $txt = "";
        for( $i=0; $i < count($cid); $i++ )
        {
            $row->load( $cid[$i] );
            if ($row->ordering != $order[$i])
            {
                $row->ordering = $order[$i];
                $row->store();
            } // if
        } // for
		for( $i=0; $i < count($cid); $i++ )
        {
			$row->load( $cid[$i] );
			$row->reorder();
		}
	}
} 
