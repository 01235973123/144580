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

class OSMembershipViewFieldRaw extends MPFViewHtml
{
	/**
	 * Options from the field
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Prepare view data and render it
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display()
	{
		$this->setLayout('options');
		$fieldId = Factory::getApplication()->getInput()->getInt('field_id');
		$db      = $this->model->getDbo();
		$query   = $db->getQuery(true)
			->select('`values`')
			->from('#__osmembership_fields')
			->where('id=' . $fieldId);
		$db->setQuery($query);
		$options       = explode("\r\n", $db->loadResult());
		$this->options = $options;

		parent::display();
	}
}
