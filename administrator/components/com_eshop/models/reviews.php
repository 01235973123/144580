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

use Joomla\CMS\Component\ComponentHelper;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelReviews extends EShopModelList
{

	public function __construct($config)
	{
		$config['search_fields'] = ['a.review', 'a.author'];
		parent::__construct($config);
	}

	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$query = $db->getQuery(true);
		$query->select('a.*, b.product_name')
			->from($this->mainTable . ' AS a ')
			->innerJoin('#__eshop_productdetails AS b ON (a.product_id = b.product_id)')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$where = $this->_buildContentWhereArray();

		if (count($where))
		{
			$query->where($where);
		}

		$orderby = $this->_buildContentOrderBy();

		if ($orderby != '')
		{
			$query->order($orderby);
		}

		return $query;
	}
}