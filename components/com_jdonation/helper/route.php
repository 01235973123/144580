<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_ROOT . '/components/com_jdonation/helper/helper.php';

class DonationHelperRoute
{

	protected static $config;

	protected static $lookup;

	protected static $events;

	/**
	 * Function to get Donation Form router
	 *
	 * @param     $campaignId
	 * @param int $itemId
	 *
	 * @return string
	 */
	public static function getDonationFormRoute($campaignId, $itemId = 0)
	{
		$link = 'index.php?option=com_jdonation&view=donation';

		if ($campaignId)
		{
			$needles['donation']  = array($campaignId, 0);
			$needles['campaigns'] = array(0);
			$link .= '&campaign_id=' . $campaignId;
		}
		else
		{
			$needles['donation']  = array(0);
			$needles['campaigns'] = array(0);
		}
		if ($item = self::findItem($needles, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Function to get Donation Form router
	 *
	 * @param     $campaignId
	 * @param int $itemId
	 *
	 * @return string
	 */
	public static function getDonationCompleteRoute($id, $campaignId, $itemId = 0)
	{
		$link = 'index.php?option=com_jdonation&view=complete';

		if ($campaignId)
		{
			$needles['donation']  = array($campaignId, 0);
			$needles['campaigns'] = array(0);
		}
		else
		{
			$needles['donation']  = array(0);
			$needles['campaigns'] = array(0);
		}

		if($id > 0)
		{
			$link .= '&id='.$id;
		}

		if ($item = self::findItem($needles, $itemId))
		{
			if((int) $item > 0)
			{
				$link .= '&Itemid=' . $item;
			}
			else
			{
				$link .= '&Itemid=9999';
			}
		}

		//making this link is unique
		$link .= "&random_donor_code=".md5(rand(10,99));

		return $link;
	}

	/**
	 * Function to get Donation Form router
	 *
	 * @param     $campaignId
	 * @param int $itemId
	 *
	 * @return string
	 */
	public static function getDonationFailureRoute($id, $campaignId, $itemId = 0)
	{
		$link = 'index.php?option=com_jdonation&view=failure&id=' . $id;

		if ($campaignId)
		{
			$needles['donation']  = array($campaignId, 0);
			$needles['campaigns'] = array(0);
		}
		else
		{
			$needles['donation']  = array(0);
			$needles['campaigns'] = array(0);
		}

		if ($item = self::findItem($needles, $itemId))
		{
			if((int) $item > 0)
			{
				$link .= '&Itemid=' . $item;
			}
			else
			{
				$link .= '&Itemid=9999';
			}
		}

		return $link;
	}

	/**
	 *
	 * Function to get View Route
	 *
	 * @param string $view (cart, checkout)
	 *
	 * @return string
	 */
	public static function getViewRoute($view, $itemId)
	{
		//Create the link
		$link = 'index.php?option=com_jdonation&view=' . $view;
		if ($item = self::findView($view, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Get alias for campaign, using for sef router
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getCampaignTitle($id)
	{
		static $campaigns;
		if (empty($campaigns))
		{
			$db          = Factory::getContainer()->get('db');
			$fieldSuffix = "";
			if(!Factory::getApplication()->isClient('administrator'))
			{
				$fieldSuffix = DonationHelper::getFieldSuffix();
			}
			$query       = $db->getQuery(true);
			$query->select('id, alias' . $fieldSuffix . ' AS alias')->from('#__jd_campaigns');
			$db->setQuery($query);
			$campaigns = $db->loadObjectList('id');
		}

		return $campaigns[$id]->alias;
	}

	/**
	 * Find item id variable corresponding to the view
	 *
	 * @param $view
	 *
	 * @return int
	 */
	public static function findView($view, $itemId)
	{
		$needles = array($view => array(0));
		if ($item = self::findItem($needles, $itemId))
		{
			return $item;
		}
		else
		{
			return 0;
		}
	}

	/**
	 *
	 * Function to find Itemid
	 *
	 * @param string $needles
	 *
	 * @return int
	 */
	public static function findItem($needles = null, $itemId = 0)
	{
		
		$app   = Factory::getApplication();
		$menus = $app->getMenu('site');

		$tempArr = [];

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();
			$component    = ComponentHelper::getComponent('com_jdonation');
			$items        = $menus->getItems('component_id', $component->id);
			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view]))
					{
						self::$lookup[$view] = array();
					}
					if (isset($item->query['campaign_id']))
					{
						self::$lookup[$view][$item->query['campaign_id']] = $item->id;
					}
					else
					{
						self::$lookup[$view][0] = $item->id;
					}
				}
				$tempArr[] = $item->id;
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$view][(int) $id]))
						{
							return self::$lookup[$view][(int) $id];
						}
					}
				}
			}
		}

		if($itemId > 0 && in_array($itemId, $tempArr))
		{

			//Return default item id
			return $itemId;
		}
		else
		{
			return $tempArr[0];
		}
	}
}
