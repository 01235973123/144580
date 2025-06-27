<?php
/**
 * @version        4.2
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

if($campaign_id > 0)
{
	$url = Route::_(DonationHelperRoute::getDonationFormRoute($campaign_id, $itemId), false, (int) $config->use_https);
}
else
{
	$url = Route::_('index.php?option=com_jdonation&view=donation&Itemid='.$itemId, false, (int) $config->use_https);
}
?>
<style>
td.mod_jdonation_amount .mod_jd_switch_amounts  input:checked + label {
   color:<?php echo $highlight_text;?>;
   background-color:<?php echo $highlight_bgcolor; ?>;
   border:1px solid <?php echo $highlight_bgcolor; ?>;
}
td.mod_jdonation_amount .mod_jd_switch_amounts label {
	width:<?php echo $box_width?>px;
}

#give-donation-level-button-wrap .give-btn:hover, .give-default-level {
	background-color: <?php echo $highlight_bgcolor; ?> !important;
	color: <?php echo $highlight_text; ?> !important;
}

.give-total-wrap .give-currency-symbol {
	background: <?php echo $highlight_bgcolor; ?>;
}

.elementor-give-totals
{
	background: <?php echo $background_color; ?>;
	color: <?php echo $text_color; ?>;
}

.give-goal-progress .progress .bar
{
	background-color: <?php echo $highlight_bgcolor; ?>;
}

</style>
<form name="campaign_form" id="campaign_form" method="post" action="<?php echo $url; ?>">
	<div class="elementor-give-totals">
		
		<div class="campaign_title">
			<?php
			if($campaign->id > 0)
			{
				echo $campaign->title;
			}
			elseif($show_campaign == 1)
			{
				echo $lists['campaign_id'];
			}
			?>
		</div>
		<?php
		if($campaign->id > 0)
		{
		?>
		<div class="give-totals-shortcode-wrap">
			<div class="give-card__progress give-card__progress-custom" data-type="line" data-strokewidth="1" data-easing="linear" data-duration="800" data-color="#FFEA82" data-trailcolor="#EEEEEE" data-trailwidth="1" data-tocolor="#ED6A5A" data-width="100%" data-height="15px">  	
				<div class="give-goal-progress">
					<div class="raised">
						<?php
						if($show_raised == 1)
						{
						?>
						<span class="raised-income">
							<span class="income"><?php echo DonationHelperHtml::formatAmount($config, $campaign->donated_amount,$config->currency_symbol); ?></span>
							<?php echo Text::_('JD_RAISED');?>:
						</span>
						<?php
						}
						if($campaign->show_goal == 1)
						{
							?>
							<span class="raised-goal">
								<span class="goal-text"><?php echo DonationHelperHtml::formatAmount($config, $campaign->goal,$config->currency_symbol); ?></span> 
								<?php echo Text::_('JD_CAMPAIGN_GOAL');?>:
							</span>
						<?php
						}		
						?>
					</div>
					<?php
					
					if($campaign->goal != null)
					{
						if((int) $campaign->goal == 0)
						{
							$goal = 100;
						}
						else
						{
							$goal = $campaign->goal;
						}
					}
					else
					{
						$goal = 100;
					}
					$donatedPercent = ceil($campaign->donated_amount/ $goal *100);
					if($campaign->show_goal == 1)
					{
					?>
						<div class="progress">
							<div class="bar" style="width: <?php echo $donatedPercent; ?>%"></div>
						</div>
					<?php
					}	
					?>
				</div>
			</div>
		</div>
		<?php
		}				
		?>
		<div class="donation-module-general-div">
			<div class="give-total-wrap">
				<div class="give-donation-amount form-row-wide">
					<span class="give-currency-symbol give-currency-position-before">
						<?php echo $config->currency_symbol; ?>
					</span>
					<?php
					if($donationType == 1)
					{
						$disabled = "disabled";
					}
					else
					{
						$disabled = "";
					}
					?>
					<input class="give-text-input give-amount-top form-control" id="rd_amount1" name="rd_amount1" type="text" inputmode="decimal" placeholder="" value="" autocomplete="off" <?php echo $disabled; ?> />
					<input type="hidden" name="rd_amount" id="rd_amount" value="" />
				</div>
			</div>
			<?php
			if($donationType != 0)
			{
			?>
			<ul id="give-donation-level-button-wrap" class="give-donation-levels-wrap give-list-inline">
				<?php
				$amounts = (array) $amounts;
				if(count($amounts) > 0)
				{
					for ($i = 0 , $n = count($amounts); $i < $n; $i++)
					{
						$amount = $amounts[$i] ;
						if(strpos($amount, "[c]") > 0)
						{
							$amount = substr($amount, 0, strpos($amount, "[c]"));
							$checked = ' checked="checked" ' ;
						}
						else
						{
							$checked = '' ;
						}
						$amount = (float)$amount ;
						?>
						<li>
							<button type="button" data-price-id="<?php echo $i; ?>" class="give-donation-level-btn give-btn give-btn-level-1 " value="<?php echo $amount; ?>" id="amount<?php echo $i;?>"><?php echo DonationHelperHtml::formatAmount($config, $amount);?></button>
						</li>
						<script type="text/javascript">
						jQuery( "#amount<?php echo $i;?>" ).click(function() {
							jQuery(".give-btn").removeClass("give-default-level");
							jQuery( "#amount<?php echo $i;?>" ).addClass("give-default-level");
							jQuery("#rd_amount1").val('<?php echo $amount; ?>');
							jQuery("#rd_amount").val('<?php echo $amount; ?>');
						});
						</script>
					<?php
					}
				}
				?>
			</ul>
			<?php
			}		
			?>
			<div class="give-payment-wrap">
				<?php
				if (count($paymentPlugins) > 1)
				{
					$options = array();
					$options[] = HTMLHelper::_('select.option', '', Text::_('JD_PAYMENT_METHOD'));
					for ($i = 0 , $n = count($paymentPlugins); $i < $n; $i++)
					{
						$method = $paymentPlugins[$i];
						$options[] =  HTMLHelper::_('select.option', $method->getName(), Text::_($method->getTitle()));
					}
					?>
					<?php echo HTMLHelper::_('select.genericlist', $options, 'payment_method', ' class="form-select" ', 'value', 'text'); ?>
					<?php
				}
				?>
			</div>
			<div class="give-donate-button-wrap">
				<button type="button" class="button btn btn-warning" name="btnDonate" id="btnDonate" onclick="checkDonation(this.form);"><?php echo Text::_('JD_DONATE_NOW'); ?></button>
			</div>
		</div>
	</div>
	
    <?php
	if (count($paymentPlugins) == 1)
	{
	?>
		<input type="hidden" name="payment_method" value="<?php echo $paymentPlugins[0]->getName(); ?>" />
	<?php
	}
    ?>
	<input type="hidden" name="option" value="com_jdonation" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="Itemid" value="<?php echo $itemId;?>" />
	</form>
	<script type="text/javascript">
		var donationType = <?php echo $donationType; ?>;
		var minimumAmount = <?php echo $minimumAmount ; ?> ;
		var maximumAmount = <?php echo $maximumAmount ; ?> ; 
		function checkDonation(form) 
		{				
			var amount = 0 ;	
			
			if (!parseFloat(form.rd_amount.value) && parseFloat(form.rd_amount1.value) > 0) 
			{
				form.rd_amount.value = form.rd_amount1.value;
			}

			if (!parseFloat(form.rd_amount.value) && !parseFloat(form.rd_amount1.value)) {
				alert("<?php echo Text::_('JD_ENTER_VALID_AMOUNT'); ?>");
				form.rd_amount.focus();						
				return;
			}
			if (parseFloat(form.rd_amount.value) < minimumAmount) {
				alert("<?php echo Text::_('JD_MINIMUM_AMOUNT_ALLOWED'); ?> : <?php echo $currencySymbol; ?>" + minimumAmount);
				form.rd_amount.focus();						
				return;
			}
			if ((maximumAmount > 0) &&  (parseFloat(form.rd_amount.value) > maximumAmount)) {
				alert("<?php echo Text::_('JD_MAXIMUM_AMOUNT_ALLOWED'); ?> : <?php echo $currencySymbol; ?>" + maximumAmount);
				form.rd_amount.focus();						
				return;
			}
					
            if (form.payment_method.value == '')
            {
                alert("<?php echo Text::_('JD_CHOOSE_PAYMENT_METHOD'); ?>");
                form.payment_method.focus();
                return;
            }
			//All data is valid, submit form for processing
			form.submit();
		}
	</script>	
</form>