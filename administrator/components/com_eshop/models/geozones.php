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
class EShopModelGeozones extends EShopModelList
{
	public function __construct($config)
	{
		$config['search_fields'] = ['a.geozone_name'];

		parent::__construct($config);
	}

	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')
			->from('#__eshop_geozones AS a');

		return $query;
	}
}