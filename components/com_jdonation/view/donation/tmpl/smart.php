<?php
/**
 * @version        5.4.13
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\String\StringHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;

HtmlHelper::_('behavior.core');
$option = Factory::getApplication()->input->getString('option', '');
if($option == "com_jdonation")
{
	$url = Route::_('index.php?option=com_jdonation&Itemid='.$this->Itemid);
}
else
{
	$url = Uri::root()."index.php?option=com_jdonation&Itemid=".$this->Itemid;
}
if (($this->config->accept_term ==1 && $this->config->article_id > 0) || ($this->config->show_privacy && $this->config->privacy_policy_article_id > 0))
{
	DonationHelperJquery::colorbox('jd-modal');
}
DonationHelperJquery::validateForm();
$languageKeys = [
    'JD_PLEASE_SELECT_DONATION_AMOUNT_OR_ENTER_CUSTOM_AMOUNT',
    'JD_PLEASE_ENTER_A_VALID_CUSTOM_AMOUNT', 
    'JD_SELECT_PAYMENT_OPTION',
    'JD_DAILY',
	'JD_WEEKLY',
	'JD_BI_WEEKLY',
	'JD_MONTHLY',
	'JD_QUARTERLY',
	'JD_SEMI_ANNUALLY',
	'JD_ANNUALLY',
	'JD_ONE_TIME',
	'JD_ANONYMOUS',
	'JD_PROCESSING_YOUR_DONATION',
	'JD_PLEASE_WAIT_WHILE_WE_PROCESS_YOUR_DONATION',
	'JD_DO_NOT_REFRESH_OR_CLOSE',
	'JD_PLEASE_SELECT_A_DONATION_FREQUENCY',
	'JD_PLEASE_ENTER_VALID_DONATION_AMOUNT'
];

foreach ($languageKeys as $key) {
    Text::script($key);
}

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('com_jdonation.smart', Uri::root().'media/com_jdonation/assets/js/smart.js');
$wa->registerAndUseStyle('com_jdonation.smart.css', Uri::root().'media/com_jdonation/assets/css/smart.css');
//Validation rule fo custom amount
$amountValidationRules = '';
$minDonationAmount = (int) $this->minDonationAmount;
$maxDonationAmount = (int) $this->maxDonationAmount;
if($minDonationAmount == 0)
{
	$minDonationAmount = $this->config->minimum_donation_amount;
}
if($maxDonationAmount == 0)
{
	$maxDonationAmount = $this->config->maximum_donation_amount;
}
if ($minDonationAmount)
{
	$amountValidationRules .= ",min[$minDonationAmount]";
}
if ($maxDonationAmount)
{
	$amountValidationRules .= ",max[$maxDonationAmount]";
}

echo "<script>
    const DONATION_CONFIG = {
        MIN_AMOUNT: " . (int)$minDonationAmount . ",
        MAX_AMOUNT: " . (int)$maxDonationAmount . "
    };
</script>";
?>
<script>
    const JD_LANG = {
        MIN_DONATION_AMOUNT: "<?php echo sprintf(Text::_('JD_MIN_DONATION_AMOUNT_ALLOWED'), $minDonationAmount); ?>",
        MAX_DONATION_AMOUNT: "<?php echo sprintf(Text::_('JD_MAX_DONATION_AMOUNT_ALLOWED'), $maxDonationAmount); ?>"
    };
</script>
<?php
$db    = Factory::getContainer()->get('db');
$selectedState = '';
$bootstrapHelper 	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class		= $bootstrapHelper->getClassMapping('span12');
$span3Class		    = $bootstrapHelper->getClassMapping('span3');
$span6Class		    = $bootstrapHelper->getClassMapping('span6');
$controlGroupClass 	= $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass 	= $bootstrapHelper->getClassMapping('input-group');
$addOnClass        	= $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass 	= $bootstrapHelper->getClassMapping('control-label');
$controlsClass     	= $bootstrapHelper->getClassMapping('controls');
$btnClass          	= $bootstrapHelper->getClassMapping('btn');
$inputSmallClass	= $bootstrapHelper->getClassMapping('input-small');
$inputLargeClass	= $bootstrapHelper->getClassMapping('input-large');
$stripePaymentMethod = null;
$hasSquareCard      = false;
if($this->campaignId > 0 && $this->campaign->currency_symbol != "")
{
	$this->config->currency_symbol = $this->campaign->currency_symbol;
}
$color				= "";
if($this->config->color != '')
{
	$color			= $this->config->color;
	if(substr($color, 0, 1) == "#")
	{
		$color		= substr($color, 1);
	}
}
$color = "#".$color;
?>
<script type="text/javascript">
	<?php echo $this->recurringString ;?>
	var siteUrl	= "<?php echo DonationHelper::getSiteUrl(); ?>";
	var root_path	= "<?php echo DonationHelper::getSiteUrl(); ?>";
	var amounts_format = '<?php echo $this->config->amounts_format; ?>';
	var recurring_require = <?php echo $this->recurring_require; ?>;
</script>
<script type="text/javascript" src="<?php echo DonationHelper::getSiteUrl().'media/com_jdonation/assets/js/jdonation.js'?>"></script>
<script type='text/javascript' src="<?php echo DonationHelper::getSiteUrl().'media/com_jdonation/assets/js/imask/imask.min.js';?>"></script>
<div id="errors"></div>
<?php
$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");

$allow_donation = true;
$msg = "";
if($this->campaign->id > 0)
{
	$total_donated = DonationHelper::getTotalDonatedAmount($this->campaign->id);
	$total_donors  = DonationHelper::getTotalDonor($this->campaign->id);
	if (!$this->config->endable_donation_with_expired_campaigns && (($this->campaign->end_date != "" && $this->campaign->end_date != "0000-00-00 00:00:00") && (strtotime($this->campaign->end_date) < time())))
	{
		//already expired
		$allow_donation = false;
		$msg = Text::_('JD_EXPIRED_CAMPAIGN');
	}
	if($this->campaign->goal > 0 && $total_donated > $this->campaign->goal && ! $this->config->endable_donation_with_goal_achieved_campaigns && $allow_donation)
	{
		$allow_donation = false;
		$msg = Text::_('JD_GOAL_ACHIEVED');
	}
	if((int)$this->campaign->limit_donors > 0 && $total_donors > (int)$this->campaign->limit_donors && $allow_donation)
	{
		$allow_donation = false;
		$msg = Text::_('JD_NUMBER_DONORS_ACHIEVED');
	}
}

if($allow_donation)
{
	//show campaign
	if($this->campaign->id > 0)
	{
		$campaign_link = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).Route::_(DonationHelperRoute::getDonationFormRoute($this->campaign->id,Factory::getApplication()->input->getInt('Itemid',0)));
		
		$config=Factory::getApplication()->getConfig();
		if(JVERSION>=3.0)
			$site_name=$config->get( 'sitename' );
		else
			$site_name=$config->getvalue( 'config.sitename' );

		require_once(JPATH_SITE . "/components/com_jdonation/helper/integrations.php");

		$doc = Factory::getApplication()->getDocument();
		if ($this->option == "com_jdonation")
		{
			$doc->addCustomTag( '<meta property="og:title" content="'.$this->campaign->title.'" />' );
			if($this->campaign->campaign_photo != "")
			{
				if(file_exists(JPATH_ROOT.'/images/jdonation/'.$this->campaign->campaign_photo))
				{
					$doc->addCustomTag( '<meta property="og:image" content="'.Uri::root().'images/jdonation/'.$this->campaign->campaign_photo.'" />' );
				}
				elseif(file_exists(JPATH_ROOT.'/'.$this->campaign->campaign_photo))
				{
					$doc->addCustomTag( '<meta property="og:image" content="'.Uri::root().$this->campaign->campaign_photo.'" />' );
				}
			}
			if($this->campaign->short_description != "")
			{
				$short_desc = strip_tags($this->campaign->short_description);
				$short_desc = str_replace("\n","",$short_desc);
				$short_desc = str_replace("\r","",$short_desc);
				$short_desc = str_replace("\"","",$short_desc);
				$short_desc = str_replace("'","",$short_desc);
				if(strlen($short_desc) > 155)
				{
					$short_desc = substr($short_desc,0,155)."..";
				}
				$doc->addCustomTag( '<meta property="og:description" content="'.$short_desc.'" />' );
			}
			$doc->addCustomTag( '<meta property="og:url" content="'.$campaign_link.'" />' );
			$doc->addCustomTag( '<meta property="og:site_name" content="'.$site_name.'" />' );
			$doc->addCustomTag( '<meta property="og:type" content="article" />' );
		}
	}
	
	if($this->campaign->donation_form_msg)
	{
		$message = $this->campaign->donation_form_msg;
	}
	else
	{
		$message = $this->config->donation_form_msg;
	}

	if ($this->config->use_campaign)
	{
		
		if($this->campaign->id > 0)
		{
			$title = Text::_('JD_DONATE_TO')." ".$this->campaign->title;
		}
		else
		{
			$title = Text::_('JD_MAKE_CONTRIBUTION');
		}
	}
	?>
	<form id="os_form" method="post" action="<?php echo $url; ?>" autocomplete="off" enctype="multipart/form-data">
	<div id="donation-form" class="smart-container <?php echo $extralayoutCss; ?>">
		
		<div class="donation-form-container"> 
			<h1 class="heading-donation"><?php echo $title; ?></h1>
       		<?php
            if (strlen($message))
            {
            ?>
                <div class="jd-message clearfix jd_width_100_percentage"><?php echo $message; ?></div>
            <?php
            }
            
			?>
			<div class="smart-progress-bar">
                <div class="smart-progress-step clickable active" data-step="1">
                    <div class="smart-step-number">1</div>
                    <div class="smart-step-label"><?php echo Text::_('JD_AMOUNT');?></div>
                </div>
                <div class="smart-progress-line"></div>
                <div class="smart-progress-step clickable" data-step="2">
                    <div class="smart-step-number">2</div>
                    <div class="smart-step-label"><?php echo Text::_('JD_INFO');?></div>
                </div>
                <div class="smart-progress-line"></div>
                <div class="smart-progress-step clickable" data-step="3">
                    <div class="smart-step-number">3</div>
                    <div class="smart-step-label"><?php echo Text::_('JD_PAYMENT');?></div>
                </div>
				<div class="smart-progress-line"></div>
                <div class="smart-progress-step" data-step="4">
                    <div class="smart-step-number">4</div>
                    <div class="smart-step-label">Confirm</div>
                </div>
            </div>

			<div class="steps-container">
                    <!-- Step 1: Donation Amount -->
                    <div class="step" id="step-1">
                        <div class="step-content">
							<?php
							$show_recurring = false;
							if ($this->config->enable_recurring)
							{
								if ($this->campaignId)
								{
									if (($this->campaign->donation_type == 0 || $this->campaign->donation_type == 2)&& $this->method->getEnableRecurring())
									{
										$show_recurring = true;
									}
								}
								else
								{
									if ($this->method->getEnableRecurring())
									{
										$show_recurring = true;
									}
								}
							}
							if($show_recurring && count((array)$this->recurringFrequencies) > 0)
							{
								?>
								<div class="form-group">
									<label><?php echo Text::_('JD_HOW_OFTEN_WOULD_YOU_LIKE_TO_DONATE'); ?></label>
									<div class="frequency-options">
										<label class="frequency-option">
											<input type="radio" name="r_frequency" value="one-time" checked>
											<span><?php echo Text::_('JD_ONE_TIME');?></span>
										</label>
										<?php 
										foreach($this->recurringFrequencies as $frequency)
										{
											switch($frequency)
											{
												case 'd':
													$label = Text::_('JD_DAILY');
													break;
												case 'w':
													$label = Text::_('JD_WEEKLY');
													break;
												case 'b':
													$label = Text::_('JD_BI_WEEKLY');
													break;
												case 'm':
													$label = Text::_('JD_MONTHLY');
													break;
												case 'q':
													$label = Text::_('JD_QUARTERLY');
													break;
												case 's':
													$label = Text::_('JD_SEMI_ANNUALLY');
													break;
												case 'a':
													$label = Text::_('JD_ANNUALLY');
													break;
											}
											?>
											<label class="frequency-option">
												<input type="radio" name="r_frequency" value="<?php echo $frequency;?>" />
												<span><?php echo $label;?></span>
											</label>
											<?php
										}
										?>
									</div>
								</div>
							<?php } ?>
							<div class="form-group">
								<?php
								$explanations = explode("\r\n", $rowCampaign->amounts_explanation) ;
                				$amounts = explode("\r\n", $this->config->donation_amounts);
								?>
                                <label><?php echo Text::_('JD_SELECT_AN_AMOUNT'); ?></label>
								
                                <div class="amount-options">
									<?php if(count($amounts) > 0):?>
										<?php
										$i = 0;
										foreach($amounts as $amount)
										{
											if(strpos($amount, "[c]") > 0)
											{
												$amount = substr($amount, 0, strpos($amount, "[c]"));
												if((int)$this->rdAmount == 0)
												{
													$this->rdAmount = $amount;
												}
											}
											$amount = (float)$amount ;
											if($amount > 0) 
											{ 
												if ($amount == $this->rdAmount)
												{
													$amountSelected = true;
													$checked = ' checked="checked" ' ;
												}
												else
												{
													$checked = '' ;
												}
												?>
												<label class="amount-option">
													<input type="radio" name="rd_amount" id="pre_amount_<?php echo $i; ?>" value="<?php echo $amount; ?>" <?php echo $checked ; ?>  class="<?php echo $extraClass; ?> validate[required] radio" />
													<span><?php echo DonationHelperHtml::formatAmount($this->config, $amount);?></span>
												</label>
												<?php 
												$i++;
											}
										} 
										?>
									<?php endif; ?>
									<?php
									if($rowCampaign->display_amount_textbox == 1)
									{
										$display_amount_textbox = 0;
									}
									elseif($rowCampaign->display_amount_textbox == 2)
									{
										$display_amount_textbox = 1;
									}
									else
									{
										$display_amount_textbox = (int)$this->config->display_amount_textbox;
									}
									if ($amountSelected)
									{
										$amountCssClass = 'validate[custom[number]'.$amountValidationRules.'] ';
									}
									else
									{
										$amountCssClass = 'validate[required,custom[number]'.$amountValidationRules.'] ';
									}
									if ($display_amount_textbox == 1)
                    				{
									?>
										<label class="amount-option custom">
											<input type="radio" name="rd_amount" value="custom" />
											<span>
												<input data-errormessage="<?php echo Text::_('JD_AMOUNT_IS_REQUIRED'); ?>" data-errormessage-range-underflow="<?php echo Text::sprintf('JD_MIN_DONATION_AMOUNT_ALLOWED', $minDonationAmount); ?>" data-errormessage-range-overflow="<?php echo Text::sprintf('JD_MAX_DONATION_AMOUNT_ALLOWED', $maxDonationAmount); ?>" type="number" name="amount" class="<?php echo $amountCssClass; ?>" id="custom-amount" placeholder="Enter amount" step="1" inputmode="decimal" />
											</span>
										</label>
									<?php } ?>
                                </div>
                            </div>
                        </div>
					</div>
					<div class="step" id="step-2">
                        <div class="step-content">
							<?php
							$fields = $this->form->getFields();
							if (isset($fields['state']))
							{
								$selectedState = $fields['state']->value;
							}
							foreach ($fields as $field)
							{
								if ($field->name =='email')
								{
									if ($this->userId || !$this->config->registration_integration || !$this->allowUserRegistration)
									{
										//We don't need to perform ajax email validate in this case, so just remove the rule
										$cssClass = $field->getAttribute('class');
										$cssClass = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
										$field->setAttribute('class', $cssClass);
									}
								}
								//echo $field->getControlGroup(true, $bootstrapHelper, $field);
								?>
								<div class="smart-form-group">
									<label><?php echo $field->getFieldLabel();?></label>
									<?php echo $field->getInput($bootstrapHelper); ?>
								</div>
								<?php
							}
							
							if ($this->config->pay_payment_gateway_fee)
							{
							?>
								<div class="smart-form-group checkbox-group">
									<label class="checkbox-label">
										<input type="checkbox" name="pay_payment_gateway_fee" id="pay_payment_gateway_fee0" value="1"  class="inputbox"  onClick="updateSummary();" />
										<span class="checkmark"></span>
										<span><?php echo Text::_('JD_PAY_PAYMENT_GATEWAY_FEE'); ?></span>
									</label>
								</div>
							<?php
							}
							if ($this->config->enable_hide_donor)
							{
							?>
								<div class="smart-form-group checkbox-group">
									<label class="checkbox-label">
										<input type="checkbox" name="hide_me" id="hide_me" value="1" <?php if ($this->hideMe) echo ' checked="checked"' ; ?> />
										<span class="checkmark"></span>
										<span><?php echo Text::_('JD_MAKE_THIS_DONATION_ANONYMOUS'); ?></span>
									</label>
								</div>
							<?php
							}
							if ($this->config->enable_gift_aid)
							{
							?>
								<div class="smart-form-group checkbox-group">
									<label class="checkbox-label">
										<input type="checkbox" name="gift_aid" value="1" <?php if ($this->gift_aid) echo ' checked="checked"' ; ?> />
										<span class="checkmark"></span>
										<span><?php echo Text::_('JD_GIFT_AID_EXPLAIN'); ?></span>
									</label>
								</div>
							<?php
							}
							
							if($this->show_dedicate == 1)
							{
								?>
								<div class="row mb-4" id="dedicate_heading">
									<div class="col-12">
										<div class="form-check">
											<input type="checkbox" class="form-check-input" name="show_dedicate" id="show_dedicate" value="0" onchange="toggleDedicateSection()">
											<label class="form-check-label" for="show_dedicate">
												<?php echo Text::_('JD_HONOR_OF'); ?>
											</label>
										</div>
									</div>
								</div>

								<!-- Dedicate Details (Hidden by default) -->
								<div class="row d-none" id="honoreediv">
									<div class="col-12">
										<div class="card border-light bg-light">
											<div class="card-body">
												<!-- Dedicate Type Selection -->
												<div class="row mb-3">
													<div class="col-12">
														<label class="form-label fw-semibold mb-3">
															<?php echo Text::_('JD_DEDICATE_TYPE'); ?>
														</label>
														<div class="row g-2">
															<?php
															$dedicatetype = $this->config->dedicate_type;
															if($dedicatetype == "") {
																$dedicatetype = "1,2,3,4";
															}
															$dedicatetypeArr = explode(",", $dedicatetype);
															
															for($i=1; $i<=4; $i++) {
																if(in_array($i, $dedicatetypeArr)) {
																	?>
																	<div class="col-6">
																		<div class="form-check">
																			<input class="form-check-input" type="radio" name="dedicate_type" 
																				id="dedicate_type_<?php echo $i; ?>" value="<?php echo $i; ?>" 
																				<?php echo ($i == 1) ? 'checked' : ''; ?>>
																			<label class="form-check-label" for="dedicate_type_<?php echo $i; ?>">
																				<?php echo DonationHelper::getDedicateType($i); ?>
																			</label>
																		</div>
																	</div>
																	<?php
																}
															}
															?>
														</div>
													</div>
												</div>

												<!-- Honoree Information -->
												<div class="row">
													<!-- Honoree Name -->
													<div class="col-md-6 mb-3">
														<label class="form-label" for="dedicate_name">
															<?php echo Text::_('JD_HONOREE_NAME'); ?>
															<span class="text-danger">*</span>
														</label>
														<input type="text" class="form-control" name="dedicate_name" id="dedicate_name" 
															placeholder="<?php echo Text::_('JD_HONOREE_NAME'); ?>" 
															data-rule-required="true" data-msg-required="<?php echo Text::_('JD_HONOREE_NAME'); ?>">
														<div class="invalid-feedback"></div>
													</div>

													<!-- Honoree Email -->
													<div class="col-md-6 mb-3">
														<label class="form-label" for="dedicate_email">
															<?php echo Text::_('JD_HONOREE_EMAIL'); ?>
														</label>
														<input type="email" class="form-control" name="dedicate_email" id="dedicate_email" 
															placeholder="<?php echo Text::_('JD_HONOREE_EMAIL'); ?>"
															data-rule-email="true" data-msg-email="<?php echo Text::_('JD_HONOREE_EMAIL'); ?>">
														<div class="invalid-feedback"></div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>
                        </div>
                    </div>

					<div class="step" id="step-3" >
                        <div class="step-content">
                            <div class="smart-form-group">
                                <label><?php echo Text::_('JD_SELECT_PAYMENT_OPTION');?></label>
                                <div class="payment-methods">
									<?php
									$method = null ;
									$message = [];
									$paymentMethodsData = array();
									$j      = 0;
									for ($i = 0 , $n = count($this->methods); $i < $n; $i++)
									{
										$paymentMethod = $this->methods[$i];

										if ($paymentMethod->getName() == $this->paymentMethod)
										{
											$checked = ' checked="checked" ';
											$method = $paymentMethod ;
											$display = "block;";
										}
										else
										{
											$checked = '';
											$display = "none;";
										}

										$tmp = '<div class="payment-form" id="'.$paymentMethod->getName().'_message" style="display: '.$display.'">';
										$tmp .= os_jdpayments::returnPaymentMethodMessage($paymentMethod->getName());
										$tmp .= '</div>';
										$message[] = $tmp;

										if (strpos($paymentMethod->getName(), 'os_stripe') !== false)
										{
											$stripePaymentMethod = $paymentMethod;
										}
										elseif (strpos($paymentMethod->getName(), 'os_squarecard') !== false)
										{
											$hasSquareCard = true;
										}

										$payment_name = $paymentMethod->getName();
										$payment_name = str_replace("os_","",$payment_name);

										$method       = os_jdpayments::getPaymentMethod($paymentMethod->getName());
										if($method->getEnableRecurring() == 1)
										{
											$recurring = "true";
										}
										else
										{
											$recurring = "false";
										}

										$paymentMethodsData[] = array(
											'name' => $paymentMethod->getName(),
											'title' => Text::_($paymentMethod->getTitle())
										);
										?>
										<label class="payment-method" data-supports-recurring="<?php echo $recurring;?>">
											<input id="payment_gateway_<?php echo $i;?>" type="radio" name="payment_method" onclick="changePaymentMethod();" value="<?php echo $paymentMethod->getName(); ?>" <?php echo $checked; ?> />
											<?php
											if((int)$this->config->show_payment_method == 0)
											{
												if($paymentMethod->icon != "")
												{
													?><span class="payment-icon <?php echo $paymentMethod->icon; ?>" style="width:30px;"></span><?php 
												}
												else
												{
												?>
													<span class="smart-payment-icon">
														<?php
														
															if(file_exists(JPATH_ROOT.'/media/com_jdonation/assets/images/payments_override/'.$payment_name.'.png'))
															{
																?>
																<img src="<?php echo Uri::base(true)?>/media/com_jdonation/assets/images/payments_override/<?php echo $payment_name?>.png" style="height:30px;" alt="<?php echo Text::_($paymentMethod->getTitle()); ?>"/>
																<?php
															}
															elseif(file_exists(JPATH_ROOT.'/media/com_jdonation/assets/images/payments/'.$payment_name.'.png'))
															{
																?>
																<img src="<?php echo Uri::base(true)?>/media/com_jdonation/assets/images/payments/<?php echo $payment_name?>.png" style="height:30px;" alt="<?php echo Text::_($paymentMethod->getTitle()); ?>"/>
																<?php
															}
														
														?>
													</span>
												<?php
												}

											}
											?>
											<span><?php echo Text::_($paymentMethod->getTitle()); ?></span>
										</label>

										<?php
									}
									?>
								</div>
								<?php
								foreach($message as $msg)
								{
									echo $msg;
								}
								?>
								<?php
								if ($method->getName() == 'os_squareup')
									{
										$style = '';
									}
									else
									{
										$style = 'style = "display:none"';
									}
									?>
									<div class="<?php echo $controlGroupClass;?> payment_information" id="sq_field_zipcode" <?php echo $style; ?>>
										<label class="<?php echo $controlLabelClass;?>" for="sq_billing_zipcode">
											<?php echo Text::_('JD_SQUAREUP_ZIPCODE'); ?><span class="required">*</span>
										</label>

										<div class="<?php echo $controlsClass;?>">
											<div id="field_zip_input">
												<input type="text" id="sq_billing_zipcode" name="sq_billing_zipcode" class="<?php echo $inputLargeClass;?>" value="<?php echo $this->escape($this->input->getString('sq_billing_zipcode')); ?>" />
											</div>
										</div>
									</div>
									<?php
									if ($method->getCreditCard())
									{
										$style = '' ;
									}
									else
									{
										$style = 'style = "display:none"';
									}
									?>
									<div id="creditcarddivmain" class="payment-details" <?php echo $style; ?>>
										<h3>Credit Card Information</h3>

										<div class="smart-form-group" id="tr_card_number" <?php echo $style; ?>>	
											<label for="x_card_num"><?php echo  Text::_('JD_CARD_NUMBER'); ?></label>
											<div id="sq-card-number">
												<input type="text" name="x_card_num" id="x_card_num" class="form-control validate[required,creditCard] width100" value="<?php echo $this->input->get('x_card_num', '', 'none'); ?>" size="20" data-input-mask="0000 0000 0000 0000" placeholder="<?php echo  Text::_('AUTH_CARD_NUMBER'); ?>"/>
											</div>
										</div>
										<div class="form-row">
											<div class="smart-form-group" id="tr_exp_date" <?php echo $style; ?>>
												<label for="expiry_date">
													<?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?>
												</label>
												
												<div id="sq-expiration-date">
													<input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YY" class="form-control validate[required] width100" data-input-mask="00/00" placeholder="<?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?>"/>
												</div>
											</div>
											<div class="smart-form-group" id="tr_cvv_code" <?php echo $style; ?>>
												<label for="x_card_code">
													<?php echo Text::_('JD_CVV'); ?>
												</label>
												
												<div id="sq-cvv">
													<input type="text" name="x_card_code" id="x_card_code" class="form-control validate[required,minSize[3],maxSize[4],custom[onlyNumberSp]] width100" value="<?php echo $this->input->get('x_card_code', '', 'none'); ?>" size="20" data-input-mask="0000" placeholder="<?php echo Text::_('AUTH_CVV_CODE'); ?>"/>
												</div>
											</div>
										</div>
													
										<?php
												
										if ($method->getCardHolderName())
										{
										?>
											<div class="smart-form-group" id="tr_card_holder_name" <?php echo $style; ?>>
												<label class="card_holder_name">
													<?php echo Text::_('JD_HOLDER_NAME'); ?>
												</label>
												<input type="text" name="card_holder_name" id="card_holder_name" class="form-control validate[required] width100"  value="<?php echo $this->input->get('card_holder_name', '', 'none'); ?>" size="40" placeholder="<?php echo Text::_('JD_CARD_HOLDER_NAME'); ?>"/>
											</div>
										<?php
										}	
										?>
												
										<?php
										if ($method->getCardHolderName())
										{
											$style = '' ;
										}
										else
										{
											$style = ' style = "display:none;" ' ;
										}
										?>
										<div class="smart-form-group" id="tr_card_holder_name" <?php echo $style; ?>>
											
										</div>

										<?php
										$sisowEnabled = os_jdpayments::sisowEnabled();
										if ($sisowEnabled) {
											os_jdpayments::getBankLists();
										}
										?>
										<?php
										if (DonationHelper::isPaymentMethodEnabled('os_echeck'))
										{
											if ($method->getName() == 'os_echeck')
											{
												$style = '';
											}
											else
											{
												$style = ' style = "display:none;" ';
											}
											?>
											<div class="smart-form-group" id="tr_bank_rounting_number" <?php echo $style; ?>>
												<label class="<?php echo $controlLabelClass;?>"><?php echo Text::_('JD_BANK_ROUTING_NUMBER'); ?><span class="required">*</span></label>

												<div class="<?php echo $controlsClass;?>"><input type="text" name="x_bank_aba_code" class="<?php echo $inputLargeClass;?> validate[required,custom[number]]" value="<?php echo $this->input->get('x_bank_aba_code', '', 'none'); ?>" size="40"/></div>
											</div>
											<div class="smart-form-group" id="tr_bank_account_number" <?php echo $style; ?>>
												<label class="<?php echo $controlLabelClass;?>"><?php echo Text::_('JD_BANK_ACCOUNT_NUMBER'); ?><span class="required">*</span></label>

												<div class="<?php echo $controlsClass;?>"><input type="text" name="x_bank_acct_num" class="<?php echo $inputLargeClass;?> validate[required,custom[number]]" value="<?php echo $this->input->get('x_bank_acct_num', '', 'none');; ?>" size="40"/></div>
											</div>
											<div class="smart-form-group" id="tr_bank_account_type" <?php echo $style; ?>>
												<label class="<?php echo $controlLabelClass;?>"><?php echo Text::_('JD_BANK_ACCOUNT_TYPE'); ?><span class="required">*</span></label>

												<div class="<?php echo $controlsClass;?>"><?php echo $this->lists['x_bank_acct_type']; ?></div>
											</div>
											<div class="smart-form-group" id="tr_bank_name" <?php echo $style; ?>>
												<label class="<?php echo $controlLabelClass;?>"><?php echo Text::_('JD_BANK_NAME'); ?><span class="required">*</span></label>

												<div class="<?php echo $controlsClass;?>"><input type="text" name="x_bank_name" class="<?php echo $inputLargeClass;?> validate[required]" value="<?php echo $this->input->get('x_bank_name', '', 'none'); ?>" size="40"/></div>
											</div>
											<div class="smart-form-group" id="tr_bank_account_holder" <?php echo $style; ?>>
												<label class="<?php echo $controlLabelClass;?>"><?php echo Text::_('JD_ACCOUNT_HOLDER_NAME'); ?><span class="required">*</span></label>
												<div class="<?php echo $controlsClass;?>"><input type="text" name="x_bank_acct_name" class="<?php echo $inputLargeClass;?> validate[required]" value="<?php echo $this->input->get('x_bank_acct_name', '', 'none'); ?>" size="40"/></div>
											</div>
											<?php
										}
										?>
									</div>
									<?php
									if ($stripePaymentMethod !== null && method_exists($stripePaymentMethod, 'getParams'))
									{
										/* @var os_stripe $stripePaymentMethod */
										$params = $stripePaymentMethod->getParams();
										$useStripeCardElement = true;

										if ($useStripeCardElement)
										{
											if ($method->getName() === 'os_stripe')
											{
												$style = '';
											}
											else
											{
												$style = ' style = "display:none;" ';
											}
											?>
											<div class="smart-form-group payment_information" style="display:none;" id="stripe-card-form">
												<label class="form-control-label" for="stripe-card-element">
													<?php echo Text::_('JD_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
												</label>
												<div id="stripe-card-element">

												</div>
											</div>
											<?php
										}
									}
									if ($hasSquareCard)
									{
										if (strpos($method->getName(), 'os_squarecard') !== false)
										{
											$style = '';
										}
										else
										{
											$style = ' style = "display:none;" ';
										}
										?>
										<div class="smart-form-group payment_information" <?php echo $style; ?> id="square-card-form">
											<div class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
											</div>
											<div class="<?php echo $controlsClass; ?>" id="square-card-element">

											</div>
										</div>
										<input type="hidden" name="square_card_token" value="" />
										<input type="hidden" name="square_card_verification_token" value="" />
										<?php
									}
									?>
                            </div>
                        </div>
					</div>
					<div class="step" id="step-4">
                        <div class="step-content">
                            <h2><?php echo Text::_('JD_CONFIRM_YOUR_DONATION'); ?></h2>
                            
                            <div class="summary-section">
                                <h3><?php echo Text::_('JD_DONATION_SUMMARY'); ?></h3>
                                <div class="summary-item">
                                    <span class="summary-label"><?php echo Text::_('JD_DONATION_TYPE'); ?>:</span>
                                    <span class="summary-value" id="summary-frequency"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><?php echo Text::_('JD_AMOUNT'); ?>:</span>
                                    <span class="summary-value" id="summary-amount"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><?php echo Text::_('JD_PAYMENT_METHOD'); ?>:</span>
                                    <span class="summary-value" id="summary-payment"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><?php echo Text::_('JD_DONOR'); ?>:</span>
                                    <span class="summary-value" id="summary-donor"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><?php echo Text::_('JD_EMAIL'); ?>:</span>
                                    <span class="summary-value" id="summary-email"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><strong><?php echo Text::_('JD_TOTAL'); ?>:</strong></span>
                                    <span class="summary-value" id="summary-total"><strong></strong></span>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
							<?php
							if ($this->config->accept_term ==1 && $this->config->article_id > 0)
                    		{
								$articleId = $this->config->article_id;

								if (Multilanguage::isEnabled())
								{
									$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleId);
									$langCode     = Factory::getApplication()->getLanguage()->getTag();

									if (isset($associations[$langCode]))
									{
										$article = $associations[$langCode];
									}
								}

								
								if (!isset($article))
								{
									
									$query = $db->getQuery(true);
									$query->select('id, catid')
										->from('#__content')
										->where('id = ' . (int) $articleId);
									$db->setQuery($query);
									$article = $db->loadObject();
								}
								$extra = ' class="jd-modal" ' ;

								$termLink = RouteHelper::getArticleRoute($article->id, $article->catid);
								$termLink .=  '&tmpl=component&format=html';
								?>
								<div class="terms-container" id="div_accept_term">
									<label class="checkbox-label">
										<input type="checkbox" name="accept_term" id="accept_term" class="validate[required]">
										<span class="checkmark"></span>
										<?php echo Text::_('JD_ACCEPT'); ?>&nbsp;
										 <?php
											echo "<a $extra href=\"".Route::_($termLink)."\">"."<strong>".Text::_('JD_TERM_AND_CONDITION')."</strong>"."</a>\n";
										?>
									</label>
								</div>
							<?php
							}

							if ($this->config->show_privacy)
							{
								if ($this->config->privacy_policy_article_id > 0)
								{
									$privacyArticleId = $this->config->privacy_policy_article_id;

									if (Multilanguage::isEnabled())
									{
										$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $privacyArticleId);
										$langCode     = Factory::getApplication()->getLanguage()->getTag();
										if (isset($associations[$langCode]))
										{
											$privacyArticle = $associations[$langCode];
										}
									}

									if (!isset($privacyArticle))
									{
										$query = $db->getQuery(true);
										$query->select('id, catid')
											->from('#__content')
											->where('id = ' . (int) $privacyArticleId);
										$db->setQuery($query);
										$privacyArticle = $db->loadObject();
									}

									$link = RouteHelper::getArticleRoute($privacyArticle->id, $privacyArticle->catid);
									$link .=  '&tmpl=component&format=html';
									$extra = ' jd-modal' ;
								}
								else
								{
									$link = '';
								}
								?>
								<div class="privacy-policy-section">
									<label class="checkbox-label">
										<a href="<?php echo $link; ?>" class="privacy-policy-link <?php echo $extra; ?>">
											<?php
											echo Text::_('JD_PRIVACY_POLICY');
											?>
										</a>
									</label>
									<div class="privacy-policy-content">
										<div class="checkbox-container" id="div_agree_privacy_policy">
											<label for="agree_privacy_policy" class="checkbox-label">
												<input type="checkbox" 
												id="agree_privacy_policy" 
												name="agree_privacy_policy" 
												value="1" 
												class="validate[required]" />
												<span class="checkmark"></span>
												<?php echo Text::_('JD_AGREE_POLICY');?>
											</label>
										</div>
										<div class="privacy-message">
											<div class="alert-info">
												<i class="info-icon">ℹ️</i>
												<?php echo Text::_('JD_PLEASE_READ_AND_ACCEPT_PRIVACY_POLICY'); ?>
											</div>
										</div>
										<div class="error-message" id="privacy-error" style="display: none;">
											<?php echo Text::_('JD_PLEASE_AGREE_TO_THE_POLICY_TO_PROCEED'); ?>
										</div>
									</div>
								</div>

								<?php
							}
							?>
                            <!-- Newsletter Subscription -->
							<?php
							if ($this->config->show_newsletter_subscription == 1 && DonationHelper::isNewsletterPluginEnabled()){
                        	?>
								<div class="checkbox-group">
									<label class="checkbox-label">
										<input type="checkbox" name="newsletter_subscription" id="newsletter_subscription" value="1" />
										<span class="checkmark"></span>
										<?php echo Text::_('JD_SUBSCRIBE_TO_NEWSLETTER');?>
									</label>
								</div>
							<?php
							}


							if($this->showCaptcha)
							{
								$shouldShowCaptcha = false;
								
								if($this->config->enable_captcha_with_public_user == 1)
								{
									$shouldShowCaptcha = !$this->userId;
								}
								elseif($this->config->enable_captcha_with_public_user == 0)
								{
									$shouldShowCaptcha = true;
								}
								
								if($shouldShowCaptcha)
								{
									
									?>
									<div class="modern-captcha-wrapper <?php echo $controlGroupClass;?>" role="group" aria-labelledby="captcha-title">
										<!-- Captcha Card Container -->
										<div class="captcha-card">
											<!-- Header Section -->
											<div class="captcha-header">
												<h3 id="captcha-title" class="captcha-title">
													<svg class="captcha-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
														<path d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.4,7 14.8,8.6 14.8,10.5V11.5C15.4,11.5 16,12.4 16,13V16C16,17.4 15.4,18 14.8,18H9.2C8.6,18 8,17.4 8,16V13C8,12.4 8.6,11.5 9.2,11.5V10.5C9.2,8.6 10.6,7 12,7M12,8.2C11.2,8.2 10.5,8.7 10.5,10.5V11.5H13.5V10.5C13.5,8.7 12.8,8.2 12,8.2Z"/>
													</svg>
													<?php echo Text::_('JD_SECURITY_VERIFICATION'); ?>
												</h3>
												<p class="captcha-subtitle">
													<?php echo Text::_('JD_PLEASE_COMPLETE_THE_VERIFICATION_BELOW_TO_CONTINUE'); ?>
												</p>
											</div>

											<!-- Content Section - Plugin Content Goes Here -->
											<div class="captcha-content-area">													
												<!-- Captcha Plugin Output -->
												<div class="captcha-plugin-container">
													<?php echo $this->captcha; ?>
												</div>
											</div>
										</div>
									</div>
									<?php
									
								}
								elseif ($this->captchaPlugin == 'recaptcha_invisible')
								{
									?>
									<div class="captcha-container captcha-invisible" style="display: none;">
										<?php echo $this->captcha; ?>
									</div>
									<?php
								}
							}
							?>
                        </div>
					</div>
				</div>
				<div class="step-buttons">
                    <button type="button" class="btn btn-secondary btn-prev" style="display: none;"><?php echo Text::_('JD_PREVIOUS'); ?></button>
                    <button type="button" class="btn btn-primary btn-next"><?php echo Text::_('JD_NEXT_BTN'); ?></button>
                    <button type="button" class="btn btn-primary btn-submit" style="display: none;"><?php echo Text::_('JD_COMPLETE_DONATION'); ?></button>
				</div>
		</div>

                
		<?php
		if (count($this->methods) == 1)
		{
		?>
			<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>" />
		<?php
		}
		
		if (!$this->showCampaignSelection)
		{
		?>
			<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $this->campaignId; ?>" />
		<?php
		}
		
		?>
		<input type="hidden" name="donation_type" id="donation_type" value="onetime" />
		<input type="hidden" name="validate_form_login" value="<?php echo $validateLoginForm; ?>" />
		<input type="hidden" name="receive_user_id" value="<?php echo $this->input->getInt('receive_user_id'); ?>" />
		<input type="hidden" name="field_campaign" value="<?php echo $this->config->field_campaign; ?>" />
		<input type="hidden" name="amount_by_campaign" value="<?php echo $this->config->amount_by_campaign; ?>" />
		<input type="hidden" name="enable_recurring" value="<?php echo $this->config->enable_recurring; ?>" />
		<input type="hidden" name="count_method" value="<?php echo count($this->methods); ?>" />
		<input type="hidden" name="current_campaign" value="<?php echo $this->campaignId; ?>" />
		<input type="hidden" name="donation_page_url" value="<?php echo $this->donationPageUrl; ?>" />
		<input type="hidden" id="card-nonce" name="nonce" />
		<input type="hidden" name="task" value="donation.process" />
		<input type="hidden" name="smallinput" id="smallinput" value="<?php echo $inputSmallClass; ?>" />
		<input type="hidden" name="activate_dedicate" id="activate_dedicate" value="<?php echo $this->show_dedicate;?>" />
		<input type="text"   name="jd_my_own_website_name" value="" autocomplete="off" class="jd_invisible_to_visitors" />
		<input type="hidden" name="<?php echo DonationHelper::getHashedFieldName(); ?>" value="<?php echo time(); ?>" />
		<?php
		if (!$show_currency_selection || $this->campaign->currency != "")
		{
			if($this->campaign->currency != "")
			{
				$this->config->currency = $this->campaign->currency;
			}
			?>
			<input type="hidden" name="currency_code" id="currency_code" value="<?php echo $this->config->currency; ?>" />
			<?php
		}
		?>
		<!-- Version 5.6.13 -->
		<?php
		if(count($this->rowCampaigns))
		{
			foreach($this->rowCampaigns as $campaign)
			{
				?>
				<input type="hidden" name="curr_<?php echo $campaign->id; ?>" id="curr_<?php echo $campaign->id; ?>" value="<?php echo DonationHelper::getCurrencyName($campaign->currency); ?>" />
				<?php
			}
		}
		?>
		<?php echo HTMLHelper::_( 'form.token' ); ?>
	</form>
	<?php
            
    }
    else
    {
        ?>
        <div class="<?php echo $rowFluidClass?>">
            <div class="<?php echo $span12Class;?> campaigndescription" id="donation_form">
                <h3>
                    <?php echo Text::_('JD_DISABLE_DONATION');?>
                </h3>
                <?php
                echo Text::_('JD_REASON').": ".$msg;
                ?>
            </div>
        </div>
        <?php
    }
    ?>

<script type="text/javascript">
var paymentMethods = <?php echo json_encode($paymentMethodsData); ?>;
<?php echo os_jdpayments::writeJavascriptObjects() ; ?>
(function (document, $) {
    $(document).ready(function () {
        // This is here for backward compatible purpose        
        JDMaskInputs(document.getElementById('os_form'));
    });
})(document, jQuery);
</script>
