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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for Membership Pro component
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipViewCategoriesHtml extends MPFViewHtml
{
	/**
	 * ID of parent category
	 *
	 * @var int
	 */
	protected $categoryId;

	/**
	 * The parent category
	 *
	 * @var stdClass
	 */
	protected $category;

	/**
	 * Categories data
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
	 * Display the view
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel();
		$items = $model->getData();

		$categoryId = (int) $model->getState()->get('id', 0);

		// If category id is passed, make sure it is valid and the user is allowed to access
		if ($categoryId)
		{
			$category = OSMembershipHelperDatabase::getCategory($categoryId);

			if (empty($category) || !in_array(
					$category->access,
					Factory::getApplication()->getIdentity()->getAuthorisedViewLevels()
				))
			{
				$app->enqueueMessage(Text::_('OSM_INVALID_CATEGORY_OR_NOT_AUTHORIZED'));
				$app->redirect(Uri::root(), 404);
			}

			$this->category = $category;
		}

		//Process content plugin in the description
		foreach ($items as $item)
		{
			$item->description = HTMLHelper::_('content.prepare', $item->description);
		}

		$this->categoryId = $categoryId;
		$this->config     = OSMembershipHelper::getConfig();
		$this->items      = $items;
		$this->pagination = $model->getPagination();

		if ($this->getLayout() === 'accordion')
		{
			$this->getAccordionData();
		}

		$this->prepareDocument();

		parent::display();
	}

	/**
	 * Get plans under in each category to display in accordion layout
	 *
	 * @return void
	 */
	protected function getAccordionData()
	{
		$config = OSMembershipHelper::getConfig();

		foreach ($this->items as $item)
		{
			// We need to get plans from category for displaying in accordion
			/* @var OSMembershipModelPlans $model */
			$model = MPFModel::getTempInstance('Plans', 'OSMembershipModel');

			$item->plans = $model->set('limitstart', 0)
				->set('limit', $this->params->get('number_plans_per_category', 20))
				->set('id', $item->id)
				->getData();

			foreach ($item->plans as $plan)
			{
				$taxRate = OSMembershipHelper::callOverridableHelperMethod('Helper', 'calculateTaxRate', [$plan->id]);

				if ($config->show_price_including_tax && !$config->setup_price_including_tax && $taxRate > 0)
				{
					$plan->price        = $plan->price * (1 + $taxRate / 100);
					$plan->trial_amount = $plan->trial_amount * (1 + $taxRate / 100);
					$plan->setup_fee    = $plan->setup_fee * (1 + $taxRate / 100);
				}

				$plan->short_description = HTMLHelper::_('content.prepare', $plan->short_description);
				$plan->description       = HTMLHelper::_('content.prepare', $plan->description);

				OSMembershipHelperRoute::addPlan($plan);
			}
		}
	}

	/**
	 * Set document meta-data and handle breadcumb if required
	 *
	 * @throws Exception
	 */
	protected function prepareDocument()
	{
		if (!$this->input->getInt('hmvc_call') && Factory::getApplication()->getMenu()->getActive())
		{
			$this->setDocumentMetadata($this->params);
		}
	}
}
