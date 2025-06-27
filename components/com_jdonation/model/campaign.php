<?php
/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;

class DonationModelCampaign extends OSFModelAdmin
{
    function __construct($config = array())
    {
        //require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/table/campaign.php';
        parent::__construct($config);
        $this->state->insert('id', 'int', 0)->insert('tmpl', 'cmd', '');
    }

    /**
     * Load campaign data from database
     *
     * @see OSFModelAdmin::loadData()
     */
    function loadData()
    {
        parent::loadData();
        $db                         = $this->getDbo();
        $query                      = $db->getQuery(true);
        $query->select('SUM(amount)')
            ->from('#__jd_donors')
            ->where('campaign_id=' . $this->state->id)
            ->where('published = 1');
        $db->setQuery($query);
        $this->data->donated_amount = floatval($db->loadResult());
    }

    function store($input, $ignore = [])
	{
        
        $row           = $this->getTable();
        $id            = $input->getInt('id', 0);
        $delete        = $input->getInt('remove_photo',0);
        $user          = Factory::getApplication()->getIdentity();
        if($user->id == 0)
        {
            throw new Exception (Text::_('JD_NOT_ALLOWED_ACTION'));
        }
		$ret                    = Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jdonation');
		$ret1                   = Factory::getApplication()->getIdentity()->authorise('managecampaigns', 'com_jdonation');
		if(! $ret && ! $ret1)
        {
            throw new Exception (Text::_('JD_NOT_ALLOWED_ACTION'));
        }
        if($id > 0)
        {
            $row->load((int)$id);
        }
        if(is_uploaded_file($_FILES['photo']['tmp_name']))
        {
            $filename  = $_FILES['photo']['name'];
            $filename  = time() . str_replace(" ", "_", $filename);
            if (!is_dir(Path::clean(JPATH_ROOT . '/images/jdonation')))
            {
                Folder::create(JPATH_ROOT . '/images/jdonation');
            }
            File::upload($_FILES['photo']['tmp_name'], JPATH_ROOT . '/images/jdonation/' . $filename);
            $row->campaign_photo = 'images/jdonation/'.$filename;
            $input->set('campaign_photo',$filename);
        }
        elseif($delete == 1)
        {
            $filename = "";
            $input->set('campaign_photo',$filename);
            if(($row->campaign_photo != "") && (is_file(Path::clean(JPATH_ROOT.'/images/jdonation/'.$row->campaign_photo))))
            {
                File::delete(JPATH_ROOT.'/images/jdonation/'.$row->campaign_photo);
            }
        }
        $input->set('user_id', $user->id);
        parent::store($input,$ignore);
    }
}
