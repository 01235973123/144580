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
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class DonationModelField extends OSFModelAdmin
{

	public static $protectedFields = array('first_name', 'email');

	/**
	 * Store custom field
	 *
	 * @param OSFInput $input
	 * @param array    $ignore
	 *
	 * @return bool|void
	 */
	public function store($input, $ignore = [])
	{
		$db		 = Factory::getContainer()->get('db');
		$row     = $this->getTable();
		$fieldId = $input->getInt('id', 0);
		$config  = DonationHelper::getConfig();
		if ($fieldId)
		{
			$row->load($fieldId);
		}
		if (in_array($row->name, self::$protectedFields))
		{
			$ignore = ['field_type', 'published', 'validation_rules', 'required'];
		}
		if($fieldId > 0)
		{
			parent::store($input, $ignore);
		}
		else
		{
			if($config->field_campaign)
			{
				$campaign_ids = $input->get('campaign_ids',[],'array');

				if(count($campaign_ids))
				{
					foreach($campaign_ids as $campaign_id)
					{
						$input->set('id', 0);
						$field_name = $input->get('name');
						$field_name = $field_name."_".$campaign_id;
						$input->set('name', $field_name);
						$input->set('campaign_id', $campaign_id);
						parent::store($input, $ignore);
					}
				}
				else
				{
					$query		= $db->getQuery(true);
					$query->select('id')
						->from('#__jd_campaigns');
					$db->setQuery($query);
					$campaigns	= $db->loadColumn();
					if(count($campaigns))
					{
						foreach($campaigns as $campaign_id)
						{
							$input->set('id', 0);
							$field_name = $input->get('name');
							$field_name = $field_name."_".$campaign_id;
							$input->set('name', $field_name);
							$input->set('campaign_id', $campaign_id);
							parent::store($input, $ignore);
						}
					}
				}
			}
			else
			{
				parent::store($input, $ignore);
			}
		}
	}

	/**
	 *
	 * Publish, unpublish custom fields
	 *
	 * @param array $pks
	 * @param int   $value
	 */
	public function publish($pks, $value = 1)
	{
		if (count($pks))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__jd_fields')
				->where('name IN ("' . implode('","', self::$protectedFields) . '")');
			$db->setQuery($query);
			$protectedFieldIds = $db->loadColumn();
			$pks               = array_diff($pks, $protectedFieldIds);
			if (count($pks))
			{
				parent::publish($pks, $value);
			}
		}
	}

	/**
	 * Method to remove  fields
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function delete($cid = array())
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__jd_fields')
				->where('name IN ("' . implode('","', self::$protectedFields) . '")');
			$db->setQuery($query);
			$protectedFieldIds = $db->loadColumn();
			$cid               = array_diff($cid, $protectedFieldIds);
			if (count($cid))
			{
				$query->clear();
				$query->delete('#__jd_field_value')->where('field_id IN (' . implode(',', $cid) . ')');
				$db->setQuery($query);
				$db->execute();
				parent::delete($cid);
			}
		}
	}

	/**
	 * Change require status
	 *
	 * @param array $cid
	 * @param int   $state
	 *
	 * @return boolean
	 */
	public function required($cid, $state)
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__jd_fields')
				->where('name IN ("' . implode('","', self::$protectedFields) . '")');
			$db->setQuery($query);
			$protectedFieldIds = $db->loadColumn();
			$cid               = array_diff($cid, $protectedFieldIds);
			if (count($cid))
			{
				$query->clear();
				$query->update('#__jd_fields')
					->set('required = ' . $state)
					->where('id IN (' . implode(',', $cid) . ' )');
				$db->setQuery($query);
				$db->execute();
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}