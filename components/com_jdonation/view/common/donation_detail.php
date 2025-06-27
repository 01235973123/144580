<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
?>
<?php
if ($config->use_campaign)
{
?>
    <div class="control-group">
        <label class="control-label">
            <?php echo Text::_('JD_CAMPAIGN'); ?>
        </label>
        <div class="controls">
            <?php echo $campaignTitle; ?>
        </div>
    </div>
<?php
}
echo $form->getOutput();
?>
<?php
if ($row->donation_type == 'I')
{
    $donationType = Text::_('JD_ONETIME');
}
else
{
    $donationType = Text::_('JD_RECURRING');
}
?>
<div class="control-group">
    <label class="control-label">
        <?php echo Text::_('JD_DONATION_TYPE'); ?>
    </label>
    <div class="controls">
        <?php echo $donationType; ?>
    </div>
</div>
<div class="control-group">
    <label class="control-label">
        <?php echo Text::_('JD_DONATION_AMOUNT'); ?>
    </label>
    <div class="controls">
        <?php
        if($config->include_payment_fee == 1) {
            echo DonationHelperHtml::formatAmount($config, $row->amount + $row->payment_fee, $row->currency_code);
        }else{
            echo DonationHelperHtml::formatAmount($config, $row->amount, $row->currency_code);
        }
        ?>
    </div>
</div>
<div class="control-group">
    <label class="control-label">
        <?php echo Text::_('JD_TRANSACTION_ID'); ?>
    </label>
    <div class="controls">
        <?php 
		if($row->transaction_id != "")
		{
			echo $row->transaction_id;
		}
		elseif($row->subscr_id != "")
		{
			echo $row->subscr_id;
		}
		?>
    </div>
</div>
<?php
if ($row->donation_type == 'R')
{
    switch ($row->r_frequency)
    {
        case 'd' :
            $frequency = Text::_('JD_DAILY');
            break;
        case 'w' :
            $frequency = Text::_('JD_WEEKLY');
            break;
        case 'b' :
            $frequency = Text::_('JD_BI_WEEKLY') ;
            break ;
        case 'm' :
            $frequency = Text::_('JD_MONTHLY');
            break;
        case 'q' :
            $frequency = Text::_('JD_QUARTERLY');
            break;
        case 's' :
            $frequency = Text::_('JD_SEMI_ANNUALLY');
            break;
        case 'a' :
            $frequency = Text::_('JD_ANNUALLY');
            break;
        default:
            $frequency = '';
            break;
    }
    ?>
    <div class="control-group">
        <label class="control-label">
            <?php echo Text::_('JD_FREQUENCY'); ?>
        </label>
        <div class="controls">
            <?php echo $frequency; ?>
        </div>
    </div>
    <?php
    if ($row->r_times)
    {
    ?>
        <div class="control-group">
            <label class="control-label">
                <?php echo Text::_('JD_NUMBER_DONATIONS'); ?>
            </label>
            <div class="controls">
                <?php echo $row->r_times; ?>
            </div>
        </div>
    <?php
    }
}

?>
<div class="control-group">
    <label class="control-label">
        <?php echo Text::_('JD_PAYMENT_OPTION'); ?>
    </label>
    <div class="controls">
        <?php
	        $method = os_jdpayments::getPaymentMethod($row->payment_method);
	        if ($method)
	        {
		        echo Text::_($method->getTitle());
	        }
	    ?>
    </div>
</div>
<?php
if($row->payment_method == 'os_jd_offline_creditcard' && $row->params)
{
	require_once JPATH_ROOT . '/components/com_jdonation/helper/encrypt.php';
	$ccEncryption		= new CreditCardEncryption();
	$params				= new Registry($row->params);
	$last_cc_characters	= $params->get('last_characters');
	$decrypted			= $ccEncryption->decrypt($last_cc_characters);
	?>
	<div class="control-group">
		<label class="control-label">
			<?php echo Text::_('JD_4_LAST_NUMBERS_CC'); ?>
		</label>
		<div class="controls">
			<?php
				echo $decrypted;
			?>
		</div>
	</div>
	<?php
}
?>
