<?php

/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if ($config->use_https)
{
	$ssl 			= 1;
}
else
{
	$ssl 			= 0;
}
$bootstrapHelper 	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class      	= $bootstrapHelper->getClassMapping('span12');
$span6Class      	= $bootstrapHelper->getClassMapping('span6');
$span3Class      	= $bootstrapHelper->getClassMapping('span3');
$controlGroupClass 	= $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass 	= $bootstrapHelper->getClassMapping('input-group');
$addOnClass        	= $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass 	= $bootstrapHelper->getClassMapping('control-label');
$controlsClass     	= $bootstrapHelper->getClassMapping('controls');
$config             = $this->config;
$user               = Factory::getApplication()->getIdentity();
$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");
?>
<form method="post" action="<?php echo Route::_('index.php?option=com_jdonation');?>" id="jdForm" name="jdForm">
<h1>
    <?php echo Text::_('JD_DONATION');?> #<?php echo $this->item->id;?>
</h1>
<div id="donation-details" class="<?php echo $rowFluidClass." ".$extralayoutCss;?> jd-container">
    <div class="<?php echo $span6Class?> donor_information">
        <div class="<?php echo $rowFluidClass;?>">
            <div class="<?php echo $span12Class?>">
                <h3><?php echo Text::_('JD_DONOR_INFORMATION');?></h3>
            </div>
        </div>
        <div class="<?php echo $rowFluidClass;?>">
            <div class="<?php echo $span12Class?>">
                <?php
                if ($config->use_campaign)
                {
                    ?>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo Text::_('JD_CAMPAIGN'); ?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
                            <?php echo $this->item->campaign_title; ?>
                        </div>
                    </div>
                    <?php
                }
                echo $this->form->getOutput(true, $bootstrapHelper);
                if($config->activate_tributes && $this->item->show_dedicate == 1)
                {
                    ?>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo  Text::_('JD_DEDICATE_DONATION'); ?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
                            <?php
                                echo Text::_('JYES');
                            ?>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo  Text::_('JD_DEDICATE_TYPE'); ?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
                            <div class="<?php echo $rowFluidClass?>">
                                <?php
                                echo DonationHelper::getDedicateType($this->item->dedicate_type);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo Text::_('JD_HONOREE_NAME');?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
                            <?php echo $this->item->dedicate_name; ?>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo Text::_('JD_HONOREE_EMAIL');?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
                            <?php echo $this->item->dedicate_email; ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="<?php echo $span6Class?> payment_information">
        <div class="<?php echo $rowFluidClass;?>">
            <div class="<?php echo $span12Class?>">
                <h3><?php echo Text::_('JD_PAYMENT_INFORMATION');?></h3>
            </div>
        </div>
        <div class="<?php echo $rowFluidClass;?>">
            <div class="<?php echo $span12Class?>">
                <div class="<?php echo $controlGroupClass;?>">
                    <div class="<?php echo $controlLabelClass;?>">
                        <?php echo  Text::_('JD_DONATION_DATE'); ?>
                    </div>
                    <div class="<?php echo $controlsClass;?>">
                        <?php echo HTMLHelper::_('date', $this->item->created_date, 'j F Y', true) ; ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass;?>">
                    <div class="<?php echo $controlLabelClass;?>">
                        <?php echo  Text::_('JD_AMOUNT'); ?>
                    </div>
                    <div class="<?php echo $controlsClass;?>">
                        <?php
                        if($config->include_payment_fee == 1)
                        {
                            echo DonationHelperHtml::formatAmount($config, $this->item->amount + $this->item->payment_fee, $this->item->currency_code);
                        }
                        else
                        {
                            echo DonationHelperHtml::formatAmount($config, $this->item->amount, $this->item->currency_code);
                        }
                        ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass;?>">
                    <div class="<?php echo $controlLabelClass;?>">
                        <?php echo Text::_('JD_PAYMENT_METHOD') ?>
                    </div>
                    <div class="<?php echo $controlsClass;?>">
                        <?php
                        $method = os_jdpayments::getPaymentMethod($this->item->payment_method);
                        if ($method)
                        {
                            echo Text::_($method->getTitle());
                        }
                        ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass;?>">
                    <div class="<?php echo $controlLabelClass;?>">
                        <?php echo Text::_('JD_TRANSACTION_ID'); ?>
                    </div>
                    <div class="<?php echo $controlsClass;?>">
                        <?php echo $this->item->transaction_id ; ?>
                    </div>
                </div>

                <?php
                if ($this->item->donation_type == 'R')
                {
                    ?>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo Text::_('JD_DONATION_TYPE'); ?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
                            <?php echo Text::_('JD_RECURRING'); ?>
                        </div>
                    </div>
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo Text::_('JD_FREQUENCY'); ?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
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
                    <div class="<?php echo $controlGroupClass;?>">
                        <div class="<?php echo $controlLabelClass;?>">
                            <?php echo Text::_('JD_NUMBER_PAYMENTS'); ?>
                        </div>
                        <div class="<?php echo $controlsClass;?>">
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
                }
                ?>
                <div class="<?php echo $controlGroupClass;?>">
                    <div class="<?php echo $controlLabelClass;?>">
                        <?php echo Text::_('JD_HIDE_DONOR'); ?>
                    </div>
                    <div class="<?php echo $controlsClass;?>">
                        <?php
                        //echo DonationHelperHtml::showCheckboxfield('hide_me',(int)$this->item->hide_me);
                        if((int)$this->item->hide_me)
                        {
                            echo Text::_('JYES');
                        }
                        else
                        {
                            echo Text::_('JNO');
                        }
                        ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass;?>">
                    <div class="<?php echo $controlLabelClass;?>">
                        <?php echo Text::_('JD_PAID'); ?>
                    </div>
                    <div class="<?php echo $controlsClass;?>">
                        <?php
                        if($this->item->payment_method == "os_offline")
                        {
                            echo DonationHelperHtml::showCheckboxfield('published',(int)$this->item->published);
                        }
                        else
                        {
                            if ((int)$this->item->published)
                            {
                                echo Text::_('JYES');
                            }
                            else
                            {
                                echo Text::_('JNO');
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass; ?> savebutton">
                    <?php
                    if($this->item->payment_method == "os_offline")
                    {
                        ?>
                        <a href="javascript:void(0)" class="btn btn-success" onclick="javascript:saveDonor();">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-check-fill" viewBox="0 0 16 16">
							  <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm-1.146 6.854-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708.708z"/>
							</svg>
							<?php echo Text::_('JD_SAVE');?>
                        </a>
                        <?php
                    }
                    ?>
                    &nbsp;
                    <?php
                    if($this->config->use_campaign && $this->campaign_id > 0)
                    {
                        $campaignUrl    = "&id=".$this->campaign_id;
                    }
                    ?>
                    <a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_jdonation&view=userdonors'.$campaignUrl.'&Itemid='.Factory::getApplication()->input->getInt('Itemid'));?>"><i class="fa fa-list"></i> <?php echo Text::_('JD_DONORS_LIST');?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="option" value="com_jdonation"/>
<input type="hidden" name="task"   value="donation.save"/>
<input type="hidden" name="id"     value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo Factory::getApplication()->input->getInt('Itemid');?>" />
</form>
<script type="text/javascript">
function saveDonor()
{
   document.jdForm.submit();
}
</script>
