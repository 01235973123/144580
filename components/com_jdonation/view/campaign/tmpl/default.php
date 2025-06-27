<?php

/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$editor				= Editor::getInstance(Factory::getApplication()->getConfig()->get('editor'));
$translatable		= Multilanguage::isEnabled() && count($this->languages);
if (version_compare(JVERSION, '4.0.0-dev', 'lt'))
{
	HTMLHelper::_('behavior.tabstate');
}

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
    function submitCampaign()
    {
        var form = document.adminForm;
		//disable btnSubmitCampaign
		jQuery("#btnSubmitCampaign").attr("disabled", true);
        form.task.value = "campaign.save";
        form.submit();
    }
    function cancelSubmit()
    {
        var form = document.adminForm;
        form.task.value = "campaign.canceledit";
        form.submit();
    }
</script>
<form class="form-horizontal" action="<?php echo Route::_('index.php?option=com_jdonation&task=campaign.save&Itemid='.Factory::getApplication()->input->getInt('Itemid'));?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="<?php echo $rowFluidClass;?>">
        <div class="<?php echo $span12Class?>">
            <h1 class="jd-page-title">
            <?php
            if($this->item->id > 0)
            {
                echo str_replace('CAMPAIGN_TITLE', $this->item->title, Text::_('JD_EDIT_CAMPAIGN'));
            }
            else
            {
                echo Text::_('JD_ADD_CAMPAIGN');
            }
            ?>
            </h1>
        </div>
    </div>
    <div class="<?php echo $rowFluidClass;?>">
        <div class="<?php echo $span12Class?> alignright">
            <input type="button" class="btn btn-warning" onclick="cancelSubmit();" value="<?php echo Text::_('JD_CANCEL'); ?>" />
            <input type="button" id="btnSubmitCampaign" class="btn btn-success" onclick="submitCampaign();" value="<?php echo Text::_('JD_SAVE'); ?>"  />
        </div>
    </div>
    <div class="<?php echo $rowFluidClass;?>">
        <div class="<?php echo $span12Class?>">
            <?php
            echo HTMLHelper::_('bootstrap.startTabSet', 'campaign', array('active' => 'general-page'));
            echo HTMLHelper::_('bootstrap.addTab', 'campaign', 'general-page', Text::_('JD_GENERAL', true));
            ?>
            <div class="tab-pane active" id="general-page">
                <div class="<?php echo $controlGroupClass; ?>">
                    <label class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('JD_TITLE'); ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <input class="<?php echo $inputLargeClass; ?>" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $this->item->title;?>" />
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass; ?>">
                    <label class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('JD_ALIAS'); ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <input class="<?php echo $inputLargeClass; ?>" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->item->alias;?>" />
                    </div>
                </div>
				<div class="<?php echo $controlGroupClass; ?>">
                    <label class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('JD_CATEGORY'); ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
						echo $this->lists['category_id'];
						?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass; ?>">
                    <label class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('JD_PHOTO');?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
                        if($this->item->campaign_photo != "")
						{
							if(file_exists(JPATH_ROOT.'/images/jdonation/'.$this->item->campaign_photo))
							{
                            ?>
								<img src="<?php echo Uri::root()?>images/jdonation/<?php echo $this->item->campaign_photo?>" width="150" class="img img-polaroid"/>
							<?php
							}
							elseif(file_exists(JPATH_ROOT.'/'.$this->item->campaign_photo))
							{
								?>
								<img src="<?php echo Uri::root()?><?php echo $this->item->campaign_photo?>" width="150" class="img img-polaroid"/>
							<?php
							}
							?>
                            <div class="clearfix"></div>
                            <input type="checkbox" name="remove_photo" id="remove_photo" value="0" onclick="javascript:changeValue('remove_photo');" /> &nbsp; <?php echo Text::_('JD_DELETE_PICTURE'); ?>
                            <div class="clearfix"></div>
                            <?php
                        }
                        ?>
                        <input type="file" name="photo" id="photo" class="form-control input-medium" />
                    </div>
                </div>
				<div class="<?php echo $controlGroupClass; ?>">
                    <label class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('JD_PUBLISHED'); ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
						echo $this->lists['published'];
						?>
                    </div>
                </div>
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
                        <input class="<?php echo $inputSmallClass; ?>" type="text" name="goal" id="goal" size="20" maxlength="250" value="<?php echo $this->item->goal;?>" />
                    </div>
                </div>
                <?php
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
                        <?php echo Text::_('JD_DISABLE_DONATION_WHEN_THIS_CAMPAIGN_HAS'); ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <input class="<?php echo $inputSmallClass; ?>" type="text" name="limit_donors" id="limit_donors" size="20" maxlength="250" value="<?php echo $this->item->limit_donors;?>" />
                        <?php
                        echo Text::_('JD_DONATIONS');
                        echo ".";
                        echo Text::_('JD_LEAVE_ZERO_UNLIMIT')
                        ?>
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
                        <?php echo Text::_('JD_SHORT_DESCRIPTION') ; ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php echo $editor->display( 'short_description',  $this->item->short_description , '100%', '200', '75', '5' , false) ;?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass; ?>">
                    <label class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('JD_DESCRIPTION') ; ?>
                    </label>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php echo $editor->display( 'description',  $this->item->description , '100%', '300', '75', '8' , false) ;?>
                    </div>
                </div>
				<?php
				if(Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_jdonation') || Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jdonation'))
				{
				?>
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
				<?php
							
				}		
				?>
            </div>
            <?php
            echo HTMLHelper::_('bootstrap.endTab');
            echo HTMLHelper::_('bootstrap.addTab', 'campaign', 'payment-page', Text::_('JD_PAYMENT', true));
                ?>
                <div class="help-text">
                    <p><?php echo Text::_('JD_PAYMENT_PAGE_EXPLANATION'); ?></p>
                </div>
                <?php
                if(DonationHelper::isPaypalEnable())
                {
                    ?>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_PAYPAL_EMAIL'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" class="<?php echo $inputLargeClass; ?>" name="paypal_id"
                                   value="<?php echo $this->item->paypal_id; ?>" size="50"/>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_PAYPAL_REDIRECTION_MESSAGE'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" class="<?php echo $inputLargeClass; ?>" name="paypal_redirection_message"
                                   value="<?php echo $this->item->paypal_redirection_message; ?>" size="50"/>
                        </div>
                    </div>
                    <?php
                }
                if(DonationHelper::isAuthorizeEnable())
                {
                    ?>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_API_LOGIN'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" class="<?php echo $inputLargeClass; ?>" name="authorize_api_login"
                                   value="<?php echo $this->item->authorize_api_login; ?>" size="50"/>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_TRANSACTION_KEY'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" class="<?php echo $inputLargeClass; ?>" name="authorize_transaction_key"
                                   value="<?php echo $this->item->authorize_transaction_key; ?>" size="50"/>
                        </div>
                    </div>
                    <?php
                }
                
            echo HTMLHelper::_('bootstrap.endTab');
            echo HTMLHelper::_('bootstrap.addTab', 'campaign', 'message-page', Text::_('JD_MESSAGES', true));
            ?>
            <div class="tab-pane">
                <table class="admintable">
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_PRE_DEFINED_AMOUNT'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <textarea name="amounts" cols="40" rows="5" class="<?php echo $inputLargeClass; ?>"><?php echo $this->item->amounts;?></textarea>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_AMOUNTS_EXPLANTION'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <textarea name="amounts_explanation" cols="40" rows="5" class="<?php echo $inputLargeClass; ?>"><?php echo $this->item->amounts_explanation;?></textarea>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_FROM_NAME'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input class="<?php echo $inputLargeClass; ?>" type="text" name="from_name" id="from_name" value="<?php echo $this->item->from_name;?>" />
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_FROM_EMAIL'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input class="<?php echo $inputLargeClass; ?>" type="text" name="from_email" id="from_email" value="<?php echo $this->item->from_email;?>" />
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_USER_EMAIL_SUBJECT'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" name="user_email_subject" class="<?php echo $inputLargeClass; ?>" value="<?php echo $this->item->user_email_subject; ?>" size="50" />
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_USER_EMAIL_BODY'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <?php echo $editor->display( 'user_email_body',  $this->item->user_email_body , '100%', '300', '75', '8' , false) ;?>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_SUBJECT'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">

                            <input type="text" name="recurring_email_subject" class="<?php echo $inputLargeClass; ?>" value="<?php echo $this->item->recurring_email_subject; ?>" size="50" />
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_BODY'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <?php echo $editor->display( 'recurring_email_body',  $this->item->recurring_email_body , '100%', '300', '75', '8' , false) ;?>
                        </div>
                    </div>
					<div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE'); ?>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                           <?php echo $editor->display( 'user_email_body_offline',  $this->item->user_email_body_offline , '100%', '250', '75', '8' , false) ;?>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_NOTIFICATION_EMAILS'); ?> <br />
                            <small>Comma separated</small>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" name="notification_emails" class="<?php echo $inputLargeClass; ?>" value="<?php echo $this->item->notification_emails; ?>" size="50" />
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_DONATION_PAGE_MESSAGE'); ?>
                            <br />
                            <small><?php echo Text::_('JD_DONATION_PAGE_MESSAGE_EXPLANATION');?></small>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <?php echo $editor->display( 'donation_form_msg',  $this->item->donation_form_msg , '100%', '300', '75', '8' , false) ;?>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_THANKYOU_MESSAGE'); ?>
							<br />
                            <small><?php echo Text::_('JD_THANKYOU_MESSAGE_EXPLANATION');?></small>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <?php echo $editor->display( 'thanks_message',  $this->item->thanks_message , '100%', '300', '75', '8', false ) ;?>
                        </div>
                    </div>
					<div class="<?php echo $controlGroupClass; ?>">
                        <label class="<?php echo $controlLabelClass; ?>">
                            <?php echo Text::_('JD_THANKYOU_MESSAGE_OFFLINE'); ?>
							<br />
                            <small><?php echo Text::_('JD_THANKYOU_MESSAGE_OFFLINE_EXPLANATION');?></small>
                        </label>
                        <div class="<?php echo $controlsClass; ?>">
                            <?php echo $editor->display( 'thanks_message_offline',  $this->item->thanks_message_offline , '100%', '300', '75', '8', false ) ;?>
                        </div>
                    </div>
                </table>
            </div>
            <?php
            echo HTMLHelper::_('bootstrap.endTab');
            if ($translatable) {
                echo HTMLHelper::_('bootstrap.addTab', 'campaign', 'translation-page', Text::_('JD_TRANSLATION'));
                ?>
                <div class="tab-pane" id="translation-page">
                    <div class="tab-content">
                        <?php
                        $i = 0;
                        echo HTMLHelper::_('bootstrap.startTabSet', 'campaign-translation', array('active' => 'translation-page-0'));
                        foreach ($this->languages as $language) {
                            $sef = $language->sef;
                            echo HTMLHelper::_('bootstrap.addTab', 'campaign-translation', 'translation-page-'.$i, $language->title . ' <img src="' . Uri::root() . 'media/com_jdonation/flags/' . $sef . '.png" />');
                            ?>
                            <div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>">
                                <table class="admintable adminform" style="width: 100%;">
                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_TITLE'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <input class="<?php echo $inputLargeClass; ?>" type="text" name="title_<?php echo $sef; ?>"
                                                   id="title_<?php echo $sef; ?>" size="" maxlength="250"
                                                   value="<?php echo $this->item->{'title_' . $sef}; ?>"/>
                                        </div>
                                    </div>
                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_ALIAS'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <input class="<?php echo $inputLargeClass; ?>" type="text" name="alias_<?php echo $sef; ?>"
                                                   id="alias_<?php echo $sef; ?>" size="" maxlength="250"
                                                   value="<?php echo $this->item->{'alias_' . $sef}; ?>"/>
                                        </div>
                                    </div>
                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_SHORT_DESCRIPTION'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <?php echo $editor->display('short_description_' . $sef, $this->item->{'short_description_' . $sef}, '100%', '250', '75', '10', false); ?>
                                        </div>
                                    </div>
                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_DESCRIPTION'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10', false); ?>
                                        </div>
                                    </div>

                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_AMOUNTS_EXPLANTION'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
											<textarea name="amounts_explanation_<?php echo $sef; ?>" cols="40"
                                                      rows="5"><?php echo $this->item->{'amounts_explanation_' . $sef}; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="<?php echo $controlGroupClass; ?>">
                                        <label class="<?php echo $controlLabelClass; ?>">
                                            <?php echo Text::_('JD_USER_EMAIL_SUBJECT'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <input type="text" name="user_email_subject_<?php echo $sef; ?>"
                                                   class="<?php echo $inputLargeClass; ?>"
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
                                            <?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_SUBJECT'); ?>
                                        </label>
                                        <div class="<?php echo $controlsClass; ?>">
                                            <input type="text" name="recurring_email_subject_<?php echo $sef; ?>"
                                                   class="<?php echo $inputLargeClass; ?>"
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
                                </table>
                            </div>
                            <?php
                            echo HTMLHelper::_('bootstrap.endTab');
                            $i++;
                        }
                        echo HTMLHelper::_('bootstrap.endTabSet');
                        ?>
                    </div>
                </div>
                <?php
                echo HTMLHelper::_('bootstrap.endTab');
            }
            echo HTMLHelper::_('bootstrap.endTabSet');
            ?>
        </div>
    </div>
    <div class="<?php echo $rowFluidClass;?>">
        <div class="<?php echo $span12Class?>">
            <input type="button" class="btn btn-warning" onclick="cancelSubmit();" value="<?php echo Text::_('JD_CANCEL'); ?>" />
            <input type="button" class="btn btn-success" onclick="submitCampaign();" value="<?php echo Text::_('JD_SAVE'); ?>"  />
        </div>
    </div>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="MAX_UPLOAD_FILESIZE" value="90000000" />
</form>
