<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class OSMembershipViewUserfilesHtml extends MPFViewHtml
{
	/**
	 * Path to the folder stores user's files
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Files which are available for user
	 *
	 * @var array
	 */
	protected $files;

	/**
	 * Preview view data before rendering
	 *
	 * @throws Exception
	 */
	public function prepareView()
	{
		$this->requestLogin();

		[$this->path, $this->files] = $this->model->getData();

		parent::prepareView();
	}
}
