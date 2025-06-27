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
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
class DonationModelCampaign extends OSFModelAdmin
{
    function store($input, $ignore = []){
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $row           = $this->getTable();
        $id            = $input->getInt('id', 0);
        $delete        = $input->getInt('remove_photo',0);
        $payment_methods = $input->get('payment_methods',[], 'ARRAY');
        if($id > 0)
        {
            $row->load((int)$id);
        }
        $campaign_photo = $input->getString('campaign_photo','');
		$campaign_photo = DonationHelperHtml::getCleanImagePath($campaign_photo);
		$input->set('campaign_photo',$campaign_photo);
        $remove_owner	= $input->getInt('remove_owner',0);
        if($remove_owner == 1)
        {
            $input->set('user_id',0);
        }
        if(count($payment_methods) > 0)
        {
            $payment_methods = implode(",", $payment_methods);
            $input->set('payment_plugins', $payment_methods);
        }
        $recurring_frequencies = $input->get('recurring_frequencies', [],'array');
        if(count($recurring_frequencies))
        {
            $recurring_frequencies = implode(",", $recurring_frequencies);
            $input->set('recurring_frequencies', $recurring_frequencies);
        }
		else
		{
			$input->set('recurring_frequencies', '');
		}

		$category = $input->getInt('category_id',0);
		$input->set('category_id', $category);

		$description = $_POST['description'];
		$input->set('description', $description);
        parent::store($input,$ignore);
    }
	/**
	 * Load campaign data from database
	 *
	 * @see OSFModelAdmin::loadData()
	 */
	protected function loadData()
	{
		parent::loadData();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(amount)')
			->from('#__jd_donors')
			->where('campaign_id=' . $this->state->id)
			->where('published = 1');
		$db->setQuery($query);

		$this->data->donated_amount = floatval($db->loadResult());

        $query->clear()->select('count(id)')
			->from('#__jd_donors')
			->where('campaign_id=' . $this->state->id)
			->where('published = 1');
		$db->setQuery($query);

        $this->data->number_donations = intval($db->loadResult());
	}
}
