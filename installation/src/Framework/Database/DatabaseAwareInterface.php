<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

defined('_AKEEBA') or die();

interface DatabaseAwareInterface
{
	/**
	 * Return the database driver object
	 *
	 * @return  AbstractDriver
	 */
	public function getDbo(): AbstractDriver;
}