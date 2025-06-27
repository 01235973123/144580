<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class DonationModelPlugins extends OSFModelList
{

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct($config = array())
	{
		$config['table'] = '#__jd_payment_plugins';
		parent::__construct($config);
	}
}