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

class plgContentMPSubscriptionForm extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepare' => 'onContentPrepare',
		];
	}

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

		if (!str_contains($article->text, 'mpsubscriptionform'))
		{
			return;
		}

		$regex         = '#{mpsubscriptionform (\d+)}#s';
		$article->text = preg_replace_callback($regex, [&$this, 'displaySubscriptionForm'], $article->text);
	}

	/**
	 * Replace callback function
	 *
	 * @param $matches
	 *
	 * @return string
	 * @throws Exception
	 */
	private function displaySubscriptionForm($matches)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$planId = (int) $matches[1];

		if (!$planId)
		{
			return '';
		}

		$plan = OSMembershipHelperDatabase::getPlan($planId);

		if (!$plan)
		{
			return '';
		}

		$layout = $this->params->get('layout_type', 'default');
		OSMembershipHelper::loadLanguage();

		$Itemid = OSMembershipHelperRoute::getPlanMenuId($plan->id, $plan->category_id, OSMembershipHelper::getItemid());

		$request = [
			'option'    => 'com_osmembership',
			'view'      => 'register',
			'layout'    => $layout,
			'id'        => $planId,
			'limit'     => 0,
			'hmvc_call' => 1,
			'Itemid'    => $Itemid,
		];

		$input = new MPFInput($request);

		$config = [
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
