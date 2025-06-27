<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

Factory::getApplication()
	->getDocument()
	->addScriptOptions('dailyLabels', $this->dailySales['labels'])
	->addScriptOptions('dailySales', $this->dailySales['income'])
	->addScriptOptions('dailySubscriptionsCount', $this->dailySales['count'])
	->addScriptOptions('dailySalesAjaxUrl', Uri::base(true) . '/index.php?option=com_osmembership&task=subscription.get_daily_sales_chart_data')
	->getWebAssetManager()
	->registerAndUseScript('com_osmembership.Chart', 'media/com_osmembership/assets/js/chartjs/Chart.min.js')
	->registerAndUseScript('com_osmembership.chartjs-plugin-datalabels', 'media/com_osmembership/assets/js/chartjs/chartjs-plugin-datalabels.min.js')
	->registerAndUseScript('com_osmembership.dashboard-daily-sales-chart', 'media/com_osmembership/js/admin-dashboard-daily-sales-chart.min.js');

Text::script('OSM_SALES_INCOME');
?>
<canvas id="osm-daily-sales-chart"></canvas>