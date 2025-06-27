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
class EShopModelQuote extends EShopModel
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
			$query->delete('#__eshop_quotes')
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			$numItemsDeleted = $db->getAffectedRows();
			
			//Delete quote products
			$query->clear();
			$query->delete('#__eshop_quoteproducts')
				->where('quote_id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			
			//Delete quote totals
			$query->clear();
			$query->delete('#__eshop_quotetotals')
				->where('quote_id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
			//Removed error
			{
				return 0;
			}
			
			//Delete quote options
			$query->clear();
			$query->delete('#__eshop_quoteoptions')
				->where('quote_id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			if ($numItemsDeleted < count($cid))
			{
				//Removed warning
				return 2;
			}
		}

		//Removed success
		return 1;
	}

	/**
	 *
	 * Function to download file
	 *
	 * @param   int  $id
	 */
	public function downloadFile($id, $download = true)
	{
		$app   = Factory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('option_value')
			->from('#__eshop_quoteoptions')
			->where('id = ' . intval($id));
		$db->setQuery($query);
		$filename = $db->loadResult();
		while (@ob_end_clean())
		{
		}
		EShopHelper::processDownload(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $filename, $download);
		$app->close(0);
	}
}