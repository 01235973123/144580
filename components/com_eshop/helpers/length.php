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

class EShopLength
{
	public $lengths = [];

	/**
	 * Store singleton instance of the class
	 *
	 * @var EShopLength
	 */
	private static $instance;

	/**
	 * Get instance of the class
	 *
	 * @return EShopLength
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
		$query->select('a.*, b.length_name, b.length_unit')
			->from('#__eshop_lengths AS a')
			->innerJoin('#__eshop_lengthdetails AS b ON (a.id = b.length_id)')
			->where('a.published = 1')
			->where('b.language = "' . Factory::getLanguage()->getTag() . '"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$this->lengths[$row->id] = [
				'length_id'       => $row->id,
				'length_name'     => $row->length_name,
				'length_unit'     => $row->length_unit,
				'exchanged_value' => $row->exchanged_value,
			];
		}
	}

	/**
	 *
	 * Function to convert a number between length unit
	 *
	 * @param   float  $number
	 * @param   int    $lengthFromId
	 * @param   int    $lengthToId
	 *
	 * @return float
	 */
	public function convert($number, $lengthFromId, $lengthToId)
	{
		if (!$lengthToId)
		{
			$lengthToId = 1;
		}

		if (!$lengthFromId)
		{
			$lengthFromId = $lengthToId;
		}

		if ($lengthFromId == $lengthToId)
		{
			return $number;
		}

		if (isset($this->lengths[$lengthFromId]))
		{
			$lengthFromId = $this->lengths[$lengthFromId]['exchanged_value'];
		}
		else
		{
			$lengthFromId = 0;
		}

		if (isset($this->lengths[$lengthToId]))
		{
			$lengthToId = $this->lengths[$lengthToId]['exchanged_value'];
		}
		else
		{
			$lengthToId = 0;
		}

		return $number * ($lengthToId / $lengthFromId);
	}

	/**
	 *
	 * Function to format a number based on length
	 *
	 * @param   float   $number
	 * @param   int     $lengthId
	 * @param   string  $decimalPoint
	 * @param   string  $thousandPoint
	 *
	 * @return float
	 */
	public function format($number, $lengthId, $decimalPoint = '.', $thousandPoint = ',')
	{
		if (isset($this->lengths[$lengthId]))
		{
			return number_format($number, 2, $decimalPoint, $thousandPoint) . $this->lengths[$lengthId]['length_unit'];
		}

		return number_format($number, 2, $decimalPoint, $thousandPoint);
	}

	/**
	 *
	 * Function to get unit of a specific length
	 *
	 * @param   int  $lengthId
	 *
	 * @return string
	 */
	public function getUnit($lengthId)
	{
		if (isset($this->lengths[$lengthId]))
		{
			return $this->lengths[$lengthId]['length_unit'];
		}

		return '';
	}
}