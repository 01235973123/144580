<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgContentMembershipPlans extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepare' => 'onContentPrepare',
		];
	}

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Parse article and display plans if configured
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onContentPrepare(Event $event): void
	{
		[$context, $article, $params, $limitstart] = array_values($event->getArguments());

		if (!str_contains($article->text, '{membershipplans'))
		{
			return;
		}

		$regex         = '#{membershipplans ids="(.*?)"}#s';
		$article->text = preg_replace_callback($regex, [&$this, 'displayPlans'], $article->text);
	}

	/**
	 * Replace callback function
	 *
	 * @param   array  $matches
	 *
	 * @return string
	 * @throws Exception
	 */
	private function displayPlans($matches)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$planIds = $matches[1];
		$layout  = $this->params->get('layout_type', 'default');
		OSMembershipHelper::loadLanguage();
		$request = [
			'option'          => 'com_osmembership',
			'view'            => 'plans',
			'layout'          => $layout,
			'filter_plan_ids' => $planIds,
			'limit'           => 0,
			'hmvc_call'       => 1,
			'Itemid'          => OSMembershipHelper::getItemid(),
		];
		$input   = new MPFInput($request);
		$config  = [
			'default_controller_class' => 'OSMembershipController',
			'default_view'             => 'plans',
			'class_prefix'             => 'OSMembership',
			'language_prefix'          => 'OSM',
			'remember_states'          => false,
			'ignore_request'           => false,
		];

		ob_start();

		//Initialize the controller, execute the task
		MPFController::getInstance('com_osmembership', $input, $config)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		if (!$this->app->isClient('site'))
		{
			return;
		}

		parent::registerListeners();
	}
}
