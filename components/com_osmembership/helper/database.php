<?php

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class OSMembershipHelperDatabase
{
	/**
	 * Get category data from database
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getCategory($id)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_categories')
			->where('id=' . (int) $id);

		if ($fieldSuffix = OSMembershipHelper::getFieldSuffix())
		{
			self::getMultilingualFields($query, ['title', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get category data from database
	 *
	 * @param   int     $id
	 * @param   string  $fieldSuffix
	 *
	 * @return mixed
	 */
	public static function getPlan($id, $fieldSuffix = null)
	{
		/* @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');

		if ($fieldSuffix === null)
		{
			$fieldSuffix = OSMembershipHelper::getFieldSuffix();
		}

		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_plans')
			->where('id=' . (int) $id);

		if ($fieldSuffix)
		{
			self::getMultilingualFields($query, ['title', 'short_description', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get all published subscription plans
	 *
	 * @param   string  $key
	 *
	 * @return mixed
	 */
	public static function getAllPlans($key = '')
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_plans')
			->where('published = 1');

		if ($fieldSuffix = OSMembershipHelper::getFieldSuffix())
		{
			self::getMultilingualFields($query, ['title', 'short_description', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObjectList($key);
	}

	/**
	 * Helper method to get fields from database table in case the site is multilingual
	 *
	 * @param   DatabaseQuery  $query
	 * @param   array          $fields
	 * @param   string         $fieldSuffix
	 */
	public static function getMultilingualFields(DatabaseQuery $query, $fields = [], $fieldSuffix = '')
	{
		/* @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');

		foreach ($fields as $field)
		{
			$alias  = $field;
			$dotPos = strpos($field, '.');

			if ($dotPos !== false)
			{
				$alias = substr($field, $dotPos + 1);
			}

			$query->select($db->quoteName($field . $fieldSuffix, $alias));
		}
	}

	/**
	 * Method to get a upgrade rule base on given id
	 *
	 * @param   int  $id
	 *
	 * @return stdClass|null
	 */
	public static function getUpgradeRule($id)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_upgraderules')
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to get a renew option
	 *
	 * @param   int  $id
	 *
	 * @return stdClass|null
	 */
	public static function getRenewOption($id)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_renewrates')
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadObject();
	}
}
