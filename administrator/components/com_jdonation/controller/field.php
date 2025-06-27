<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * EventBooking Field controller
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class DonationControllerField extends DonationController
{

	public function __construct(OSFInput $input = null, array $config = array())
	{
		parent::__construct($input, $config);
		
		$this->registerTask('un_required', 'required');
	}

	/**
     * Require the selected fields
     *
     */
	function required()
	{
		$cid = $this->input->get('cid', array(), 'array');
        $cid = ArrayHelper::toInteger($cid);
		//JArrayHelper::toInteger($cid);
		$task = $this->getTask();
		if ($task == 'required')
		{
			$state = 1;
		}
		else
		{
			$state = 0;
		}
		$model = $this->getModel();
		if($model->required($cid, $state))
		{
			$msg = Text::_('Field Required status was successfully updated');
		}
		else
		{
			$msg = Text::_('It is impossible to change Required status of core fields');
		}
		$this->setRedirect(Route::_('index.php?option=com_jdonation&view=fields', false), $msg);
	}

	function save_order_ajax()
	{
		$jinput			= Factory::getApplication()->input;
        $db				= Factory::getContainer()->get('db');
        $cid 			= $jinput->get( 'cid', array(), 'array' );
        $order			= $jinput->get( 'order', array(), 'array' );
        $row			= Table::getInstance('Field','DonationTable');
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
