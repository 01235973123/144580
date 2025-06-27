<?php

use Joomla\Database\DatabaseQuery;
use Joomla\String\StringHelper;

trait EventbookingModelEventsfilter
{
	/**
	 * Method to apply keyword filter, make it easier to customize keyword search behavior
	 *
	 * @param   DatabaseQuery  $query
	 */
	protected function applyKeywordFilter(DatabaseQuery $query)
	{
		if (!$this->state->search)
		{
			return;
		}

		$db          = $this->getDbo();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$searchFields = [];

		foreach ($this->searchFields as $field)
		{
			if (in_array($field, static::$translatableFields))
			{
				$searchFields[] = $field . $fieldSuffix;
			}
			else
			{
				$searchFields[] = $field;
			}
		}

		$searchFields = $db->quoteName($searchFields);

		if ($config->get('search_events', 'exact') == 'exact')
		{
			$search = $db->quote('%' . $db->escape(StringHelper::strtolower($this->state->search), true) . '%', false);

			$whereOr = [];

			foreach ($searchFields as $searchField)
			{
				$whereOr[] = " LOWER($searchField) LIKE " . $search;
			}

			$query->where('(' . implode(' OR ', $whereOr) . ') ');
		}
		else
		{
			$words = explode(' ', $this->state->search);

			$wheres = [];

			foreach ($words as $word)
			{
				$word = $db->quote('%' . $db->escape(StringHelper::strtolower($word), true) . '%', false);

				foreach ($searchFields as $searchField)
				{
					$wheres[] = " LOWER($searchField) LIKE " . $word;
				}
			}

			$query->where('(' . implode(' OR ', $wheres) . ')');
		}
	}
}