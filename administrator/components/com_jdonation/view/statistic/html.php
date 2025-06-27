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
defined('_JEXEC') or die ();
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;


class DonationViewStatisticHtml extends OSFViewHtml
{
	public $hasModel = false;

	function display()
	{
		$config				= DonationHelper::getConfig();
	    $campaignId         = Factory::getApplication()->input->getInt('campaignId',0);
	    $payment_method     = Factory::getApplication()->input->getString('payment_method','');
	    $time_period        = Factory::getApplication()->input->getString('time_period','current_month');


        $model = OSFModel::getInstance('Campaigns', 'DonationModel', array('ignore_request' => true, 'remember_states' => false, 'table_prefix' => '#__jd_'));
        $model->filter_order('tbl.title')
            ->filter_order_Dir('asc')
            ->limitstart(0)
            ->limit(0);
        $this->campaigns = $model->getData();

        $this->donated_by_countries = DonationModelDonors::returnDonorsBasedOnCountry();

		$this->config       = DonationHelper::getConfig();
        $this->addToolbar();

        $db = Factory::getContainer()->get('db');
        $query = $db->getQuery(true);
        $options = [];
        $options [] = HTMLHelper::_ ( 'select.option', 0, Text::_ ( 'JD_SELECT_CAMPAIGN' ), 'id', 'title' );
        $query->select('id, title')
            ->from('#__jd_campaigns')
            ->where('published = 1')
            ->order('title');
        $db->setQuery ($query);
        $options = array_merge ( $options, $db->loadObjectList () );
        $this->lists['campaigns'] = HTMLHelper::_ ( 'select.genericlist', $options, 'campaignId', ' class="input-large form-select" onChange="javascript:document.adminForm.submit();" style="display:inline;"', 'id', 'title', $campaignId );

        $query->clear();
        $options = [];
        $options [] = HTMLHelper::_ ( 'select.option', '', Text::_ ( 'JD_SELECT_PAYMENT_OPTIONS' ), 'name', 'title' );
        $query->select('name, title')
            ->from('#__jd_payment_plugins')
            ->where('published = 1')
            ->order('title');
        $db->setQuery ($query);
        $options = array_merge ( $options, $db->loadObjectList () );
        $this->lists['payment_method'] = HTMLHelper::_ ( 'select.genericlist', $options, 'payment_method', ' class="input-large form-select" onChange="javascript:document.adminForm.submit();" style="display:inline;"', 'name', 'title', $payment_method );

        $options = [];
        $options [] = HTMLHelper::_ ( 'select.option', 0, Text::_ ( 'JD_SELECT_TIME_PERIOD' ), 'value', 'text' );
        $options [] = HTMLHelper::_('select.option', 'this_week', Text::_('JD_THIS_WEEK'));
        $options [] = HTMLHelper::_('select.option', 'current_month', Text::_('JD_THIS_MONTH'));
        $options [] = HTMLHelper::_('select.option', 'last_month', Text::_('JD_LAST_MONTH'));
        $options [] = HTMLHelper::_('select.option', 'this_year', Text::_('JD_THIS_YEAR'));
        $options [] = HTMLHelper::_('select.option', 'last_year', Text::_('JD_LAST_YEAR'));
        $this->lists['time_period'] = HTMLHelper::_ ( 'select.genericlist', $options, 'time_period', ' class="input-large form-select" onChange="javascript:document.adminForm.submit();" style="display:inline;"', 'value', 'text', $time_period );

        $this->campaignId			= $campaignId;
        $this->payment_method		= $payment_method;
        $this->time_period			= $time_period;
		$this->bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
		// Render sub-menu in dashboard
		if(Factory::getApplication()->input->getString('tmpl','') != "component")
		{
			DonationHelperHtml::renderSubmenu('statistic');
		}
		parent::display();
	}

    /**
     * Add toolbar to the view
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('JD_STATISTIC'), 'generic.png');
        $canDo = DonationHelper::getActions();
        ToolbarHelper::cancel('gotojddasboard');
        if ($canDo->get('core.admin'))
        {
            ToolbarHelper::preferences('com_jdonation');
        }
    }
}
