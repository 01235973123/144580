<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class os_moneybooker extends OSFPayment
{
	/**
	 * Constructor functions, init some parameter
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array())
	{
		parent::__construct($params, $config);
		$this->url = 'https://www.moneybookers.com/app/payment.pl';
		$this->setParameter('pay_to_email', $params->get('mb_merchant_email', ''));
		$this->setParameter('currency', $params->get('mb_currency', 'USD'));
		$this->setParameter('language', 'EN');
	}

	/**
	 * Process payment for onetime donation
	 *
	 * @param JTable $row
	 * @param array  $data
	 */
	public function processPayment($row, $data)
	{
		$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);
		$siteUrl = Uri::base();
		$this->setParameter('transaction_id', $row->transaction_id);
		$this->setParameter('amount', $data['gateway_amount']);
		$this->setParameter('merchant_fields', 'id');
		$this->setParameter('id', $row->id);
		$this->setParameter('return_url', Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . Route::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id, $Itemid), false));
		$this->setParameter('cancel_url', $siteUrl . 'index.php?option=com_jdonation&view=cancel' . ($row->campaign_id > 0 ? '&campaign_id=' . $row->campaign_id : '') . '&Itemid=' . $Itemid);
		$this->setParameter('status_url', $siteUrl . 'index.php?option=com_jdonation&task=payment_confirm&payment_method=os_moneybooker');
		$this->setParameter('firstname', $data['first_name']);
		$this->setParameter('lastname', $data['last_name']);
		$this->setParameter('address', $data['address']);
		$this->setParameter('address2', $data['address2']);
		$this->setParameter('phone_number', $row->phone);
		$this->setParameter('postal_code', $row->zip);
		$this->setParameter('city', $row->city);
		$this->setParameter('state', $row->state);
		$this->setParameter('country', $row->country);
		$this->renderRedirectForm();
	}

	/**
	 * Confirm payment process
	 * @return boolean : true if success, otherwise return false
	 */
	public function verifyPayment()
	{
		$input = Factory::getApplication()->input;
		$id    = $input->getInt('id', 0);
		$row   = Table::getInstance('jdonation', 'Table');
		$row->load($id);
		$this->onPaymentSuccess($row, $input->getString('mb_transaction_id'));
	}
}
