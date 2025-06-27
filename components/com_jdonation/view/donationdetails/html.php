<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class DonationViewDonationdetailsHtml extends OSFViewHtml
{
	function display()
	{
        $app                    = Factory::getApplication();
        $db                     = Factory::getContainer()->get('db');
        $document               = Factory::getApplication()->getDocument();
        $user                   = Factory::getApplication()->getIdentity();
        $userId                 = $user->id;
        $config                 = DonationHelper::getConfig();
        $model                  = $this->getModel();
        $state                  = $model->getState();
        $id                     = $state->id;
        if (!DonationHelper::canAccessDonation($id))
        {
            if (!$userId)
            {
                $app->enqueueMessage(Text::_('JD_LOGIN_TO_ACCESS'));
                $app->redirect('index.php?option=com_users&view=login');
            }
            else
            {
                $url = Route::_('index.php?option=com_jdonation&Itemid=' . $this->Itemid);
                $app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'), 'error');
                $app->redirect($url);
            }
        }

        $document->setTitle(Text::_('JD_DONATION').' #'.$id);
        // Make sure a valid document ID is passed in URL
        $item                   = $model->getData();
        $this->item             = $item;

        $rowFields              = DonationHelper::getFormFields($item->language);
        $formData               = DonationHelper::getDonationData($item, $rowFields, true, false);
        $form                   = new OSFForm($rowFields);
        $form->bind($formData)->prepareFormField($item->campaign_id);
        $this->form             = $form;
        $this->config           = $config;
        $this->userId           = $userId;
        $this->campaign_id      = Factory::getApplication()->input->getInt('campaign_id',0);
        $this->bootstrapHelper  = new DonationHelperBootstrap($config->twitter_bootstrap_version);
        parent::display();
	}
}
