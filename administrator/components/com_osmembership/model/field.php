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
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

class OSMembershipModelField extends MPFModelAdmin
{
	/**
	 * Method to store a custom field
	 *
	 * @param   MPFInput  $input
	 * @param   array     $ignore
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function store($input, $ignore = [])
	{
		$id = $input->getInt('id');

		if ($id)
		{
			$row = $this->getTable();
			$row->load($id);

			if ($row->is_core)
			{
				$ignore = ['name', 'fee_field'];
			}
		}

		$input->set('depend_on_options', json_encode($input->get('depend_on_options', [], 'array')));

		parent::store($input, $ignore);
	}

	/**
	 * Store custom fields mapping with plans.
	 *
	 * @param   Table     $row
	 * @param   MPFInput  $input
	 * @param   bool      $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$planIds    = $input->get('plan_id', [], 'array');
		$assignment = $input->getInt('assignment', 0);

		// Email field need to be assigned to all plans
		if ($assignment == 0 || $row->name == 'email')
		{
			$row->plan_id = 0;
		}
		else
		{
			$row->plan_id = 1;
		}

		if (in_array($row->name, ['first_name', 'email']))
		{
			$row->show_on_subscription_form = 1;
		}

		$row->store(); // Store the plan_id field

		if (!$isNew)
		{
			$query->delete('#__osmembership_field_plan')
				->where('field_id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}

		$planIds = array_filter(ArrayHelper::toInteger($planIds));

		if ($row->plan_id != 0 && count($planIds))
		{
			$query->clear()
				->insert('#__osmembership_field_plan')
				->columns('field_id, plan_id');

			foreach ($planIds as $planId)
			{
				$planId = $assignment * $planId;
				$query->values("$row->id, $planId");
			}

			$db->setQuery($query)
				->execute();
		}

		// Calculate depend on options in different languages
		if (Multilanguage::isEnabled())
		{
			$languages = OSMembershipHelper::getLanguages();

			if (count($languages) && $row->depend_on_field_id > 0)
			{
				$masterField = $this->getTable();
				$masterField->load($row->depend_on_field_id);
				$masterFieldValues = explode("\r\n", $masterField->values);
				$dependOnOptions   = json_decode($row->depend_on_options);
				$dependOnIndexes   = [];

				foreach ($dependOnOptions as $option)
				{
					$index = array_search($option, $masterFieldValues);

					if ($index !== false)
					{
						$dependOnIndexes[] = $index;
					}
				}

				foreach ($languages as $language)
				{
					$sef                             = $language->sef;
					$dependOnOptionsWithThisLanguage = [];
					$values                          = explode("\r\n", $masterField->{'values_' . $sef});

					foreach ($dependOnIndexes as $index)
					{
						if (isset($values[$index]))
						{
							$dependOnOptionsWithThisLanguage[] = $values[$index];
						}
					}

					$row->{'depend_on_options_' . $sef} = json_encode($dependOnOptionsWithThisLanguage);
				}

				$row->store();
			}
		}
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array  $pks    A list of the primary keys to change.
	 * @param   int    $value  The value of the published state.
	 *
	 * @throws Exception
	 */
	public function publish($pks, $value = 1)
	{
		$restrictedFieldIds = $this->getRestrictedFieldIds();
		$pks                = array_diff($pks, $restrictedFieldIds);

		if (count($pks))
		{
			parent::publish($pks, $value);
		}
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $cid
	 *
	 * @throws Exception
	 */
	public function delete($cid = [])
	{
		if (!count($cid))
		{
			return;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->delete('#__osmembership_fields')
			->where('is_core = 0')
			->whereIn('id', $cid);
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->delete('#__osmembership_field_value')
			->whereIn('field_id', $cid);
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Get Ids of restricted fields which cannot be changed status, ddeleted...
	 *
	 * @return array
	 */
	private function getRestrictedFieldIds()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__osmembership_fields')
			->where('name IN ("email")');
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Initialize custom field data
	 */
	protected function initData()
	{
		parent::initData();

		$this->data->can_edit_on_profile                 = 1;
		$this->data->show_on_user_profile                = 1;
		$this->data->show_on_subscription_form           = 1;
		$this->data->populate_from_previous_subscription = 1;
		$this->data->taxable                             = 1;
	}
}
