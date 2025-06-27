<?php
use Joomla\CMS\Factory;
/**
 * @package            Joomla
 * @subpackage         Documents Seller
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2009 - 2023 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class DonationViewChartHtml extends OSFViewHtml
{
	public function display()
	{
		$state = $this->model->getState();
		$config = DonationHelper::getConfig();
		if ($config->use_campaign)
		{
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('id, title')
				->from('#__jd_campaigns')
				->order('title');
			$db->setQuery($query);
			$options                            = array();
			$options []                         = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CAMPAIGN'), 'id', 'title');
			$options                            = array_merge($options, $db->loadObjectList());
			$lists['filter_campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_campaign_id', ' onchange="submit();" ', 'id', 'title', $this->state->filter_campaign_id);
			$query->clear();
		}

		$this->sales = array_reverse($this->model->getData());
		$this->lists = $lists;
		ToolbarHelper::title(Text::_('JD_DONATION_CHART'), 'dashboard.png');
		DonationHelperHtml::renderSubmenu('chart');
		parent::display();
	}
}
