<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

class os_offline extends OSFPayment
{
	/**
	 * Constructor
	 *
	 * @param   \Joomla\Registry\Registry  $params
	 * @param   array                      $config
	 */
	public function __construct($params, $config = [])
	{
		parent::__construct($params, $config);
	}

	/**
	 * Process payment
	 *
	 */
	public function processPayment($row, $data)
	{
		$app		= Factory::getApplication();
		$Itemid		= $app->input->getInt('Itemid');
		$config		= DonationHelper::getConfig();
		$session	= Factory::getApplication()->getSession();
		$session->set('id', $row->id);

		//$row->published =  1;
		//$row->store();

		DonationHelper::sendEmails($row, $config);
		$url		= Route::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id, $Itemid), false);
		$app->redirect($url);
	}

	/**
	 * Check to see whether this payment gateway support recurring payment
	 *
	 */
	public function getEnableRecurring()
	{
		return 0;
	}
}