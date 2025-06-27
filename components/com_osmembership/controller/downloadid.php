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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;

class OSMembershipControllerDownloadId extends OSMembershipController
{
	/**
	 * Generate Download IDs for user
	 *
	 * @throws Exception
	 */
	public function generate_download_ids()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		if (!$this->app->getIdentity()->id)
		{
			throw new Exception('OSM_GUEST_COULD_NOT_GENERATE_DOWNLOAD_IDS', 403);
		}

		$numberDownloadIds = $this->input->post->getInt('number_download_ids', 1);

		if (!$numberDownloadIds)
		{
			$numberDownloadIds = 1;
		}

		/* @var OSMembershipModelDownloadids $model */
		$model = $this->getModel('Downloadids');
		$model->generateDownloadIds($numberDownloadIds);

		$this->setRedirect(
			Route::_('index.php?option=com_osmembership&view=downloadids&Itemid=' . $this->input->getInt('Itemid')),
			Text::sprintf('OSM_COUNT_DOWNLOAD_ID_GENERATED', $numberDownloadIds)
		);
	}

	/**
	 * Delete Download IDs
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function delete()
	{
		$user = $this->app->getIdentity();

		if (!$user->id)
		{
			throw new Exception('You do not have permission to delete Download IDs', 403);
		}

		$cid = ArrayHelper::toInteger($this->input->get('cid', [], 'int'));

		if (count($cid) === 0)
		{
			throw new Exception('You need to select at least one Download ID to delette', 403);
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->delete('#__osmembership_downloadids')
			->where('user_id = ' . $user->id)
			->whereIn('id', $cid);
		$db->setQuery($query)
			->execute();

		$this->setRedirect(
			Route::_('index.php?option=com_osmembership&view=downloadids&Itemid=' . $this->input->getInt('Itemid')),
			Text::_('OSM_DOWNLOAD_IDS_DELETED')
		);
	}
}
