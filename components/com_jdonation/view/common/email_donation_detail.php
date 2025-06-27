<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;

$db = Factory::getContainer()->get('db');
$query = $db->getQuery(true);
?>
<style>
    <?php echo $css ; ?>
</style>
<table width="100%" cellspacing="5" cellpadding ="5">
<tbody>
    <?php
        if ($config->use_campaign)
        {
        ?>
            <tr>
                <td class="title-cell" width="25%">
                    <?php echo Text::_('JD_CAMPAIGN'); ?>
                </td>
                <td>
                    <?php echo $campaignTitle ; ?>
                </td>
            </tr>
        <?php
        }
        echo $form->getOutput(false);
        if ($row->donation_type == 'I')
        {
            $donationType = Text::_('JD_ONETIME') ;
        }
        else
        {
            $donationType = Text::_('JD_RECURRING') ;
        }
    ?>
    <tr>
        <td class="title-cell" width="25%">
            <?php echo Text::_('JD_DONATION_TYPE'); ?>
        </td>
        <td>
            <?php echo $donationType;?>
        </td>
    </tr>
    <tr>
        <td class="title-cell" width="25%">
            <?php echo Text::_('JD_DONATION_AMOUNT'); ?>
        </td>
        <td>
            <?php
            if($config->include_payment_fee == 1) 
			{
                echo DonationHelperHtml::formatAmount($config, $row->amount + $row->payment_fee, $row->currency_code);
            }
			else
			{
                echo DonationHelperHtml::formatAmount($config, $row->amount, $row->currency_code);
            }
			?>
        </td>
    </tr>
    <tr>
        <td class="title-cell" width="25%">
            <?php echo Text::_('JD_TRANSACTION_ID'); ?>
        </td>
        <td>
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
        </td>
    </tr>
    <?php
    if ($row->donation_type == 'R')
    {
        switch ($row->r_frequency)
        {
            case 'd' :
                $frequency =  Text::_('JD_DAILY') ;
                break ;
            case 'w' :
                $frequency = Text::_('JD_WEEKLY') ;
                break ;
            case 'b' :
                $frequency = Text::_('JD_BI_WEEKLY') ;
                break ;
            case 'm' :
                $frequency = Text::_('JD_MONTHLY') ;
                break ;
            case 'q' :
                $frequency = Text::_('JD_QUARTERLY') ;
                break ;
            case 's' :
                $frequency = Text::_('JD_SEMI_ANNUALLY') ;
                break ;
            case 'a' :
                $frequency = Text::_('JD_ANNUALLY') ;
                break ;
            default:
                $frequency = '';
                break ;
        }
        ?>
        <tr>
            <td class="title-cell" width="25%">
                <?php echo Text::_('JD_FREQUENCY'); ?>
            </td>
            <td>
                <?php echo $frequency;?>
            </td>
        </tr>
        <?php
        if ($row->r_times)
        {
        ?>
            <tr>
                <td class="title-cell" width="25%">
                    <?php echo Text::_('JD_NUMBER_DONATIONS'); ?>
                </td>
                <td>
                    <?php echo $row->r_times;?>
                </td>
            </tr>
        <?php
        }
        $payment_method = $row->payment_method;
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
                if($paymentClass->supportCancelRecurringSubscription() && $row->recurring_donation_cancelled == 0 && ($row->r_times > 0 && $row->r_times > $row->payment_made))
                {
                    ?>
                    <tr>
                        <td class="title-cell" width="25%">
                            <?php echo Text::_('JD_CANCEL_DONATION'); ?>
                        </td>
                        <td>
                            <a href="<?php echo Uri::root()?>index.php?option=com_jdonation&task=donation.cancelrecurringdonation&id=<?php echo $row->id;?>"><?php echo Uri::root()?>index.php?option=com_jdonation&task=donation.cancelrecurringdonation&id=<?php echo $row->id;?></a>
                        </td>
                    </tr>
                    </a>
                    <?php
                }
            }
        }
    }
    ?>
    <tr>
        <td class="title-cell" width="25%">
            <?php echo Text::_('JD_PAYMENT_OPTION'); ?>
        </td>
        <td>
        <?php
	        $method = os_jdpayments::getPaymentMethod($row->payment_method);
	        if ($method)
	        {
		        echo Text::_($method->getTitle());
	        }
	    ?>
        </td>
    </tr>
	<?php
	if($row->payment_method == 'os_jd_offline_creditcard' && $row->params)
	{
		$ccEncryption		= new CreditCardEncryption();
		$params				= new Registry($row->params);
		$last_cc_characters	= $params->get('last_characters');
		$decrypted			= $ccEncryption->decrypt($last_cc_characters);
		?>
		<tr>
			<td class="title-cell" width="25%">
				<?php echo Text::_('JD_4_LAST_NUMBERS_CC'); ?>
			</td>
			<td>
				<?php
					echo $decrypted;
				?>
			</td>
		</tr>
		<?php
	}				
	?>
</tbody>
</table>
