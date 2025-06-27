<?php

/**
 * @version        5.5.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Dang Thuc Dam
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;
 
HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
$editor					= Editor::getInstance(Factory::getApplication()->getConfig()->get('editor'));
$translatable			= Multilanguage::isEnabled() && count($this->languages);
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span5Class				= $bootstrapHelper->getClassMapping('span5');
$controlGroupClass		= $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass		= $bootstrapHelper->getClassMapping('control-label');
$controlsClass			= $bootstrapHelper->getClassMapping('controls');
if (version_compare(JVERSION, '4.0.0-dev', 'lt'))
{
	HTMLHelper::_('behavior.tabstate');
}
?>
<script type="text/javascript">
	function changeValue(itemid){
		var temp = document.getElementById(itemid);
		if(temp.value == 0){
			temp.value = 1;
		}else{
			temp.value = 0;
		}
	}
	Joomla.submitbutton = function(pressbutton) 
	{
		var form = document.adminForm;
		if (pressbutton == 'cancel')
	    {
			Joomla.submitform( pressbutton );
			return;				
		}
	    else
	    {		    
	        <?php
	            $fields = array('description', 'user_email_subject', 'user_email_body', 'recurring_email_body', 'donation_form_msg', 'confirmation_message', 'thanks_message', 'cancel_message');
	            foreach($fields as $field)
	            {
	                //echo $editor->save($field);
	            }
	        ?>
	        Joomla.submitform(pressbutton);
		}
	}
</script>
<?php
if (DonationHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';

	Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
}
else
{
	$tabApiPrefix = 'bootstrap.';

	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}
?>
<form class="form-horizontal" action="index.php?option=com_jdonation&view=campaign" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<?php
		echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'campaign', array('active' => 'general-page'));
			echo HTMLHelper::_($tabApiPrefix.'addTab', 'campaign', 'general-page', Text::_('JD_GENERAL', true));
			?>
			<div class="<?php echo $rowFluidClass;?>">
				<div class="<?php echo $span7Class; ?>">
					<fieldset class="general form-horizontal options-form">
						<legend><?php echo Text::_( 'JD_GENERAL' ); ?></legend>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo  Text::_('JD_TITLE'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="text_area form-control" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $this->item->title;?>" />
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo  Text::_('JD_ALIAS'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="text_area form-control" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->item->alias;?>" size="50" />
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo  Text::_('JD_CATEGORY'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo $this->lists['category_id']; ?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_PHOTO');?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php
								if($this->item->campaign_photo != "" && file_exists(JPATH_ROOT.'/'.$this->item->campaign_photo))
								{
									//$this->item->campaign_photo = str_replace("images/jdonation/","", $this->item->campaign_photo);
									//do nothing
								}
								else
								{
									$this->item->campaign_photo = "";
								}

								//$this->item->campaign_photo = "images/jdonation/".$this->item->campaign_photo;

								?>
								<?php echo DonationHelperHtml::getMediaInput($this->item->campaign_photo, 'campaign_photo', null); ?>								
								<!--<input type="file" name="photo" id="photo" class="form-control input-large ilarge"/>-->
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_SHORT_DESCRIPTION') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo $editor->display( 'short_description',  $this->item->short_description , '100%', '200', '75', '5' ) ;?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_DESCRIPTION') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo $editor->display( 'description',  $this->item->description , '100%', '300', '75', '8' ) ;?>
							</div>
						</div>
					</fieldset>
					<fieldset class="meta-setting form-horizontal options-form">
						<legend><?php echo Text::_( 'JD_META_INFORMATION' ); ?></legend>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_INHERIT_MENU_METADATA'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php
								echo DonationHelperHtml::showCheckboxfield('use_parameter',(int)$this->item->use_parameter);
								?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JFIELD_META_KEYWORDS_LABEL'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<textarea name="meta_keywords" id="meta_keywords" cols="50" rows="3" style="width:100% !important;" class="form-control imedium"><?php echo $this->item->meta_keywords; ?></textarea>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JFIELD_META_DESCRIPTION_LABEL'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<textarea name="meta_description" id="meta_description" cols="50" style="width:100% !important;" rows="3" class="form-control imedium"><?php echo $this->item->meta_description; ?></textarea>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_BROWSER_PAGE_TITLE'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="form-control input-large imedium" type="text" style="width:100% !important;" name="browser_page_title" id="browser_page_title" value="<?php echo $this->item->browser_page_title;?>" />
							</div>
						</div>
					</fieldset>
					<fieldset class="layout-setting form-horizontal options-form">
						<legend><?php echo Text::_( 'JD_LAYOUT_SETTING' ); ?></legend>					
						
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_HIGHLIGHT_COLOR'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="form-control input-large imedium color {required:false}" type="text" name="highlight_color" id="highlight_color" value="<?php echo ($this->item->highlight_color != '') ? $this->item->highlight_color : '#FE9301';?>" style="background-color:<?php echo ($this->item->highlight_color != '') ? $this->item->highlight_color : '#FE9301';?>;"/>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_BORDER_HIGHLIGHT_COLOR'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="form-control input-large imedium color {required:false}" type="text" name="border_highlight_color" id="border_highlight_color" value="<?php echo ($this->item->border_highlight_color != '') ? $this->item->border_highlight_color : '#EB5901';?>" style="background-color:<?php echo ($this->item->border_highlight_color != '') ? $this->item->border_highlight_color : '#EB5901';?>;color:#FFF;"/>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_PROGRESS_BAR_BACKGROUND_COLOR'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="form-control input-large imedium color {required:false}" type="text" name="progress_color" id="progress_color" value="<?php echo ($this->item->progress_color != '') ? $this->item->progress_color : '#0e90d2';?>" style="background-color:<?php echo ($this->item->progress_color != '') ? $this->item->progress_color : '#0e90d2';?>;color:#FFF;"/>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_PROGRESS_BAR_GRADIENT_COLOR'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>" style="display:flex;">
								<input class="form-control input-small imini color {required:false}" type="text" name="gradient_progress_color" id="gradient_progress_color" value="<?php echo ($this->item->gradient_progress_color != '') ? $this->item->gradient_progress_color : '#149bdf';?>" style="background-color:<?php echo ($this->item->gradient_progress_color != '') ? $this->item->gradient_progress_color : '#149bdf';?>;color:#FFF;"/>
								&nbsp;-&nbsp;
								<input class="form-control input-small imini color {required:false}" type="text" name="gradient_progress_color1" id="gradient_progress_color1" value="<?php echo ($this->item->gradient_progress_color1 != '') ? $this->item->gradient_progress_color1 : '#0480be';?>" style="background-color:<?php echo ($this->item->gradient_progress_color1 != '') ? $this->item->gradient_progress_color1 : '#0480be';?>;color:#FFF;"/>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="<?php echo $span5Class;?>">
					<fieldset class="other-information form-horizontal options-form">
						<legend><?php echo Text::_( 'JD_OTHER_INFORMATION' ); ?></legend>
						<?php
						if($this->item->id > 0 && $this->item->goal > 0)
						{
						?>
							<div class="campaign-progress-box">
								<div class="progress-circle">
									<svg width="90" height="90">
									<circle cx="45" cy="45" r="40" stroke="#e0e7ef" stroke-width="9" fill="none"/>
									<circle id="progress-bar" cx="45" cy="45" r="40" stroke="#1976d2" stroke-width="9" fill="none"
										stroke-linecap="round"
										stroke-dasharray="251.2"
										stroke-dashoffset="0"/>
									</svg>
									<div class="progress-value" id="progress-value">0%</div>
								</div>
								<div class="progress-info">
									<div class="amount"><span id="current-amount">$0</span> / <span id="goal-amount">$0</span></div>
									<div class="goal"><?php echo Text::_('JD_DONATED'); ?> / <?php echo Text::_('JD_GOAL'); ?></div>
									<div class="goal" id="donate-count">(0 <?php echo Text::_('JD_DONATIONS'); ?>)</div>
								</div>
							</div>
						<?php
						}
						?>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_START_DATE'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo HTMLHelper::_('calendar', $this->item->start_date, 'start_date', 'start_date') ; ?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_END_DATE'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo HTMLHelper::_('calendar', $this->item->end_date, 'end_date', 'end_date') ; ?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_GOAL'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="input-mini  form-control" type="text" name="goal" id="goal" size="5" maxlength="250" value="<?php echo $this->item->goal;?>" />
							</div>
						</div>
						<?php
							if($this->item->goal > 0)
							{
								?>
								<div class="<?php echo $controlGroupClass; ?>">
									<label class="<?php echo $controlLabelClass; ?>">
										<?php echo Text::_('JD_SHOW_CAMPAIGN_GOAL') ; ?>
									</label>
									<div class="<?php echo $controlsClass; ?>">
										<?php
										echo DonationHelperHtml::showCheckboxfield('show_goal',(int)$this->item->show_goal);
										?>
									</div>
								</div>
								<?php
							}
							if ($this->item->id)
							{
							?>
							<div class="<?php echo $controlGroupClass; ?>">
								<label class="<?php echo $controlLabelClass; ?>">
									<?php echo Text::_('JD_DONATED_AMOUNT'); ?>
								</label>
								<div class="<?php echo $controlsClass; ?>">
									<?php echo number_format($this->item->donated_amount , 1); ?>
								</div>
							</div>
							<?php
							}
						?>

						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_DONATION_TYPE') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo $this->lists['donation_type'] ; ?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo DonationHelperHtml::getFieldLabel('recurring_frequencies', Text::_('JD_RECURRING_FREQUENCIES'), Text::_('JD_RECURRING_FREQUENCIES_EXPLAIN').' '.Text::_('JD_LEAVE_FIELD_EMPTY_TO_INHERIT')); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo DonationHelper::getChoicesJsSelect($this->lists['recurring_frequencies']) ; ?>
								<div class="clearfix"></div>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_DISABLE_DONATION_WHEN_THIS_CAMPAIGN_HAS'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="input-mini form-control" type="text" name="limit_donors" id="limit_donors" size="5" maxlength="250" value="<?php echo (int) $this->item->limit_donors;?>" />
								<?php
								echo Text::_('JD_DONATIONS');
								echo ".";
								echo Text::_('JD_LEAVE_ZERO_OR_EMPTY_TO_PASS_THIS_CONFIG_OPTION');
								?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_MINIMUM_DONATION_AMOUNT'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="input-mini  form-control" type="text" name="min_donation_amount" id="min_donation_amount" size="5" maxlength="250" value="<?php echo (int)$this->item->min_donation_amount;?>" />
								<?php echo Text::_('JD_LEAVE_ZERO_OR_EMPTY_TO_PASS_THIS_CONFIG_OPTION'); ?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_LEAVE_ZERO_OR_EMPTY_TO_PASS_THIS_CONFIG_OPTION'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="input-mini  form-control" type="text" name="max_donation_amount" id="max_donation_amount" size="5" maxlength="250" value="<?php echo (int)$this->item->max_donation_amount;?>" />
								<?php echo Text::_('JD_LEAVE_ZERO_OR_EMPTY_TO_PASS_THIS_CONFIG_OPTION'); ?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_OWNER') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?> campaign_owner">
								<?php echo DonationHelper::getUserInput($this->item->user_id) ; ?>
								<?php
								if($this->item->user_id > 0){
									?>
									<input type="checkbox" name="remove_owner" id="remove_owner" value="0" onchange="javascript:changeValue('remove_owner');"/> <?php echo Text::_('JD_REMOVE_OWNER');?>
									<?php
								}
								?>
							</div>
						</div>
					
					
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_SHOW_CAMPAIGN_INFO') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo $this->lists['show_campaign'] ; ?>
							</div>
						</div>
						
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_FROM_NAME'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="form-control w-100" type="text" name="from_name" id="from_name" value="<?php echo $this->item->from_name;?>" />
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_FROM_EMAIL'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="form-control w-100" type="text" name="from_email" id="from_email" value="<?php echo $this->item->from_email;?>" />
							</div>
						</div>
						<?php
						if($this->config->activate_tributes){
							?>
							<div class="<?php echo $controlGroupClass; ?>">
								<label class="<?php echo $controlLabelClass; ?>">
									<?php echo Text::_('JD_ACTIVATE_TRIBUTES') ; ?>
								</label>
								<div class="<?php echo $controlsClass; ?>">
									<?php
									echo DonationHelperHtml::showCheckboxfield('activate_dedicate',(int)$this->item->activate_dedicate);
									?>
								</div>
							</div>
							<?php
						}
						?>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_PRIVATE_CAMPAIGN') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php
								echo DonationHelperHtml::showCheckboxfield('private_campaign',(int)$this->item->private_campaign);
								?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_PUBLISHED') ; ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php
								echo DonationHelperHtml::showCheckboxfield('published',(int)$this->item->published);
								?>
							</div>
						</div>
						<div class="<?php echo $controlGroupClass; ?>">
							<div class="<?php echo $controlLabelClass; ?>">
								<?php echo Text::_('JD_ACCESS'); ?>
							</div>
							<div class="<?php echo $controlsClass; ?>">
								<?php
								echo $this->lists['access'];
								?>
							</div>
						</div>
					</fieldset>											
				</div>
			</div>
			<?php
			echo HTMLHelper::_($tabApiPrefix.'endTab');
			echo HTMLHelper::_($tabApiPrefix.'addTab', 'campaign', 'message-page', Text::_('JD_MESSAGES', true));
			?>
			<div class="tab-pane">
				<fieldset class="amount-options form-horizontal options-form">
					<legend><?php echo Text::_( 'JD_MANAGE_SUGGESTED_DONATION_AMOUNTS' ); ?></legend>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_DISPLAY_AMOUNT_TEXTBOX'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['display_amount_textbox'];
							?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_SHOW_PRE_DEFINED_AMOUNT'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['show_amounts'];
							?>
						</div>
					</div>	
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_PRE_DEFINED_AMOUNT'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<textarea name="amounts" cols="40" rows="5" class="form-control"><?php echo $this->item->amounts;?></textarea>
						</div>
					</div>			
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_AMOUNTS_EXPLANTION'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<textarea name="amounts_explanation" cols="40" rows="5" class="form-control"><?php echo $this->item->amounts_explanation;?></textarea>
						</div>
					</div>
				</fieldset>
				<fieldset class="emails-options form-horizontal options-form">
					<legend><?php echo Text::_( 'JD_EMAILS_MESSAGE_SETTING' ); ?></legend>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_NOTIFICATION_EMAILS'); ?> <br />
							<small>Comma separated</small>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input type="text" name="notification_emails" class="form-control input-large ilarge" value="<?php echo $this->item->notification_emails; ?>" size="50" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_REPLY_EMAIL'); ?> <br />							
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input type="text" name="reply_email" class="form-control input-large ilarge" value="<?php echo $this->item->reply_email; ?>" size="50" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_USER_EMAIL_SUBJECT'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input type="text" name="user_email_subject" class="input-large form-control ilarge" value="<?php echo $this->item->user_email_subject; ?>" size="50" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_USER_EMAIL_BODY'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $editor->display( 'user_email_body',  $this->item->user_email_body , '100%', '300', '75', '8' ) ;?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $editor->display( 'user_email_body_offline',  $this->item->user_email_body_offline , '100%', '300', '75', '8' ) ;?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_SUBJECT'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">

							<input type="text" name="recurring_email_subject" class="form-control input-large ilarge" value="<?php echo $this->item->recurring_email_subject; ?>" size="50" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_BODY'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $editor->display( 'recurring_email_body',  $this->item->recurring_email_body , '100%', '300', '75', '8' ) ;?>
						</div>
					</div>					
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_DONATION_PAGE_MESSAGE'); ?>
							<br />
							<small>The message displayed above the donation form</small>					
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $editor->display( 'donation_form_msg',  $this->item->donation_form_msg , '100%', '300', '75', '8' ) ;?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_THANKYOU_MESSAGE'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $editor->display( 'thanks_message',  $this->item->thanks_message , '100%', '300', '75', '8' ) ;?>
						</div>
					</div>
				</fieldset>
			</div>
			<?php
			echo HTMLHelper::_($tabApiPrefix.'endTab');
			echo HTMLHelper::_($tabApiPrefix.'addTab', 'campaign', 'payment-page', Text::_('JD_PAYMENT'));
			?>
			<fieldset class="form-horizontal options-form">
				<legend><?php echo Text::_( 'JD_PAYMENT_INFORMATION' ); ?></legend>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo DonationHelperHtml::getFieldLabel('payment_methods', Text::_('JD_PAYMENT_METHODS'), Text::_('JD_PAYMENT_METHODS_EXPLAIN')); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php
						echo DonationHelper::getChoicesJsSelect($this->lists['payment_methods']);
						?>
					</div>
				</div>
				<?php
				if($this->count_active_currencies_arr > 1)
				{
					?>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_CURRENCY') ; ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['currency'];
							?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_CURRECY_SYMBOL') ; ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input type="text" class="form-control input-mini w-25" name="currency_symbol" value="<?php echo $this->item->currency_symbol ; ?>" size="5" />
						</div>
					</div>
					<?php
				}
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_PAYPAL_EMAIL') ; ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input type="text" class="form-control w-50" name="paypal_id" value="<?php echo $this->item->paypal_id ; ?>" size="40" />
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_PAYPAL_REDIRECTION_MESSAGE') ; ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input type="text" class="form-control w-100" name="paypal_redirection_message" value="<?php echo $this->item->paypal_redirection_message ; ?>" size="40" />
					</div>												   
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_API_LOGIN') ; ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input type="text" class="form-control w-50" name="authorize_api_login" value="<?php echo $this->item->authorize_api_login ; ?>" size="40" />
					</div>												   
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_TRANSACTION_KEY') ; ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input type="text" class="form-control w-50" name="authorize_transaction_key" value="<?php echo $this->item->authorize_transaction_key ; ?>" size="40" />
					</div>
				</div>
			</fieldset>
			<?php
			echo HTMLHelper::_($tabApiPrefix.'endTab');
			if ($translatable) {
				echo HTMLHelper::_($tabApiPrefix.'addTab', 'campaign', 'translation-page', Text::_('JD_TRANSLATION'));
				?>
				<div class="tab-pane" id="translation-page">
					<div class="tab-content">
						<?php
						$i = 0;
						echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'campaign-translation', array('active' => 'translation-page-0'));
						foreach ($this->languages as $language) 
						{
							$sef = $language->sef;
							echo HTMLHelper::_($tabApiPrefix.'addTab', 'campaign-translation', 'translation-page-'.$i, $language->title . ' <img src="' . Uri::root() . 'media/com_jdonation/flags/' . $sef . '.png" />');
							?>
							<div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>">
								<table class="admintable" style="width: 100%;">
									<div class="<?php echo $controlGroupClass; ?>">
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo Text::_('JD_TITLE'); ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
											<input class="form-control input-large ilarge" type="text" name="title_<?php echo $sef; ?>"
												   id="title_<?php echo $sef; ?>" size="" maxlength="250"
												   value="<?php echo $this->item->{'title_' . $sef}; ?>"/>
										</div>
									</div>
									<div class="<?php echo $controlGroupClass; ?>">
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo Text::_('JD_ALIAS'); ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
											<input class="form-control input-large ilarge" type="text" name="alias_<?php echo $sef; ?>"
												   id="alias_<?php echo $sef; ?>" size="" maxlength="250"
												   value="<?php echo $this->item->{'alias_' . $sef}; ?>"/>
										</div>
									</div>
                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_SHORT_DESCRIPTION'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <?php echo $editor->display('short_description_' . $sef, $this->item->{'short_description_' . $sef}, '100%', '250', '75', '10'); ?>
                                        </div>
                                    </div>
									<div class="<?php echo $controlGroupClass; ?>">
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo Text::_('JD_DESCRIPTION'); ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
											<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10'); ?>
										</div>
									</div>
									<?php
									if((int)$this->config->simple_language == 0)
									{				   
									?>
										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_AMOUNTS_EXPLANTION'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<textarea name="amounts_explanation_<?php echo $sef; ?>" cols="40"
														  rows="5" class="form-control"><?php echo $this->item->{'amounts_explanation_' . $sef}; ?></textarea>
											</div>
										</div>
										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_USER_EMAIL_SUBJECT'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<input type="text" name="user_email_subject_<?php echo $sef; ?>"
													   class="form-control input-large ilarge"
													   value="<?php echo $this->item->{'user_email_subject_' . $sef}; ?>"
													   size="50"/>
											</div>
										</div>
										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_USER_EMAIL_BODY'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<?php echo $editor->display('user_email_body_' . $sef, $this->item->{'user_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
											</div>
										</div>
										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<?php echo $editor->display('user_email_body_offline_' . $sef, $this->item->{'user_email_body_offline_' . $sef}, '100%', '250', '75', '8'); ?>
											</div>
										</div>
										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_SUBJECT'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<input type="text" name="recurring_email_subject_<?php echo $sef; ?>"
													   class="form-control input-large ilarge"
													   value="<?php echo $this->item->{'recurring_email_subject_' . $sef}; ?>"
													   size="50"/>
											</div>
										</div>
										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_BODY'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<?php echo $editor->display('recurring_email_body_' . $sef, $this->item->{'recurring_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
											</div>
										</div>

										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_DONATION_PAGE_MESSAGE'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<?php echo $editor->display('donation_form_msg_' . $sef, $this->item->{'donation_form_msg_' . $sef}, '100%', '250', '75', '10'); ?>
											</div>
										</div>

										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_THANKYOU_MESSAGE'); ?>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<?php echo $editor->display('thanks_message_' . $sef, $this->item->{'thanks_message_' . $sef}, '100%', '250', '75', '10'); ?>
											</div>
										</div>
									<?php } ?>
								</table>
							</div>
							<?php
							echo HTMLHelper::_($tabApiPrefix.'endTab');
							$i++;
						}
						echo HTMLHelper::_($tabApiPrefix.'endTabSet');
						?>
					</div>
				</div>
				<?php
				echo HTMLHelper::_($tabApiPrefix.'endTab');
			}
			echo HTMLHelper::_($tabApiPrefix.'endTabSet');
			?>
	<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="90000000" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
	function changeValue(item){
		var itemElement = document.getElementById(item);
		if(itemElement.value == 0){
			itemElement.value = 1;
		}else{
			itemElement.value = 0;
		}
	}

	// Số liệu mẫu (có thể lấy từ backend hoặc form)
	const donated = <?php echo $this->item->donated_amount; ?>;    
	const goal = <?php echo $this->item->goal; ?>;      
	const donations = <?php echo $this->item->number_donations; ?>;    

	// Tính toán phần trăm và cập nhật UI
	const percent = Math.min(Math.round((donated / goal) * 100), 100);
	const circle = document.getElementById('progress-bar');
	const value = document.getElementById('progress-value');
	const current = document.getElementById('current-amount');
	const goalElem = document.getElementById('goal-amount');
	const countElem = document.getElementById('donate-count');

	const radius = 40;
	const circumference = 2 * Math.PI * radius;

	// Update số liệu
	current.textContent = `$${donated.toLocaleString()}`;
	goalElem.textContent = `$${goal.toLocaleString()}`;
	countElem.textContent = `(${donations} donations)`;
	value.textContent = percent + '%';

	// Vẽ vòng tròn tiến độ
	circle.style.strokeDasharray = circumference;
	circle.style.strokeDashoffset = circumference * (1 - percent / 100);

	// (Optional) Animate
	circle.animate(
	[{ strokeDashoffset: circumference }, { strokeDashoffset: circumference * (1 - percent / 100) }],
	{ duration: 900, fill: "forwards" }
	);

</script>
