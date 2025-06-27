<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class OsMembershipViewMessageHtml extends MPFViewHtml
{
	/**
	 * Extra offline payment plugins
	 *
	 * @var array
	 */
	protected $extraOfflinePlugins;

	/**
	 * The message item data
	 *
	 * @var stdClass
	 */
	protected $item;

	/**
	 * Languages use on the site exclude default language
	 *
	 * @var array
	 */
	protected $languages;

	/**
	 * Set data and render the view
	 *
	 * @return void
	 */
	public function display()
	{
		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_plugins')
			->where('name LIKE "os_offline_%"');
		$db->setQuery($query);

		$this->extraOfflinePlugins = $db->loadObjectList();
		$this->item                = OSMembershipHelper::getMessages();
		$this->languages           = OSMembershipHelper::getLanguages();

		parent::display();
	}
}
