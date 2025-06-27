<?php
/**
 * @version        1.7.6
 * @package        Joomla
 * @subpackage     EDocman
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2011 - 2018 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class DonationModelSearch extends OSFModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */

	public function __construct($config = array())
	{
        $config['remember_states'] = false;
        $config['table'] = '#__jd_campaigns';
		parent::__construct($config);
		$this->state->insert('filter_search', 'string', '');
	}
}