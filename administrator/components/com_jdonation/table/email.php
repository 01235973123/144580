<?php

use Joomla\CMS\Table\Table;
/**
 * Email table
 */
class DonationTableEmail extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__jd_emails', 'id', $db);
	}
}
