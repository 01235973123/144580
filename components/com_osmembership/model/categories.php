<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseQuery;
use Joomla\Utilities\ArrayHelper;

class OSMembershipModelCategories extends MPFModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('id', 'int', 0);
	}

	/**
	 * Method to get categories data
	 *
	 * @param   bool  $returnIterator
	 *
	 * @access public
	 * @return array
	 */
	public function getData($returnIterator = false)
	{
		// Let load the content if it doesn't already exist
		if (empty($this->data))
		{
			$rows = parent::getData();

			foreach ($rows as $row)
			{
				$row->total_plans = OSMembershipHelper::countPlans($row->id);
			}

			$this->data = $rows;
		}

		return $this->data;
	}


	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(DatabaseQuery $query)
	{
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		$query->select('tbl.*');

		if ($fieldSuffix)
		{
			OSMembershipHelperDatabase::getMultilingualFields($query, ['tbl.title', 'tbl.description'], $fieldSuffix);
		}

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		$query->where('tbl.published = 1')
			->where('tbl.parent_id = ' . $this->state->id)
			->whereIn('tbl.access', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels());

		$params               = $this->getParams();
		$categoriesId         = array_filter(ArrayHelper::toInteger($params->get('categories_id')));
		$excludeCategoriesIds = array_filter(ArrayHelper::toInteger($params->get('exclude_categories_id')));

		if (count($categoriesId) > 0)
		{
			$query->whereIn('tbl.id', $categoriesId);
		}

		if (count($excludeCategoriesIds))
		{
			$query->whereNotIn('tbl.id', $excludeCategoriesIds);
		}

		return $this;
	}
}
