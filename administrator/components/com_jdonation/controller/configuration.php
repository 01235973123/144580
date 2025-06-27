<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
class DonationControllerConfiguration extends DonationController
{

	public function save()
	{
		jimport('joomla.filesystem.file');
        $jinput                 = Factory::getApplication()->input;
		$model                  = $this->getModel('Configuration', array('ignore_request' => true));
        $mailchimp_list_ids     = $jinput->get('mailchimp_list_ids', array(),'array');
        if(count($mailchimp_list_ids) > 0){
            $mailchimp_list_ids = implode(",",$mailchimp_list_ids);
            $this->input->set('mailchimp_list_ids', $mailchimp_list_ids);
        }
		$model->store($this->input->getData());
		$custom_css = $jinput->get('custom_css','','string');
		if ($custom_css != "")
		{
			File::write(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css', trim($custom_css));
		}
        $db                     = Factory::getContainer()->get('db');
		$db->setQuery("Delete from #__jd_urls");
		$db->execute();
        $task = $this->getTask();

        if ($task == 'save')
        {
            $this->setRedirect('index.php?option=com_jdonation&view=dashboard', Text::_('JD_CONFIGURATION_DATA_HAVE_BEEN_SAVED_SUCCESSFULLY'));
        }
        else
        {
            $this->setRedirect('index.php?option=com_jdonation&view=configuration', Text::_('JD_CONFIGURATION_DATA_HAVE_BEEN_SAVED_SUCCESSFULLY'));
        }
	}


	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jdonation&view=dashboard');
	}
} 
