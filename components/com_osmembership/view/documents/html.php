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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\PluginHelper;

class OSMembershipViewDocumentsHtml extends MPFViewHtml
{
	/**
	 * The list of documents
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * Path to the folder which document stored
	 *
	 * @var string
	 */
	protected $documentsPath;

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		if (!PluginHelper::isEnabled('osmembership', 'documents'))
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('Memebership Pro Documents plugin is not enabled. Please contact super administrator')
			);

			return;
		}

		// Make sure users are logged in before allow them to access
		$this->requestLogin();

		/* @var $model OSMembershipModelDocuments */
		$model               = $this->getModel();
		$this->items         = $model->getData();
		$this->pagination    = $model->getPagination();
		$this->documentsPath = OSMembershipHelper::getDocumentsPath();
	}
}
