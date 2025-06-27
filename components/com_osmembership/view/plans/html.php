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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Pagination\Pagination;

class OSMembershipViewPlansHtml extends MPFViewHtml
{
	/**
	 * ID of category
	 *
	 * @var int
	 */
	protected $categoryId;

	/**
	 * The category
	 *
	 * @var stdClass
	 */
	protected $category;

	/**
	 * List of sub-categories
	 *
	 * @var array
	 */
	protected $categories = [];

	/**
	 * Plans data
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination
	 *
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * The component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Bootstrap helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Name of views which could be used to get menu item parameters for the current view
	 *
	 * @var array
	 */
	protected $paramsViews = ['categories', 'plans'];

	/**
	 * Display plans
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$config = OSMembershipHelper::getConfig();
		$model  = $this->getModel();
		$items  = $model->getData();

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item    = $items[$i];
			$taxRate = OSMembershipHelper::callOverridableHelperMethod('Helper', 'calculateTaxRate', [$item->id]);

			if ($config->show_price_including_tax && !$config->setup_price_including_tax && $taxRate > 0)
			{
				$item->price        = $item->price * (1 + $taxRate / 100);
				$item->trial_amount = $item->trial_amount * (1 + $taxRate / 100);

				$item->setup_fee = $item->setup_fee * (1 + $taxRate / 100);
			}

			$item->short_description = HTMLHelper::_('content.prepare', $item->short_description);
			$item->description       = HTMLHelper::_('content.prepare', $item->description);
		}

		$categoryId = (int) $model->getState()->get('id', 0);

		// Load sub-categories of the current category
		if ($categoryId > 0)
		{
			/* @var OSMembershipModelCategories $categoriesModel */
			$categoriesModel = MPFModel::getTempInstance('Categories', 'OSMembershipModel');

			$this->categories = $categoriesModel->limitstart(0)
				->limit(0)
				->filter_order('tbl.ordering')
				->id($categoryId)
				->getData();
		}

		$category = OSMembershipHelperDatabase::getCategory($categoryId);

		if ($category)
		{
			$category->description = HTMLHelper::_('content.prepare', $category->description);
		}

		$this->category        = $category;
		$this->pagination      = $model->getPagination();
		$this->items           = $items;
		$this->config          = $config;
		$this->categoryId      = $categoryId;
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		// Add plans data to route class to reduce necessary database query
		foreach ($items as $item)
		{
			OSMembershipHelperRoute::addPlan($item);
		}

		$this->prepareDocument();

		parent::display();
	}

	/**
	 * Set document meta-data and handle breadcumb if required
	 *
	 * @throws Exception
	 */
	protected function prepareDocument()
	{
		if ($this->input->getInt('hmvc_call'))
		{
			return;
		}

		$active = Factory::getApplication()->getMenu()->getActive();

		if (!$active)
		{
			return;
		}

		if (isset($active->query['view']) && $active->query['view'] == 'plans')
		{
			// This is direct menu link to category view, so use the layout from menu item setup
		}
		elseif ($this->input->getInt('hmvc_call') && $this->input->getCmd('layout'))
		{
			// Use layout from the HMVC call, in this case, it's from MP view module
		}
		else
		{
			$this->setLayout('default');
		}

		$this->setDocumentMetadata($this->params);
		$this->handleBreadcrumb($active);
	}

	/**
	 * Add breadcrumb items
	 *
	 * @param   MenuItem  $active
	 */
	protected function handleBreadcrumb($active)
	{
		if (isset($active->query['view']) && $active->query['view'] === 'categories' && $this->category)
		{
			Factory::getApplication()->getPathway()->addItem($this->category->title);
		}
	}
}
