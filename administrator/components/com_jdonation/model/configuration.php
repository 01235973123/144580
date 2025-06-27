<?php
/**
 * @version        5.12.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Factory;

class DonationModelConfiguration extends OSFModel
{

	public function __construct($config = array())
	{
		$config['table'] = '#__jd_configs';
		parent::__construct($config);
	}

	/**
	 * Get configuration data
	 */
	function getData()
	{
		return DonationHelper::getConfig();
	}

	/**
	 * Store the configuration data
	 *
	 * @param array $post
	 */
	function store($data)
	{
		$db  = $this->getDbo();
		$row = $this->getTable();
		$db->truncateTable('#__jd_configs');
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			$row->id           = 0;
			$row->config_key   = $key;
			$row->config_value = $value;
			$row->store();
		}
		$mailchimp_list_ids = Factory::getApplication()->input->get('mailchimp_list_ids',array(),'array');
		if(count($mailchimp_list_ids) > 0)
		{
			$mailchimp_list_ids = implode(",", $mailchimp_list_ids);
			$db->setQuery("Select count(id) from #__jd_configs where config_key like 'mailchimp_list_ids'");
			$count = $db->loadResult();
			if($count > 0)
			{
				$db->setQuery("Update #__jd_configs set config_value = '$mailchimp_list_ids' where config_key like 'mailchimp_list_ids'");
			}
			else
			{
				$db->setQuery("Insert into #__jd_configs (id, config_key, config_value) values (NULL, 'mailchimp_list_ids', '$mailchimp_list_ids')");
			}
			$db->execute();
		}
		return true;
	}
}
