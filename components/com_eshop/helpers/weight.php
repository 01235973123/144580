<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EShopWeight
{
	public $weights = [];

	/**
	 * Store singleton instance of the class
	 *
	 * @var EShopWeight
	 */
	private static $instance;

	/**
	 * Get instance of the class
	 *
	 * @return EShopWeight
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor function
	 */
	public function __construct()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.weight_name, b.weight_unit')
			->from('#__eshop_weights AS a')
			->innerJoin('#__eshop_weightdetails AS b ON (a.id = b.weight_id)')
			->where('a.published = 1')
			->where('b.language = "' . Factory::getLanguage()->getTag() . '"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$this->weights[$row->id] = [
				'weight_id'       => $row->id,
				'weight_name'     => $row->weight_name,
				'weight_unit'     => $row->weight_unit,
				'exchanged_value' => $row->exchanged_value,
			];
		}
	}

	/**
	 *
	 * Function to convert a number between weight unit
	 *
	 * @param   float  $number
	 * @param   int    $weightFromId
	 * @param   int    $weightToId
	 *
	 * @return float
	 */
	public function convert($number, $weightFromId, $weightToId)
	{
		if (!$weightToId)
		{
			$weightToId = 1;
		}

		if (!$weightFromId)
		{
			$weightFromId = $weightToId;
		}

		if ($weightFromId == $weightToId || !isset($this->weights[$weightFromId]) || !isset($this->weights[$weightToId]))
		{
			return $number;
		}

		$weightFrom = $this->weights[$weightFromId]['exchanged_value'];
		$weightTo   = $this->weights[$weightToId]['exchanged_value'];

		return $number * ($weightTo / $weightFrom);
	}

	/**
	 *
	 * Function to format a number based on weight
	 *
	 * @param   float   $number
	 * @param   int     $weightId
	 * @param   string  $decimalPoint
	 * @param   string  $thousandPoint
	 *
	 * @return float
	 */
	public function format($number, $weightId, $decimalPoint = '.', $thousandPoint = ',')
	{
		if (isset($this->weights[$weightId]))
		{
			return number_format($number, 2, $decimalPoint, $thousandPoint) . $this->weights[$weightId]['weight_unit'];
		}

		return number_format($number, 2, $decimalPoint, $thousandPoint);
	}

	/**
	 *
	 * Function to get unit of a specific weight
	 *
	 * @param   int  $weightId
	 *
	 * @return string
	 */
	public function getUnit($weightId)
	{
		if (isset($this->weights[$weightId]))
		{
			return $this->weights[$weightId]['weight_unit'];
		}

		return '';
	}
}