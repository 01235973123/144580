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
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseQuery;

class OSMembershipModelDownloadids extends MPFModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->set('filter_order', 'tbl.id');
		$this->state->set('filter_order_Dir', 'DESC');
	}

	/**
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		$query->where('tbl.user_id = ' . Factory::getApplication()->getIdentity()->id);

		return $this;
	}

	/**
	 * Generate download Ids for the current user
	 *
	 * @param   int  $numberIds
	 */
	public function generateDownloadIds($numberIds = 1)
	{
		$user        = Factory::getApplication()->getIdentity();
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$createdDate = Factory::getDate('now')->toSql();

		$columns = [
			'user_id',
			'download_id',
			'created_date',
			'published',
		];

		$query->insert('#__osmembership_downloadids')
			->columns($db->quoteName($columns));

		for ($i = 0; $i < $numberIds; $i++)
		{
			$downloadId = strtoupper(
				md5(
					$user->username . Factory::getApplication()->get('secret') . UserHelper::genRandomPassword(
						10
					) . time()
				)
			);
			$values     = [$user->id, $db->quote($downloadId), $db->quote($createdDate), 1];
			$query->values(implode(',', $values));
		}

		$db->setQuery($query);
		$db->execute();
	}
}
