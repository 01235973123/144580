<?php
/**
 * @version        5.9.8
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;

HTMLHelper::_('bootstrap.tooltip');
$db						= Factory::getContainer()->get('db');
$query					= $db->getQuery(true);
$selectedState			= '';
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span5Class				= $bootstrapHelper->getClassMapping('span5');
$span3Class				= $bootstrapHelper->getClassMapping('span3');
$controlGroupClass		= $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass		= $bootstrapHelper->getClassMapping('control-label');
$controlsClass			= $bootstrapHelper->getClassMapping('controls');
if($this->config->enable_cancel_recurring && $this->item->donation_type == 'R' && $this->item->id > 0)
{
	$payment_method = $this->item->payment_method;
	if($payment_method != "" && $payment_method != "os_offline")
	{
		$query->clear();
		$query->select('params')
			->from('#__jd_payment_plugins')
			->where('name=' . $db->quote($payment_method))
			->where('published = 1');
		$db->setQuery($query);
		$plugin = $db->loadObject();

		$params = new Registry($plugin->params);
		require_once JPATH_ROOT . '/components/com_jdonation/payments/' . $payment_method . '.php';
		$paymentClass = new $payment_method($params);

		if (method_exists($paymentClass, 'supportCancelRecurringSubscription'))
		{
			if($paymentClass->supportCancelRecurringSubscription() && $this->item->recurring_donation_cancelled == 0 && ($this->item->r_times > 0 && $this->item->r_times > $this->item->payment_made))
			{
				ToolbarHelper::custom('donor.cancelrecurringdonation', 'delete', 'delete', 'JD_CANCEL_RECURRING_DONATION', false);
			}
		}
	}
}
?>
<form action="index.php?option=com_jdonation&view=donor" method="post" name="adminForm" id="adminForm" class="form-horizontal" enctype="multipart/form-data">
<div class="<?php echo $rowFluidClass; ?>" id="donorDetailsAdmin">
	<div class="<?php echo $span7Class; ?>">
		<fieldset class="general form-horizontal options-form">
			<legend><?php echo Text::_( 'JD_DONOR_INFORMATION' ); ?></legend>
			<?php
			if ($this->config->use_campaign)
			{
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_CAMPAIGN'); ?>
						<span class="required">*</span>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo $this->lists['campaign_id'] ; ?>
					</div>
				</div>
				<?php
			}
			?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo  Text::_('JD_USER'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?> donor_user">
					<?php echo DonationHelper::getUserInput($this->item->user_id); ?>
				</div>
			</div>
			<?php
			if($this->item->campaign_id > 0)
			{
				$this->form->prepareFormField($this->item->campaign_id);
			}
			$fields = $this->form->getFields();

			if (isset($fields['state']))
			{
				if (StringHelper::strtolower($fields['state']->type) == 'state')
				{
					$stateType = 1;
				}
				else
				{
					$stateType = 0;
				}
				$selectedState = $fields['state']->value;
			}
			echo $this->form->render(true);
			?>
			<?php
			if($this->config->activate_tributes)
			{
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('JD_DEDICATE_DONATION'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if($this->item->show_dedicate == 1)
						{
							$check = "checked";
						}
						else
						{
							$check = "";
						}
						?>
						<input type="checkbox" name="show_dedicate" id="show_dedicate" value="1" <?php echo $check; ?>/>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('JD_DEDICATE_TYPE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
							<?php
							if($this->item->show_dedicate == 0)
							{
								$this->item->dedicate_type = '';
							}
							$option = [];
							$option[] = HTMLHelper::_('select.option','',Text::_('JD_SELECT_DEDICATE_TYPE'));
							for($i=1;$i<=4;$i++)
							{
								$option[] = HTMLHelper::_('select.option', $i, DonationHelper::getDedicateType($i));
							}
							echo HTMLHelper::_('select.genericlist', $option, 'dedicate_type', 'class="input-medium imedium form-select"','value','text', $this->item->dedicate_type);
							
							?>

					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_HONOREE_NAME');?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input type="text" class="input-large form-control" name="dedicate_name" value="<?php echo $this->item->dedicate_name; ?>" />
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_HONOREE_EMAIL');?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input type="text" class="input-large form-control" name="dedicate_email" value="<?php echo $this->item->dedicate_email; ?>" />
					</div>
				</div>
				<?php
			}
			?>
		</fieldset>
	</div>
	<div class="<?php echo $span5Class; ?>">
		<fieldset class="payment form-horizontal options-form">
			<legend><?php echo Text::_( 'JD_PAYMENT_INFORMATION' ); ?></legend>
        
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo  Text::_('JD_DONATION_DATE'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php 
					echo HTMLHelper::_('calendar', $this->item->created_date , 'created_date', 'created_date','%Y-%m-%d %H:%M:%I',array('showTime' => true)) ; 
					?>
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo  Text::_('JD_PAYMENT_DATE'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php 
					echo HTMLHelper::_('calendar', $this->item->payment_date , 'payment_date', 'created_date','%Y-%m-%d %H:%M:%I',array('showTime' => true)) ; 
					?>
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo  Text::_('JD_AMOUNT'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>" style="display:flex;">
					<?php
					if($this->item->currency_code == ""){
						echo $this->config->currency_symbol;
					}
					?>
					<?php
					if(($this->item->amount_converted > 0) && DonationHelper::isMultipleCurrencies() && ($this->item->currency_code != $this->item->currency_converted)){
						?>
						<input type="text" class="input-mini form-control" name="amount" value="<?php echo $this->item->amount_converted > 0 ? round($this->item->amount_converted, 2) : ""; ?>" size="7" />
						<?php
					}else{
						?>
						<input type="text" class="input-mini form-control" name="amount" value="<?php echo $this->item->amount > 0 ? round($this->item->amount, 2) : ""; ?>" size="7" />
						<?php
					}
					?>
					<?php
					if(!DonationHelper::isMultipleCurrencies()){
						echo $this->item->currency_code;
					}else{
						echo $this->lists['currencies'];
					}
					?>
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('JD_PAYMENT_METHOD') ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->lists['payment_method'];?>
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('JD_PAYMENT_GATEWAY_FEE') ?>
				</label>
				<div class="<?php echo $controlsClass; ?>" style="display:flex;">
					<input type="text" class="input-mini form-control" size="7" name="payment_fee" id="payment_fee" value="<?php echo $this->item->payment_fee ; ?>" />
					<?php
					if(!DonationHelper::isMultipleCurrencies()){
						echo $this->item->currency_code;
					}else{
						echo $this->lists['currencies'];
					}
					?>
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('JD_TRANSACTION_ID'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<input type="text" class="input-large form-control" size="50" name="transaction_id" id="transaction_id" value="<?php echo $this->item->transaction_id ; ?>" />
				</div>
			</div>

			<?php
			if ($this->item->donation_type == 'R')
			{
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_DONATION_TYPE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo Text::_('JD_RECURRING'); ?>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_FREQUENCY'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						switch ($this->item->r_frequency)
						{
							case 'd' ;
								echo Text::_('JD_DAILY');
								break ;
							case 'w' :
								echo Text::_('JD_WEEKLY');
								break ;
							case 'b':
								echo Text::_('JD_BI_WEEKLY');
								break ;
							case 'm' :
								echo Text::_('JD_MONTHLY');
								break ;
							case 'q' :
								echo Text::_('JD_QUARTERLY');
								break ;
							case 's' :
								echo Text::_('JD_SEMI_ANNUALLY');
								break ;
							case 'a' :
								echo Text::_('JD_ANNUALLY');
								break ;
						}
						?>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_NUMBER_PAYMENTS'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if (!$this->item->r_times)
						{
							$times = 'Un-limit' ;
						}
						else
						{
							$times = $this->item->r_times ;
						}
						echo $this->item->payment_made.' / '.$times ;
						?>
					</div>
				</div>
				<?php			
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_RECURRING_PAYMENT_STATUS'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						if($this->item->recurring_donation_cancelled == 1)
						{
							echo Text::_('JD_CANCELLED');
						}
						else
						{
							echo Text::_('JD_ACTIVATED');
						}
						?>
					</div>
				</div>
				<input type="hidden" name="donation_type"   value="<?php echo $this->item->donation_type;?>" />
				<input type="hidden" name="r_frequency"     value="<?php echo $this->item->r_frequency;?>" />
				<input type="hidden" name="r_times"         value="<?php echo $this->item->r_times;?>" />
				<input type="hidden" name="payment_made"    value="<?php echo $this->item->payment_made;?>" />
				<?php
			}
			?>
			<?php 
			if($this->item->payment_method ==='os_jd_offline_creditcard' && $this->item->params)
			{	
				$params  = new Registry($this->item->params);
				require_once JPATH_ROOT . '/components/com_jdonation/helper/encrypt.php';
				$ccEncryption		= new CreditCardEncryption();
				$last_cc_characters	= $params->get('last_characters');
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('AUTH_CARD_NUMBER'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo $params->get('card_number'); ?>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo $params->get('exp_date'); ?>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('AUTH_CVV_CODE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo $params->get('cvv'); ?>
					</div>
				</div>
				<?php
			}
			?>
			<?php
			if ($this->config->enable_hide_donor)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_HIDE_DONOR'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo DonationHelperHtml::showCheckboxfield('hide_me',(int)$this->item->hide_me); ?>
					</div>
				</div>
			<?php
			}	
			
			?>
			<?php
			if ($this->config->enable_gift_aid)
			{
			?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_GIFT_AID'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo DonationHelperHtml::showCheckboxfield('gift_aid',(int)$this->item->gift_aid); ?>
					</div>
				</div>
			<?php
			}	
			?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('JD_PAID'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo DonationHelperHtml::showCheckboxfield('published',(int)$this->item->published); ?>
				</div>
			</div>
			<?php
			if($this->config->activate_donation_receipt_feature && $this->item->id > 0)
			{
				if(!$this->config->generated_invoice_for_paid_donation_only || ($this->config->generated_invoice_for_paid_donation_only && $this->item->published == 1))
				{
					?>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_INVOICE'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<a href="<?php echo Uri::root().'index.php?option=com_jdonation&task=download_receipt&id='.$this->item->id; ?>" title="<?php echo Text::_('JD_DOWNLOAD'); ?>"><?php echo Text::_('JD_DOWNLOAD'); ?></a>
						</div>
					</div>
					<?php
				}
			}
			?>
		</fieldset>
	</div>
</div>

<input type="hidden" name="id" value="<?php echo (int)$this->item->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
	var siteUrl = "<?php echo Uri::root(); ?>";
	<?php
	if ($stateType) 
	{

	?>
		(function($){
			buildStateField = (function(stateFieldId, countryFieldId, defaultState){
				if($('#' + stateFieldId).length)
				{
					//set state
					if ($('#' + countryFieldId).length)
					{
						var countryName = $('#' + countryFieldId).val();
					}
					else 
					{
						var countryName = '';
					}
					$.ajax({
						type: 'POST',
						url: siteUrl + 'index.php?option=com_jdonation&task=get_states&country_name='+ countryName+'&field_name='+stateFieldId + '&state_name=' + defaultState,
						success: function(data) {
							$('#field_' + stateFieldId + ' .controls').html(data);
							$('#field_' + stateFieldId + ' .col-md-9').html(data);
						},
						error: function(jqXHR, textStatus, errorThrown) {						
							alert(textStatus);
						}
					});			
					//Bind onchange event to the country 
					if ($('#' + countryFieldId).length)
					{
						$('#' + countryFieldId).change(function(){
							$('#field_' + stateFieldId + ' .controls select').after('<span class="wait">&nbsp;<img src="components/com_jdonation/assets/images/loading.gif" alt="" /></span>');
							$.ajax({
								type: 'POST',
								url: siteUrl + 'index.php?option=com_jdonation&task=get_states&country_name='+ $(this).val()+'&field_name=' + stateFieldId + '&state_name=' + defaultState,
								success: function(data) {
									$('#field_' + stateFieldId + ' .controls').html(data);
									$('#field_' + stateFieldId + ' .col-md-9').html(data);
									$('.wait').remove();
								},
								error: function(jqXHR, textStatus, errorThrown) {						
									alert(textStatus);
								}
							});
							
						});
					}						
				}//end check exits state
			});

			$(document).ready(function(){							
				buildStateField('state', 'country', '<?php echo $selectedState; ?>');										
			})
		})(jQuery)
	<?php
	} 
	?>
	populateUserData = (function(){
		var id = jQuery('#user_id_id').val();
		var data = {
			'task'	 :'populateUserData',
			'user_id' : id
		};
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo Uri::root(); ?>index.php?option=com_jdonation&tmpl=component&format=raw',
			data: data,
			dataType: 'json',
			success: function(json) 
			{
				//json = $.parseJSON(response);
				var selecteds = [];
				for (var field in json)
				{
					value = json[field];
					if (jQuery("input[name='" + field + "[]']").length)
					{
						//This is a checkbox or multiple select
						if (jQuery.isArray(value))
						{
							selecteds = value;
						}
						else
						{
							selecteds.push(value);
						}
						jQuery("input[name='" + field + "[]']").val(selecteds);
					}
					else if (jQuery("input[type='radio'][name='" + field + "']").length)
					{
						jQuery("input[name="+field+"][value=" + value + "]").attr('checked', 'checked');
					}
					else
					{
						jQuery('#' + field).val(value);
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
	});
	</script>	
