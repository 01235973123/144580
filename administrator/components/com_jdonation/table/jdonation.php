<?php
use Joomla\CMS\Table\Table;
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class TableJdonation extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 *            object Database connector object
	 *
	 * @since 1.5
	 */
	function __construct(& $db)
	{
		parent::__construct('#__jd_donors', 'id', $db);
	}
}
