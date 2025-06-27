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

use Joomla\CMS\Factory;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelAttributes extends EShopModelList
{

	public function __construct($config)
	{
		$input                = Factory::getApplication()->input;
		$config['state_vars'] = [
			'attributegroup_id' => ['', 'cmd', 1],
		];

		$config['search_fields']       = ['b.attribute_name'];
		$config['translatable']        = true;
		$config['translatable_fields'] = ['attribute_name'];

		parent::__construct($config);
	}

	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$query = $db->getQuery(true);
		$query = parent::_buildQuery();

		if ($state->attributegroup_id)
		{
			$query->where('a.attributegroup_id = ' . $state->attributegroup_id);
		}

		return $query;
	}
}