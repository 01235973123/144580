<?php
/**
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;



class plgSystemJdonation extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * Flag to see whether the plan subscription status for this record has been processed or not
	 *
	 * @var bool
	 */
	private $subscriptionProcessed = false;

	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 */
	public function __construct($subject, array $config = [])
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_jdonation/jdonation.php'))
		{
			return;
		}

		parent::__construct($subject, $config);

		require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';
	}

	
	/**
	 * This method is run after subscription become active, ie after user complete payment or admin approve the subscription
	 *
	 * @param   DonationTableDonor  $row
	 *
	 * @throws Exception
	 */
	public function onAfterPaymentSuccess($row)
	{
		if (!($row instanceof Table))
		{
			return;
		}
		
		if ($row->getTableName() !== '#__jd_donors')
		{
			return;
		}
		$config = DonationHelper::getConfig();

		// Create user account (in case the system is configured to generate user account when subscription is active)
		if (!$row->user_id && $row->username && $row->user_password)
		{
			$this->createUserAccount($row);
		}
	}

	/**
	 * Create user account for subscriber after subscription being active
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @throws Exception
	 */
	protected function createUserAccount($row)
	{
		$data['username']   = $row->username;
		$data['first_name'] = $row->first_name;
		$data['last_name']  = $row->last_name;
		$data['email']      = $row->email;

		//Password
		$data['password1']	= DonationHelperCryptor::decrypt($row->user_password);

		try
		{
			$row->user_id	= (int) DonationHelper::saveRegistration($data);

			$db				= Factory::getDbo();
			$db->setQuery("Update #__jd_donors set user_id = '$row->user_id' where id = '$row->id'");
			$db->execute();

			$config			= DonationHelper::getConfig();
		}
		catch (Exception $e)
		{
			DonationHelper::logData(__DIR__ . '/create_user_error.txt', $data, $e->getMessage());
		}
	}
}
