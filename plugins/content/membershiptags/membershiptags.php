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
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgContentMembershipTags extends CMSPlugin implements SubscriberInterface
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
	 * Parse and display membership tags in the article
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onContentPrepare(Event $event): void
	{
		[$context, $article, $params, $limitstart] = array_values($event->getArguments());

		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$config = OSMembershipHelper::getConfig();
		$user   = $this->app->getIdentity();
		$item   = OSMembershipHelperSubscription::getMembershipProfile($user->id);

		if ($item && OSMembershipHelper::isUniquePlan($item->user_id))
		{
			$planId = $item->plan_id;
		}
		else
		{
			$planId = 0;
		}

		// Form
		$rowFields = OSMembershipHelper::getProfileFields($planId);

		if ($item)
		{
			$data = OSMembershipHelper::getProfileData($item, $planId, $rowFields);
		}
		else
		{
			$data = [];
		}

		$replaces = [];

		foreach ($rowFields as $rowField)
		{
			if (isset($data[$rowField->name]))
			{
				$value = $data[$rowField->name];

				if ($rowField->fieldtype == 'Url' && filter_var($value, FILTER_VALIDATE_URL))
				{
					$value = '<a href="' . $value . '">' . $value . '</a>';
				}
				elseif ($rowField->fieldtype == 'Date')
				{
					try
					{
						$date  = Factory::getDate($value);
						$value = $date->format($config->date_format);
					}
					catch (Exception $e)
					{
					}
				}

				$replaces[$rowField->name] = $value;
			}
			else
			{
				$replaces[$rowField->name] = '';
			}
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		if ($item)
		{
			$replaces['membership_id'] = OSMembershipHelper::formatMembershipId($item, $config);
			$replaces['created_date']  = HTMLHelper::_('date', $item->created_date, $config->date_format);

			$query->select('username')
				->from('#__users')
				->where('id = ' . (int) $item->user_id);
			$db->setQuery($query);
			$replaces['username'] = $db->loadResult();
		}
		else
		{
			$replaces['membership_id'] = '';
			$replaces['created_date']  = '';
			$replaces['username']      = '';
		}

		// Move information about user
		if ($user->id)
		{
			$replaces['name'] = $user->name;

			$query->clear()
				->select('a.title')
				->from('#__usergroups AS a')
				->innerJoin('#__user_usergroup_map AS b ON a.id = b.group_id')
				->where('b.user_id = ' . $user->id);
			$db->setQuery($query);
			$replaces['user_groups'] = implode(', ', $db->loadColumn());
		}
		else
		{
			$replaces['name']        = '';
			$replaces['user_groups'] = 'Guest';
		}

		// Get active plans
		$query->clear()
			->select('DISTINCT a.title')
			->from('#__osmembership_plans AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.id = b.plan_id')
			->where('b.user_id = ' . $user->id)
			->where('b.published = 1');

		$db->setQuery($query);

		$replaces['active_plans'] = implode(', ', $db->loadColumn());

		// Get expired plans
		$query->clear()
			->select('DISTINCT a.title')
			->from('#__osmembership_plans AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.id = b.plan_id')
			->where('b.user_id = ' . $user->id)
			->where('b.published = 2')
			->where('a.id NOT IN (SELECT plan_id FROM #__osmembership_subscribers AS c WHERE c.published = 1)');
		$db->setQuery($query);
		$replaces['expired_plans'] = implode(', ', $db->loadColumn());

		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);

			if (is_string($value) && is_array(json_decode($value)))
			{
				$value = implode(', ', json_decode($value));
			}
			else
			{
				$value = (string) $value;
			}

			$article->text = str_replace("[$key]", $value, $article->text);
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
