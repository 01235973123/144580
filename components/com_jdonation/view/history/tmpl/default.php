<?php

/**
 * @version        5.4
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Dang Thuc Dam
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text; 
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$db                 = Factory::getContainer()->get('db');
$query              = $db->getQuery(true);
$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");
?>
<div id="donation-history-page" class="<?php echo $rowFluidClass." ".$extralayoutCss;?> jd-container">
<h1 class="jd-title"><?php echo $this->page_heading ; ?></h1>
<form action="<?php echo Route::_('index.php?option=com_jdonation&Itemid='.$this->Itemid); ?>" method="post" name="adminForm" id="adminForm">	
	<table class="table table-striped table-bordered table-condensed" id="table-donorhistory">
		<thead>
			<tr>
				<th width="5%" class="hidden-phone">
					<?php echo Text::_('JD_NO'); ?>	
				</th>			
				<th width="5%" class="hidden-phone">
					<?php echo Text::_('ID'); ?>	
				</th>
				<th>
					<?php echo Text::_('JD_CAMPAIGN') ?>
				</th>
				<th class="center">
					<?php echo Text::_('JD_DONATION_DATE') ; ?>
				</th>
				<th class="hidden-phone">
					<?php echo Text::_('JD_DONATION_TYPE') ; ?>
				</th>
				<th class="center">
					<?php echo Text::_('JD_DONATION_AMOUNT') ; ?>
				</th>
				<th>
					<?php echo Text::_('JD_TRANSACTION_ID') ; ?>
				</th>
				<th>
					<?php echo Text::_('JD_PAID') ; ?>
				</th>
				<?php 
                if ($this->config->activate_donation_receipt_feature)
                {
                ?>
                    <th class="hidden-phone">
                        <?php echo Text::_('JD_DONATION_RECEIPT') ; ?>
                    </th>
                <?php
                }
				?>
                <?php
                if($this->config->enable_cancel_recurring)
                {
                    ?>
                    <th>
                        <?php echo Text::_('JD_CANCEL_DONATION') ; ?>
                    </th>
                    <?php
                }
                ?>
			</tr>
		</thead>				
		<tbody>		
		<?php						
			for ($i = 0 , $n = count($this->items) ; $i < $n ; $i++) {
				$row = $this->items[$i] ;												
			?>
				<tr>
					<td class="center" data-label="<?php echo Text::_('JD_NO'); ?>">
						<?php echo $i + 1 + $this->start; ?>
					</td>
					<td class="center" data-label="<?php echo Text::_('ID'); ?>">
						<?php echo $row->id; ?>
					</td>
					<td data-label="<?php echo Text::_('JD_CAMPAIGN'); ?>">
						<?php echo $row->title; ?>
					</td>				
					<td class="center" data-label="<?php echo Text::_('JD_DONATION_DATE'); ?>">
						<?php echo HTMLHelper::_('date', $row->created_date, $this->config->date_format) ; ?>
					</td>
					<td data-label="<?php echo Text::_('JD_DONATION_TYPE'); ?>">
						<?php
							if ($row->donation_type == 'R')	{
								echo Text::_('JD_RECURRING') ;
							} else {
								echo Text::_('JD_ONETIME') ;
							}
						?>
					</td>										
					<td align="right" data-label="<?php echo Text::_('JD_DONATION_AMOUNT'); ?>">
						<?php echo DonationHelperHtml::formatAmount($this->config, $row->amount); ?>
					</td>					
					<td class="hidden-phone" data-label="<?php echo Text::_('JD_TRANSACTION_ID'); ?>">
						<?php echo $row->transaction_id ; ?>
					</td>
					<td class="" data-label="<?php echo Text::_('JD_PAID'); ?>">
						<?php
						if($row->published == 1)
						{
							echo Text::_('JYES');
						}
						else
						{
							echo Text::_('JNO');
							?>
								<BR />
							<a href="<?php echo Route::_('index.php?option=com_jdonation&task=donation.processPayment&id='.$row->id.'&Itemid='.$this->Itemid);?>" title="<?php echo Text::_('OS_MAKE_PAYMENT');?>">
								<?php echo Text::_('OS_MAKE_PAYMENT');?>
							</a>
							<?php
						}
						?>
					</td>
					<?php 
                    if ($this->config->activate_donation_receipt_feature) 
					{
                    ?>
                        <td class="center" data-label="<?php echo Text::_('JD_DONATION_RECEIPT'); ?>">
							<?php
							if($this->config->generated_invoice_for_paid_donation_only)
							{
								if($row->published == 1)
								{
									?>
									<a href="<?php echo Route::_('index.php?option=com_jdonation&task=download_receipt&id='.$row->id); ?>" title="<?php echo Text::_('JD_DOWNLOAD'); ?>"><?php echo Text::_('JD_DOWNLOAD'); ?></a>
									<?php
								}
							}
							else
							{
								?>
								<a href="<?php echo Route::_('index.php?option=com_jdonation&task=download_receipt&id='.$row->id); ?>" title="<?php echo Text::_('JD_DOWNLOAD'); ?>"><?php echo Text::_('JD_DOWNLOAD'); ?></a>
							<?php
							}		
							?>
                        </td>
                    <?php
                    }
					?>
                    <?php
                    if($this->config->enable_cancel_recurring)
                    {
                        ?>
                        <td class="center" data-label="<?php echo Text::_('JD_CANCEL_DONATION'); ?>">
                            <?php
                            if ($row->donation_type == 'R')
                            {
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
                                    require_once JPATH_COMPONENT . '/payments/' . $payment_method . '.php';
                                    $paymentClass = new $payment_method($params);

                                    if (method_exists($paymentClass, 'supportCancelRecurringSubscription'))
                                    {
                                        if($paymentClass->supportCancelRecurringSubscription() && $row->recurring_donation_cancelled == 0 && (($row->r_times > 0 && $row->r_times > $row->payment_made) || $payment_method == "os_stripe"))
                                        {
                                            ?>
                                            <a href="javascript:void(0);" class="btn btn-warning" onclick="javascript:cancelRecurringDonation(<?php echo $row->id;?>);">
                                                <?php echo Text::_('JD_CANCEL_DONATION');?>
                                            </a>
                                            <?php
                                        }
                                    }
                                }
                            }
							if($row->recurring_donation_cancelled == 1)
							{
								echo Text::_('JD_RECURRING_DONATION_CANCELLED');
							}
                            ?>
                        </td>
                        <?php
                    }
                    ?>
				</tr>
			<?php	
			}
			if ($this->pagination->total > $this->pagination->limit) {
				if ($this->config->activate_donation_receipt_feature)
					$cols = 10 ;
				else
					$cols = 9 ;
			?>
				<tr>
					<td colspan="<?php echo $cols; ?>">
						<div class="pagination"><?php echo $this->pagination->getListFooter(); ?></div>
					</td>
				</tr>
			<?php	
			}
		?>				
		</tbody>	
	</table>
</form>
</div>
<script type="text/javascript">
    function cancelRecurringDonation(id)
    {
        var answer = confirm("<?php echo Text::_('JD_DO_YOU_WANT_TO_CANCEL_THE_RECURRING_DONATION');?>");
        if(answer == 1)
        {
            location.href = "<?php echo Uri::root(); ?>index.php?option=com_jdonation&task=donation.cancelrecurringdonation&id=" + id;
        }
    }
</script>
