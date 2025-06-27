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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgContentMPRestriction extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepare' => 'onContentPrepare',
		];
	}

	/**
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onContentPrepare(Event $event): void
	{
		[$context, $row, $params, $page] = array_values($event->getArguments());

		if (!str_contains($row->text, '{mprestriction'))
		{
			return;
		}

		// Search for this tag in the content
		$regex     = '#{mprestriction ids="(.*?)"}(.*?){/mprestriction}#s';
		$row->text = preg_replace_callback($regex, [&$this, 'processRestriction'], $row->text);
	}

	private function processRestriction($matches)
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$wa = Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->registerAndUseStyle('com_osmembership.style', 'media/com_osmembership/assets/css/style.css');

		$customCssFile = JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css';

		if (file_exists($customCssFile) && filesize($customCssFile) > 0)
		{
			$wa->registerAndUseStyle('com_osmembership.custom', 'media/com_osmembership/assets/css/custom.css');
		}

		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if (strlen($message->{'content_restricted_message' . $fieldSuffix}))
		{
			$restrictedText = $message->{'content_restricted_message' . $fieldSuffix};
		}
		else
		{
			$restrictedText = $message->content_restricted_message;
		}

		$requiredPlanIds = $matches[1];
		$protectedText   = $matches[2];

		// Super admin should see all text
		$user = $this->app->getIdentity();
		$db   = $this->db;

		if ($user->authorise('core.admin', 'com_osmembership'))
		{
			return $protectedText;
		}

		$activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans();

		if (str_starts_with($requiredPlanIds, '!'))
		{
			$requiredPlanIds = substr($requiredPlanIds, 1);

			if ($requiredPlanIds == '*')
			{
				if (count($activePlanIds) == 1 && $activePlanIds[0] == 0)
				{
					return $protectedText;
				}
			}
			else
			{
				$requiredPlanIds = explode(',', $requiredPlanIds);

				if (count(array_intersect($requiredPlanIds, $activePlanIds)) == 0)
				{
					return $protectedText;
				}

				return '';
			}
		}
		else
		{
			if ($requiredPlanIds == '*')
			{
				$query = $db->getQuery(true)
					->select('id')
					->from('#__osmembership_plans')
					->where('published = 1')
					->order('ordering');
				$db->setQuery($query);
				$planIds = $db->loadColumn();
			}
			else
			{
				$planIds = explode(',', $requiredPlanIds);
			}

			$redirectUrl = OSMembershipHelper::callOverridableHelperMethod('Helper', 'getPluginRestrictionRedirectUrl', [$this->params, $planIds]);

			// Store URL of this page to redirect user back after user logged in if they have active subscription of this plan
			$session = $this->app->getSession();
			$session->set('osm_return_url', Uri::getInstance()->toString());
			$session->set('required_plan_ids', $planIds);

			$query = $db->getQuery(true)
				->select('title')
				->from('#__osmembership_plans')
				->whereIn('id', $planIds);
			$db->setQuery($query);
			$planTitles = implode(', ', $db->loadColumn());

			$restrictedText = str_replace('[SUBSCRIPTION_URL]', $redirectUrl, $restrictedText);
			$restrictedText = str_replace('[PLAN_TITLES]', $planTitles, $restrictedText);

			$restrictedText = HTMLHelper::_('content.prepare', $restrictedText);

			if (count($activePlanIds) == 1 && $activePlanIds[0] == 0)
			{
				return '<div id="restricted_info">' . $restrictedText . '</div>';
			}
			elseif ($requiredPlanIds == '*')
			{
				return $protectedText;
			}
			$requiredPlanIds = explode(',', $requiredPlanIds);

			if (count(array_intersect($requiredPlanIds, $activePlanIds)))
			{
				return $protectedText;
			}

			return '<div id="restricted_info">' . $restrictedText . '</div>';
		}
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
