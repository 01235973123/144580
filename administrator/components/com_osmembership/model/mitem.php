<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;

class OSMembershipModelMitem extends MPFModelAdmin
{
	/**
	 * Override store method to store data into #__osmembership_mitems table
	 *
	 * @param   MPFInput  $input
	 * @param   array     $ignore
	 *
	 * @return bool|void
	 * @throws Exception
	 */
	public function store($input, $ignore = [])
	{
		$id    = $input->getInt('id', 0);
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_mitems')
			->where('id = ' . $id);
		$db->setQuery($query);
		$row = $db->loadObject();

		if (!$row)
		{
			throw new Exception('Invalid Message Item', 404);
		}

		$data = [];

		$data[$row->name] = $input->get($row->name, '', 'raw');

		if ($row->translatable && Multilanguage::isEnabled())
		{
			foreach (OSMembershipHelper::getLanguages() as $language)
			{
				$inputName        = $row->name . '_' . $language->sef;
				$data[$inputName] = $input->get($inputName, '', 'raw');
			}
		}

		$this->insertOrUpdateMessages($data);
	}

	/**
	 * Method to insert or update messages
	 *
	 * @param   array  $data
	 *
	 * @return void
	 */
	private function insertOrUpdateMessages($data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('message_key')
			->from('#__osmembership_messages');
		$db->setQuery($query);
		$existingMessages = $db->loadColumn();

		foreach ($data as $messageKey => $message)
		{
			if (in_array($messageKey, $existingMessages))
			{
				// Update
				$query->clear()
					->update('#__osmembership_messages')
					->set('message = ' . $db->quote($message))
					->where('message_key = ' . $db->quote($messageKey));
			}
			else
			{
				// Insert
				$query->clear()
					->insert('#__osmembership_messages')
					->columns($db->quoteName(['message_key', 'message']))
					->values(implode(',', $db->quote([$messageKey, $message])));
			}

			$db->setQuery($query)
				->execute();
		}
	}
}
