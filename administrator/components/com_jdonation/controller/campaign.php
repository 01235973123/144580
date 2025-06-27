<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

class DonationControllerCampaign extends DonationController
{
	function save_order_ajax()
	{
		$jinput			= Factory::getApplication()->input;
        $db				= Factory::getContainer()->get('db');
        $cid 			= $jinput->get( 'cid', array(), 'array' );
        $order			= $jinput->get( 'order', array(), 'array' );
        $row			= new DonationTableCampaign($db); //Table::getInstance('Campaign','DonationTable');
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
