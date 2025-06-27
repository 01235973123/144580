<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelQuestion extends EShopModel
{

	public function __construct($config)
	{
		parent::__construct($config);
	}

	/**
	 * Method to remove quotes
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->delete('#__eshop_questions')
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
		}

		//Removed success
		return 1;
	}
}