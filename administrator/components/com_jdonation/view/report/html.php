<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

class DonationViewReportHtml extends OSFViewHtml
{
	public $hasModel = false;

	function display()
	{	
		$config		= DonationHelper::getConfig();

		$current_month = date("n");
		$current_year  = date("Y");
		$optionArr	   = [];
		for($i = $current_year; $i >= $current_year - 5; $i--)
		{	
			$optionArr[] = HTMLHelper::_('select.option', $i, $i);
		}
		
		$this->lists['year1'] = HTMLHelper::_('select.genericlist', $optionArr, 'from_year', 'class="form-select input-small ismall"','value','text', $current_year);

		$this->lists['year2'] = HTMLHelper::_('select.genericlist', $optionArr, 'to_year', 'class="form-select input-small ismall"','value','text', $current_year);
		
		$optionArr	   = [];
		$monthArr	   = [Text::_('JANUARY_SHORT'), Text::_('FEBRUARY_SHORT'),Text::_('MARCH_SHORT'),Text::_('APRIL_SHORT'),Text::_('MAY_SHORT'),Text::_('JUNE_SHORT'),Text::_('JULY_SHORT'),Text::_('AUGUST_SHORT'),Text::_('SEPTEMBER_SHORT'),Text::_('OCTOBER_SHORT'),Text::_('NOVEMBER_SHORT'),Text::_('DECEMBER_SHORT')];
		for($i = 1; $i <= 12; $i++)
		{	
			$optionArr[] = HTMLHelper::_('select.option', $i, $monthArr[$i-1]);
		}

		$this->lists['month1'] = HTMLHelper::_('select.genericlist', $optionArr, 'from_month', 'class="form-select input-small ismall"','value','text', $current_month);

		$this->lists['month2'] = HTMLHelper::_('select.genericlist', $optionArr, 'to_month', 'class="form-select input-small ismall"','value','text', $current_month);

		DonationHelperHtml::renderSubmenu('report');
		$this->bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
		$this->addToolbar();
		parent::display();
	}

    /**
     * Add toolbar to the view
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('JD_REPORT'), 'generic.png');
        $canDo = DonationHelper::getActions();
        ToolbarHelper::cancel();
        if ($canDo->get('core.admin'))
        {
            ToolbarHelper::preferences('com_jdonation');
        }
    }
}