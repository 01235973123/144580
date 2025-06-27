<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class OSMembershipModelK2items extends MPFModelList
{
	/**
	 * List of active subscription plans of the current logged in user
	 *
	 * @var array
	 */
	protected $activePlanIds = [];

	/**
	 * List of categories which current users can access to
	 *
	 * @var array
	 */
	protected $catIds = [];

	/**
	 * OSMembershipModelArticles constructor.
	 *
	 * @param   array  $config
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans();
		$db                  = $this->getDbo();
		$query               = $db->getQuery(true);

		// Get categories
		$query->select('id, params')
			->from('#__osmembership_plans')
			->whereIn('id', $this->activePlanIds);
		$db->setQuery($query);
		$plans  = $db->loadObjectList();
		$catIds = [];

		foreach ($plans as $plan)
		{
			$params = new Registry($plan->params);

			if ($articleCategories = $params->get('k2_item_categories'))
			{
				$catIds = array_merge($catIds, explode(',', $articleCategories));
			}
		}

		$this->catIds = $catIds;
	}

	/**
	 * Get data
	 *
	 * @param   bool  $returnIterator
	 *
	 * @return array
	 */
	public function getData($returnIterator = false)
	{
		if (count($this->activePlanIds) == 1)
		{
			return [];
		}

		return parent::getData();
	}

	/**
	 * Get total articles which user can access
	 *
	 * @return array|int
	 */
	public function getTotal()
	{
		if (count($this->activePlanIds) == 0)
		{
			return 0;
		}

		return parent::getTotal();
	}

	protected function buildListQuery()
	{
		$db         = $this->getDbo();
		$query      = $db->getQuery(true);
		$articleIds = [];

		if (count($this->catIds))
		{
			$query->select('a.id')
				->from('#__k2_items AS a')
				->innerJoin('#__k2_categories AS c ON a.catid = c.id')
				->whereIn('c.id', $this->catIds)
				->where('a.published = 1');
			$db->setQuery($query);
			$articleIds = array_merge($articleIds, $db->loadColumn());
		}

		$query->clear()
			->select('a.id')
			->from('#__k2_items AS a')
			->innerJoin('#__osmembership_k2items AS b ON a.id = b.article_id')
			->whereIn('b.plan_id', $this->activePlanIds)
			->where('a.published = 1');
		$db->setQuery($query);
		$articleIds = array_merge($articleIds, $db->loadColumn());

		if (empty($articleIds))
		{
			$articleIds = [0];
		}

		$query = $this->query;

		$query->select('a.id, a.catid, a.title, a.alias, a.hits, c.name AS category_name')
			->from('#__k2_items AS a')
			->innerJoin('#__k2_categories AS c ON a.catid = c.id')
			->whereIn('a.id', $articleIds)
			->order('a.ordering');

		return $query;
	}
}
