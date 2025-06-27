<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Table\Table;

class DonationTableCategory extends Table
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 *
	 */
	function __construct($db)
	{
		parent::__construct('#__jd_categories', 'id', $db);
	}

}
