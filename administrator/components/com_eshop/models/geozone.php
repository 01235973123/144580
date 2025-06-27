<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelGeozone extends EShopModel
{

	public function store(&$data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		if ($data['id'])
		{
			$this->_removeGeozoneZones($data['id'], false);
		}
		parent::store($data);
		$geozoneId = intval($data['id']);
		//Save new data
		if (isset($data['country_id']))
		{
			$countryIds = $data['country_id'];
			$zoneIds    = $data['zone'];
			$query->clear();
			$query->insert('#__eshop_geozonezones')
				->columns('geozone_id, zone_id, country_id');
			$processZone = false;
			foreach ($countryIds as $key => $countryId)
			{
				if ($zoneIds[$key] > 0)
				{
					$zoneId = $db->quote($zoneIds[$key]);
					$query->values("$geozoneId, $zoneId, $countryId");
					$processZone = true;
				}
			}
			if ($processZone)
			{
				$db->setQuery($query);
				$db->execute();
			}
		}

		//Process assign countries to current Geo Zone		
		if (isset($data['countries_list']))
		{
			$this->_removeGeozoneZones($geozoneId);
			//When All Countries is selected, it means that this Geo Zone is for all countries  
			if ($data['countries_list'][0] == '-1')
			{
				if ($data['include_countries'])
				{
					$sql = "INSERT INTO #__eshop_geozonezones" .
						" (geozone_id, zone_id, country_id)" .
						" (SELECT $geozoneId, 0, id FROM #__eshop_countries WHERE published = 1)";
					$db->setQuery($sql);
					$db->execute();
				}
			}
			else
			{
				if ($data['include_countries'])
				{
					//Assign Geo Zone to selected countries
					$query->clear();
					$query->insert('#__eshop_geozonezones')
						->columns('geozone_id, zone_id, country_id');
					foreach ($data['countries_list'] as $countryId)
					{
						$query->values("$geozoneId, 0, $countryId");
					}
					$db->setQuery($query);
					$db->execute();
				}
				else
				{
					//Assign Geo Zone to un-selected countries
					$sql = "INSERT INTO #__eshop_geozonezones" .
						" (geozone_id, zone_id, country_id)" .
						" (SELECT $geozoneId, 0, id FROM #__eshop_countries WHERE id NOT IN (" . implode(
							',',
							$data['countries_list']
						) . ") AND published = 1)";
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}

		//Process assign postcodes to Geo Zone
		$startPostcode     = $data['start_postcode'];
		$endPostcode       = $data['end_postcode'];
		$geozonepostcodeId = $data['geozonepostcode_id'];
		//Remove some postcodes first
		$query->clear()
			->delete('#__eshop_geozonepostcodes')
			->where('geozone_id = ' . intval($geozoneId));
		if (isset($geozonepostcodeId))
		{
			$query->where('id NOT IN (' . implode(',', $geozonepostcodeId) . ')');
		}
		$db->setQuery($query);
		$db->execute();

		if (isset($startPostcode))
		{
			for ($i = 0; $n = count($startPostcode), $i < $n; $i++)
			{
				$row                 = new EShopTable('#__eshop_geozonepostcodes', 'id', $this->getDbo());
				$row->id             = $geozonepostcodeId[$i] ?? 0;
				$row->geozone_id     = $geozoneId;
				$row->start_postcode = $startPostcode[$i];
				$row->end_postcode   = $endPostcode[$i];
				$row->store();
			}
		}

		return true;
	}

	/**
	 *
	 * Private function to remove geo zone zones
	 *
	 * @param   int      $geozoneId
	 * @param   boolean  $onlyAllZone
	 */
	private function _removeGeozoneZones($geozoneId, $onlyAllZone = true)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->clear();
		$query->delete('#__eshop_geozonezones')
			->where('geozone_id = ' . $geozoneId);
		//If only remove countries with all zone
		if ($onlyAllZone)
		{
			$query->where('zone_id = 0');
		}
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Method to remove geozones
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->delete('#__eshop_geozones')
				->where('id IN (' . $cids . ')')
				->where('id NOT IN (SELECT  DISTINCT(geozone_id) FROM #__eshop_geozonezones)');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			$numItemsDeleted = $db->getAffectedRows();
			if ($numItemsDeleted < count($cid))
			{
				//Removed warning
				return 2;
			}
		}

		//Removed success
		return 1;
	}

	/**
	 * Function to copy geozone and zones for it
	 * @see EShopModel::copy()
	 */
	public function copy($id)
	{
		$copiedGeozoneId = parent::copy($id);
		$db              = $this->getDbo();
		$sql             = 'INSERT INTO #__eshop_geozonezones'
			. ' (geozone_id, zone_id, country_id)'
			. ' SELECT ' . $copiedGeozoneId . ', zone_id, country_id'
			. ' FROM #__eshop_geozonezones'
			. ' WHERE geozone_id = ' . intval($id);
		$db->setQuery($sql);
		$db->execute();

		return $copiedGeozoneId;
	}

}