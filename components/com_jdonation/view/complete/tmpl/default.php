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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
$row				= $this->row;
$bootstrapHelper 	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class		= $bootstrapHelper->getClassMapping('span12');
$span3Class			= $bootstrapHelper->getClassMapping('span3');
$span6Class			= $bootstrapHelper->getClassMapping('span6');
$config				= $this->config;
$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");
?>
<div id="donation-complete-page" class="<?php echo $rowFluidClass." ".$extralayoutCss ;?> jd-container">
	<div class="<?php echo $span12Class; ?>">
		<div class="<?php echo $rowFluidClass; ?>">
			<div class="<?php echo $span12Class; ?> completeheadingpart">
				<h1 class="jd-title"><?php echo Text::_('JD_COMPLETE'); ?></h1>
				<BR />
				<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0d6efd" class="bi bi-check2-circle" viewBox="0 0 16 16">
				  <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
				  <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
				</svg>
				</br>
				<p class="jd-message"><?php echo $this->message; ?></p>
			</div>
		</div>
		<div class="<?php echo $rowFluidClass; ?>">
			<div class="<?php echo $span3Class; ?>">
			</div>
			<div class="<?php echo $span6Class; ?> completemainingpart">
				<table class="completetable">
					<tr>
						<td class="label">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
							  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
							  <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
							</svg>
							<?php
							echo Text::_('JD_DONOR_NAME').":";
							?>
						</td>
						<td class="value">
							<?php
							echo $row->first_name.' '.$row->last_name;
							?>
						</td>
					</tr>
					<tr>
						<td class="label">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
							  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
							</svg>
							<?php
							echo Text::_('JD_EMAIL').":";
							?>
						</td>
						<td class="value">
							<?php
							echo $row->email;
							?>
						</td>
					</tr>
					<?php
					if($row->address != "")
					{
					?>
					<tr>
						<td class="label">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo" viewBox="0 0 16 16">
							  <path fill-rule="evenodd" d="M8 1a3 3 0 1 0 0 6 3 3 0 0 0 0-6zM4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999zm2.493 8.574a.5.5 0 0 1-.411.575c-.712.118-1.28.295-1.655.493a1.319 1.319 0 0 0-.37.265.301.301 0 0 0-.057.09V14l.002.008a.147.147 0 0 0 .016.033.617.617 0 0 0 .145.15c.165.13.435.27.813.395.751.25 1.82.414 3.024.414s2.273-.163 3.024-.414c.378-.126.648-.265.813-.395a.619.619 0 0 0 .146-.15.148.148 0 0 0 .015-.033L12 14v-.004a.301.301 0 0 0-.057-.09 1.318 1.318 0 0 0-.37-.264c-.376-.198-.943-.375-1.655-.493a.5.5 0 1 1 .164-.986c.77.127 1.452.328 1.957.594C12.5 13 13 13.4 13 14c0 .426-.26.752-.544.977-.29.228-.68.413-1.116.558-.878.293-2.059.465-3.34.465-1.281 0-2.462-.172-3.34-.465-.436-.145-.826-.33-1.116-.558C3.26 14.752 3 14.426 3 14c0-.599.5-1 .961-1.243.505-.266 1.187-.467 1.957-.594a.5.5 0 0 1 .575.411z"/>
							</svg>
							<?php
							echo Text::_('JD_ADDRESS').":";
							?>
						</td>
						<td class="value">
							<?php
							echo $row->address;
							if($row->city != "")
							{
								echo ", ".$row->city;
							}
							if($row->zip != "")
							{
								echo ", ".$row->zip;
							}
							if($row->state != "")
							{
								echo ", ".$row->state;
							}
							if($row->country != "")
							{
								echo ", ".$row->country;
							}
							?>
						</td>
					</tr>
					<?php
					}			
					?>
				</table>

				<BR />
	
				<table class="completetable">
					<?php 
					if($row->payment_method != ""){?>
					<tr>
						<td class="label">
							<?php
							echo Text::_('JD_PAYMENT_METHOD').":";
							?>
						</td>
						<td class="value">
							<?php
							echo Text::_(os_jdpayments::returnPaymentMethodTitle($row->payment_method));
							?>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td class="label">
							<?php
							echo Text::_('JD_PAID_STATUS').":";
							?>
						</td>
						<td class="value">
							<?php
							if($row->published == 1)
							{
								echo Text::_('JD_COMPLETED');
							}
							else
							{
								echo Text::_('JD_PENDING');
							}
							?>
						</td>
					</tr>
					<tr>
						<td class="label">
							<?php
							echo Text::_('JD_DONATION_AMOUNT').":";
							?>
						</td>
						<td class="value">
							<?php
							echo DonationHelperHtml::formatAmount($config, $row->amount , $row->currency_code);
							?>
						</td>
					</tr>
					<tr>
						<td class="label last">
							<?php
							echo Text::_('JD_TOTAL_AMOUNT').":";
							?>
						</td>
						<td class="value last">
							<?php
							echo DonationHelperHtml::formatAmount($config, $row->amount + $row->payment_fee, $row->currency_code);
							?>
						</td>
					</tr>
				</table>

				<BR />

				<?php
				if ($config->activate_donation_receipt_feature && $row->published == 1) 
				{
					?>
					<a href="<?php echo Route::_('index.php?option=com_jdonation&task=download_receipt&id='.$row->id); ?>" title="<?php echo Text::_('JD_DOWNLOAD_RECEIPT'); ?>" class="btn btn-primary"><?php echo Text::_('JD_DOWNLOAD_RECEIPT'); ?></a>
					<?php	
				}
				
				?>
			</div>
			<div class="<?php echo $span3Class; ?>">
			</div>
		</div>
	</div>
</div>
<?php
$db = Factory::getContainer()->get('db');
if($row->campaign_id > 0)
{
	$db->setQuery("Select title from #__jd_campaigns where id = '$row->campaign_id'");
	$title = $db->loadResult();
	$title = str_replace(","," ", $title);
}

if($title == "")
{
	$title = Text::_('JD_DONATION');
}

?>
<script>
window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    event: "purchase",
    ecommerce: {
      transaction_id: "<?php echo $row->transaction_id;?>",  
      affiliation: "<?php echo Text::_('JD_DONATION'); ?>",
      value: <?php echo $row->amount;?>, 
      currency: "<?php echo $row->currency_code;?>",
      items: [
        {
          item_name: "<?php echo $title;?>",  
          item_id: <?php echo $row->id;?>,
          price: <?php echo $row->amount;?>,
          quantity: 1
        }
      ]
    }
  });
</script>
