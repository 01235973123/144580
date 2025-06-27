<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Database\DatabaseQuery;

class OSMembershipModelMitems extends MPFModelList
{
	protected $choicesStates = ['filter_group'];

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['search_fields'] = ['tbl.name', 'tbl.title', 'tbl.title_en'];

		parent::__construct($config);

		$this->state->insert('filter_group', 'int', 0);
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return MPFModelList
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		if ($this->state->filter_group)
		{
			$query->where('tbl.group = ' . $this->state->filter_group);
		}

		return parent::buildQueryWhere($query);
	}

	/**
	 * Apply search filter
	 *
	 * @param   DatabaseQuery  $query
	 */
	protected function applySearchFilter(DatabaseQuery $query)
	{
		$state = $this->state;

		if (stripos($state->filter_search, 'id:') === 0)
		{
			$query->where('tbl.id = ' . (int) substr($state->filter_search, 3));
		}
		else
		{
			$db     = $this->getDbo();
			$search = $db->quote('%' . $db->escape($state->filter_search, true) . '%', false);

			if (is_array($this->searchFields))
			{
				$whereOr = [];

				foreach ($this->searchFields as $searchField)
				{
					$whereOr[] = "LOWER($searchField) LIKE " . $search;
				}

				$whereOr[] = 'tbl.name IN (SELECT message_key FROM #__osmembership_messages WHERE message LIKE ' . $search . ')';

				$query->where('(' . implode(' OR ', $whereOr) . ') ');
			}
		}
	}

	/**
	 * Override buildQueryOrder method to have featured items displayed first
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return MPFModelList
	 */
	protected function buildQueryOrder(DatabaseQuery $query)
	{
		$query->order('tbl.featured DESC');

		return parent::buildQueryOrder($query);
	}

	/**
	 * Insert necessary messages for additional offline payment plugins
	 *
	 * @return void
	 */
	public function insertAdditionalOfflinePaymentMessages()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_plugins')
			->where('name LIKE "os_offline_%"')
			->where('published = 1');
		$db->setQuery($query);

		$extraOfflinePlugins = $db->loadObjectList();

		$query->clear()
			->select('name')
			->from('#__osmembership_mitems');
		$db->setQuery($query);
		$existingMessages = $db->loadColumn();

		$offlinePaymentMessageItems = [
			['name' => 'user_email_body_offline', 'title' => 'User Email Body', 'group' => 1],
			['name' => 'thanks_message_offline', 'title' => 'Thank You Message', 'group' => 1],
			['name'  => 'user_renew_email_body_offline',
			 'title' => 'Subscription Renewal User Email Body',
			 'group' => 2
			],
			['name' => 'renew_thanks_message_offline', 'title' => 'Subscription Renewal Thanks Message', 'group' => 2],
			['name'  => 'user_upgrade_email_body_offline',
			 'title' => 'Subscription Upgrade User Email Body',
			 'group' => 3
			],
			['name'  => 'upgrade_thanks_message_offline',
			 'title' => 'Subscription Upgrade Thanks Message',
			 'group' => 3
			],
		];

		foreach ($extraOfflinePlugins as $offlinePaymentPlugin)
		{
			$name   = $offlinePaymentPlugin->name;
			$title  = $offlinePaymentPlugin->title;
			$prefix = str_replace('os_offline', '', $name);

			foreach ($offlinePaymentMessageItems as $offlinePaymentMessageItem)
			{
				$messageKey = $offlinePaymentMessageItem['name'] . $prefix;

				if (!in_array($messageKey, $existingMessages))
				{
					$item               = new stdClass();
					$item->name         = $messageKey;
					$item->title        = $offlinePaymentMessageItem['title'] . ' (' . $title . ')';
					$item->title_en     = $offlinePaymentMessageItem['title'] . ' (' . $title . ')';
					$item->type         = 'editor';
					$item->group        = $offlinePaymentMessageItem['group'];
					$item->translatable = 1;
					$item->featured     = 0;

					$db->insertObject('#__osmembership_mitems', $item);
				}
			}
		}
	}
}
