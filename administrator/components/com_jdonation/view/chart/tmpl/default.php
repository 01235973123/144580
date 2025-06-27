<?php
/**
 * @package            Joomla
 * @subpackage         Documents Seller
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2009 - 2023 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.core');
$this->sales	= (array)$this->sales;
$rootUri		= Uri::root(true);
$config			= DonationHelper::getConfig();
Factory::getApplication()->getDocument()->getWebAssetManager()
	->registerAndUseScript('com_jdonation.chart',$rootUri . '/administrator/components/com_jdonation/assets/js/chartjs/Chart.min.js')
	->registerAndUseScript('com_jdonation.chart_datalabels',$rootUri . '/administrator/components/com_jdonation/assets/js/chartjs/chartjs-plugin-datalabels.min.js')
	->registerAndUseScript('com_jdonation.chart_default',$rootUri . '/administrator/components/com_jdonation/assets/js/admin-chart-default.js')
Factory::getDocument(->addScriptOptions('labels', array_keys($this->sales))
Factory::getDocument(->addScriptOptions('sales', array_values($this->sales))
?>
<form action="index.php?option=com_jdonation&view=chart" method="post" name="adminForm" id="adminForm">
<?php
if ($config->use_campaign)
{
?>
    <div class="dms-chart-document-filter-container span2">
        <?php echo $this->lists['filter_campaign_id']; ?>
    </div>
<?php
}	
?>
<canvas id="dms-sales-chart"></canvas>
<input type="hidden" name="currency_symbol" id="currency_symbol" value="<?php echo $config->currency_symbol; ?>" />
</form>

