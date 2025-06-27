<?php
use Joomla\CMS\Language\Text;
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;
$config = $this->config;
$decimals      = isset($config->decimals) ? $config->decimals : 2;
$dec_point     = isset($config->dec_point) ? $config->dec_point : '.';
$thousands_sep = isset($config->thousands_sep) ? $config->thousands_sep : ',';
if(DonationHelper::isMultipleCurrencies()){
	$active_currencies = $config->active_currencies;
	$active_currencies_array = explode(",",$active_currencies);			
	if((!in_array($config->currency,$active_currencies_array)) && ($config->currency != "")){
		$active_currencies_array[count($active_currencies_array)] = $config->currency;
	}
}
?>
<table class="table table-striped jdonation_dasboard">
	<thead>
	<tr>
		<th class="title" width="30%"><?php echo Text::_('JD_TIME') ?></th>
		<th class="title" width="30%"><?php echo Text::_('JD_NUMBER_DONATIONS') ?></th>
		<th class="title" width="40%" style="text-align:right;padding-right:40px;"><?php echo Text::_('JD_AMOUNT') ?></th>
	</tr>
	</thead>
	<tbody>

	<tr>
		<td>
			<?php echo Text::_('JD_LAST_WEEK'); ?>
		</td>
		<td class="center">
			<?php 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					if($this->data['last_week'][$currency]['total_donations'] > 0){
						echo "<strong>";
						echo $this->data['last_week'][$currency]['total_donations']; 
						echo "</strong>";
					}else{
						echo "0";
					}
					echo "<BR />";
				}
			}else{
				if($this->data['last_week']['total_donations'] > 0){
					echo "<strong>";
					echo $this->data['last_week']['total_donations']; 
					echo "</strong>";
				}else{
					echo "0";
				}
			}
			?>
		</td>
		<td style="text-align:right;padding-right:40px;">
			<?php //echo DonationHelperHtml::formatAmount($config, $this->data['last_week']['total_amount']); 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					echo number_format($this->data['last_week'][$currency]['total_amount'], $decimals, $dec_point, $thousands_sep);
					echo "&nbsp;";
					echo "<strong style='font-size:10px;'>";
					echo $currency;
					echo "</strong>";
					echo "<BR />";
				}
			}else{
				//echo DonationHelperHtml::formatAmount($config, $this->data['today']['total_amount']);
				echo number_format($this->data['last_week']['total_amount'], $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				echo $config->currency;
				echo "</strong>";
			}
			?>
		</td>
	</tr>

	<tr>
		<td>
			<?php echo Text::_('JD_THIS_MONTH'); ?>
		</td>
		<td class="center">
			<?php 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					if($this->data['this_month'][$currency]['total_donations'] > 0){
						echo "<strong>";
						echo $this->data['this_month'][$currency]['total_donations']; 
						echo "</strong>";
					}else{
						echo "0";
					}
					echo "<BR />";
				}
			}else{
				if($this->data['this_month']['total_donations'] > 0){
					echo "<strong>";
					echo $this->data['this_month']['total_donations']; 
					echo "</strong>";
				}else{
					echo "0";
				}
			}
			?>
		</td>
		<td style="text-align:right;padding-right:40px;">
			<?php //echo DonationHelperHtml::formatAmount($config, $this->data['this_month']['total_amount']) 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					echo number_format($this->data['this_month'][$currency]['total_amount'], $decimals, $dec_point, $thousands_sep);
					echo "&nbsp;";
					echo "<strong style='font-size:10px;'>";
					echo $currency;
					echo "</strong>";
					echo "<BR />";
				}
			}else{
				//echo DonationHelperHtml::formatAmount($config, $this->data['today']['total_amount']);
				echo number_format($this->data['this_month']['total_amount'], $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				echo $config->currency;
				echo "</strong>";
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo Text::_('JD_LAST_MONTH'); ?>
		</td>
		<td class="center">
			<?php
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					if($this->data['last_month'][$currency]['total_donations'] > 0){
						echo "<strong>";
						echo $this->data['last_month'][$currency]['total_donations']; 
						echo "</strong>";
					}else{
						echo "0";
					}
					echo "<BR />";
				}
			}else{
				if($this->data['last_month']['total_donations'] > 0){
					echo "<strong>";
					echo $this->data['last_month']['total_donations']; 
					echo "</strong>";
				}else{
					echo "0";
				}
			}
			?>
		</td>
		<td style="text-align:right;padding-right:40px;">
			<?php //echo DonationHelperHtml::formatAmount($config, $this->data['last_month']['total_amount']) 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					echo number_format($this->data['last_month'][$currency]['total_amount'], $decimals, $dec_point, $thousands_sep);
					echo "&nbsp;";
					echo "<strong style='font-size:10px;'>";
					echo $currency;
					echo "</strong>";
					echo "<BR />";
				}
			}else{
				//echo DonationHelperHtml::formatAmount($config, $this->data['today']['total_amount']);
				echo number_format($this->data['last_month']['total_amount'], $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				echo $config->currency;
				echo "</strong>";
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo Text::_('JD_THIS_YEAR'); ?>
		</td>
		<td class="center">
			<?php 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					if($this->data['this_year'][$currency]['total_donations'] > 0){
						echo "<strong>";
						echo $this->data['this_year'][$currency]['total_donations']; 
						echo "</strong>";
					}else{
						echo "0";
					}
					echo "<BR />";
				}
			}else{
				if($this->data['this_year']['total_donations'] > 0){
					echo "<strong>";
					echo $this->data['this_year']['total_donations']; 
					echo "</strong>";
				}else{
					echo "0";
				}
			}
			?>
		</td>
		<td style="text-align:right;padding-right:40px;">
			<?php //echo DonationHelperHtml::formatAmount($config, $this->data['this_year']['total_amount']) 
			
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					echo number_format($this->data['this_year'][$currency]['total_amount'], $decimals, $dec_point, $thousands_sep);
					echo "&nbsp;";
					echo "<strong style='font-size:10px;'>";
					echo $currency;
					echo "</strong>";
					echo "<BR />";
				}
			}else{
				//echo DonationHelperHtml::formatAmount($config, $this->data['today']['total_amount']);
				echo number_format($this->data['this_year']['total_amount'], $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				echo $config->currency;
				echo "</strong>";
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo Text::_('JD_LAST_YEAR'); ?>
		</td>
		<td class="center">
			<?php
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					if($this->data['last_year'][$currency]['total_donations'] > 0){
						echo "<strong>";
						echo $this->data['last_year'][$currency]['total_donations']; 
						echo "</strong>";
					}else{
						echo "0";
					}
					echo "<BR />";
				}
			}else{
				if($this->data['last_year']['total_donations'] > 0){
					echo "<strong>";
					echo $this->data['last_year']['total_donations']; 
					echo "</strong>";
				}else{
					echo "0";
				}
			}
			?>
		</td>
		<td style="text-align:right;padding-right:40px;">
			<?php //echo DonationHelperHtml::formatAmount($config, $this->data['last_year']['total_amount']) 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					echo number_format($this->data['last_year'][$currency]['total_amount'], $decimals, $dec_point, $thousands_sep);
					echo "&nbsp;";
					echo "<strong style='font-size:10px;'>";
					echo $currency;
					echo "</strong>";
					echo "<BR />";
				}
			}else{
				//echo DonationHelperHtml::formatAmount($config, $this->data['today']['total_amount']);
				echo number_format($this->data['last_year']['total_amount'], $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				echo $config->currency;
				echo "</strong>";
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo Text::_('JD_TOTAL_DONATIONS'); ?>
		</td>
		<td class="center">
			<?php 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					if($this->data['total_donations'][$currency]['total_donations'] > 0){
						echo "<strong>";
						echo $this->data['total_donations'][$currency]['total_donations']; 
						echo "</strong>";
					}else{
						echo "0";
					}
					echo "<BR />";
				}
			}else{
				if($this->data['total_donations']['total_donations'] > 0){
					echo "<strong>";
					echo $this->data['total_donations']['total_donations']; 
					echo "</strong>";
				}else{
					echo "0";
				}
			}
			?>
		</td>
		<td style="text-align:right;padding-right:40px;">
			<?php //echo DonationHelperHtml::formatAmount($config, $this->data['total_donations']['total_amount']) 
			if(DonationHelper::isMultipleCurrencies()){
				foreach($active_currencies_array as $currency){
					echo number_format($this->data['total_donations'][$currency]['total_amount'], $decimals, $dec_point, $thousands_sep);
					echo "&nbsp;";
					echo "<strong style='font-size:10px;'>";
					echo $currency;
					echo "</strong>";
					echo "<BR />";
				}
			}else{
				//echo DonationHelperHtml::formatAmount($config, $this->data['today']['total_amount']);
				echo number_format($this->data['total_donations']['total_amount'], $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				echo $config->currency;
				echo "</strong>";
			}
			?>
		</td>
	</tr>
	</tbody>
</table>
