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
use Joomla\CMS\Uri\Uri;

if ($this->config->use_https)
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
$span8Class   	    = $bootstrapHelper->getClassMapping('span8');
$span4Class   	    = $bootstrapHelper->getClassMapping('span4');
$config             = $this->config;
$user               = Factory::getApplication()->getIdentity();
?>
<div id="donation-campaigns" class="<?php echo $rowFluidClass;?> jd-container">
    <form method="post" name="jdform" id="jdform" action="<?php echo Route::_('index.php?option=com_jdonation&view=userdonors&id='.$this->campaign_id.'&Itemid='.$this->Itemid); ?>">
		<?php
		if($this->campaign_id > 0)
		{
			if($this->campaign_id > 0 && $this->campaign->currency_symbol != "")
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
			?>
			<style>
			#donation-form .switch-amounts input:checked + label
			{
				background-color: #<?php echo ($color != '') ? $color : 'b250d2' ?>;
			}
			#donation-form .switch-payment-gateway input:checked + label 
			{
				border:1px solid #<?php echo ($color != '') ? $color : 'b250d2' ?>;
			}
			.donated-amount
			{
				border:1px solid #<?php echo ($color != '') ? $color : 'b250d2' ?>;
			}
			.donated-amount-label
			{
				background-color: #<?php echo ($color != '') ? $color : 'b250d2' ?>;
			}
			</style>
			<script type='text/javascript' src="<?php echo DonationHelper::getSiteUrl().'media/com_jdonation/assets/js/imask/imask.min.js';?>"></script>
			<script type="text/javascript">
				<?php echo $this->recurringString ;?>
				var siteUrl	= "<?php echo DonationHelper::getSiteUrl(); ?>";
				var amounts_format = '<?php echo $this->config->amounts_format; ?>';
			</script>
			<?php
			echo $this->loadTemplate('campaign');
		}
		?>
		<div class="<?php echo $rowFluidClass;?>">
			<div class="<?php echo $span8Class;?>">
				<h2 class="page-title"><?php echo Text::_('JD_DONORS'); ?></h2>
			</div>
			<div class="<?php echo $span4Class; ?> donorstoolbar">
				<a href="index.php?option=com_jdonation&task=export&filter_campaign_id=<?php echo $this->campaign_id; ?>" class="btn btn-primary">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
				  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
				  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
				</svg>
				&nbsp;<?php echo Text::_('JD_EXPORT_DONORS')?></a>
			</div>
		</div>
	    
		<div class="<?php echo $rowFluidClass;?>">
			<div class="<?php echo $span12Class?>">
				<strong>
					<?php echo Text::_('JD_FILTER'); ?>:
				</strong>
				<?php echo HTMLHelper::_('calendar', Factory::getApplication()->input->getString('start_date',''), 'start_date', 'start_date','%Y-%m-%d', array('class' => 'input-small form-control ishort','placeholder' => Text::_('JD_FROM'))) ; ?>
				<?php echo HTMLHelper::_('calendar', Factory::getApplication()->input->getString('end_date',''), 'end_date', 'end_date','%Y-%m-%d', array('class' => 'input-small form-control ishort', 'placeholder' => Text::_('JD_TO'))) ; ?>
				<a class="btn btn-primary" href="javascript:document.jdform.submit();" title="<?php echo Text::_('JD_SUBMIT');?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#FFFFFF" class="bi bi-search" viewBox="0 0 16 16">
					  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
					</svg>
				</a>
				<a class="btn btn-primary" href="javascript:document.jdform.start_date.value='';document.jdform.end_date.value='';document.jdform.submit();" title="<?php echo Text::_('JD_RESET');?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
					  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
					  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
					</svg>
				</a>
			</div>
		</div>

		
        <?php
        if (count($this->items))
        {
            ?>
			<div class="<?php echo $rowFluidClass;?>">
				<div class="<?php echo $span12Class?>">
					<div class="donated_found">
						<?php
						echo sprintf(Text::_('JD_FOUND_DONATED'), $this->pagination->total);
						?>
					</div>
				</div> 
			</div>
            <table class="table table-striped table-bordered table-condensed" id="usercampaignstable">
                <thead>
                <tr>
                    <th class="jd-title-col">
                        <?php echo Text::_('JD_DONATION_INFORMATION'); ?>
                    </th>
					<?php
                    if($this->config->use_campaign)
                    {
                        ?>
                        <th class="jd-title-col">
                            <?php echo Text::_('JD_CAMPAIGN'); ?>
                        </th>
                        <?php
                    }
                    ?>
                    <th class="jd-date-col">
                        <?php echo Text::_('JD_DATE'); ?>
                    </th>
                    <th class="jd-donated-col">
                        <?php echo Text::_('JD_AMOUNT'); ?>
                    </th>
                    <th class="jd-donated-col">
                        <?php echo Text::_('JD_PAYMENT_METHOD'); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total = 0 ;
                for ($i = 0 , $n = count($this->items) ; $i < $n; $i++)
                {
                    $item               = $this->items[$i] ;
                    if($this->config->use_campaign && $this->campaign_id > 0)
                    {
                        $campaignUrl    = "&campaign_id=".$this->campaign_id;
                    }
                    $link               = Route::_('index.php?option=com_jdonation&view=donationdetails'.$campaignUrl.'&id='.$item->id.'&Itemid='.$this->Itemid);
                    ?>
                    <tr>
                        <td data-label="<?php echo Text::_('JD_DONATION_INFORMATION'); ?>">
							<strong>
                            <a href="<?php echo $link?>" title="<?php echo Text::_('JD_CLICK_HERE_TO_SEE_DONATION_DETAILS');?>">
                                <?php
                                echo $item->first_name." ".$item->last_name;
                                ?>
                            </a>
							</strong>
                            <?php
                            if($this->config->activate_tributes && $row->show_dedicate == 1){
                                ?>
                                <BR />
                                <span class="dedicated">
                                <?php
                                echo Text::_('JD_DEDICATE_DONATION')."<br />".DonationHelper::getDedicateType($row->dedicate_type)." <strong>".$row->dedicate_name."</strong>";
                                ?>
                            </span>
                                <?php
                            }
                            ?>
							<BR />
							<?php
							if ($this->config->enable_recurring)
							{
								?>
								<?php echo Text::_('JD_DONATION_TYPE'); ?>:
									<?php
									if ($item->donation_type == 'R')
									{
										echo Text::_('JD_RECURRING') ;
									}
									else
									{
										echo Text::_('JD_ONETIME') ;
									}
									?>
									<BR />
									<?php 
									if ($item->donation_type == 'R' && $item->r_frequency)
									{
										echo Text::_('JD_FREQUENCY'); ?>: 
										<?php
										if ($item->donation_type == 'R')
										{
											if ($item->r_frequency)
											{
												switch ($item->r_frequency)
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
											}
										}
										?>
										<BR />
									<?php
									}
									if($item->payment_made > 0 && $item->donation_type == 'R')
									{
										?>
										<?php echo Text::_('JD_NUMBER_PAYMENTS'); ?>:
										<?php
										if ($item->donation_type == 'R')
										{
											if (!$item->r_times)
											{
												$times = 'Un-limit' ;
											}
											else
											{
												$times = $row->r_times ;
											}
											echo $item->payment_made.' / '.$times ;
										}
										echo "<BR />";
									}
									echo Text::_('JD_NOTE').": ";
									if(DonationHelper::retrieveNumberDonations($item->email, $this->campaign_id) == 1)
									{
										echo Text::_('JD_FIRST_TIME_DONATION');
									}
									else
									{
										echo sprintf(Text::_('JD_ALREADY_DONATED'), DonationHelper::retrieveNumberDonations($item->email, $this->campaign_id));
									}
									?>
								</td>
								<?php
							}
							?>
                        </td>
						<?php
                        if($this->config->use_campaign)
                        {
                            ?>
                            <td data-label="<?php echo Text::_('JD_CAMPAIGN'); ?>">
                                <?php
                                echo $item->campaign_title;
                                ?>
                            </td>
                            <?php
                        }
                        ?>
                        <td class="center" data-label="<?php echo Text::_('JD_DATE'); ?>">
                            <?php echo HTMLHelper::_('date', $item->created_date, $this->config->date_format); ?>
                        </td>
                        
                        
                        <td class="center" data-label="<?php echo Text::_('JD_AMOUNT'); ?>">
                            <?php
                            if(DonationHelper::isMultipleCurrencies())
							{
                                if($item->amount_converted > 0 && $item->currency_converted != $item->currency_code && $item->currency_converted !='')
								{
                                    
                                    echo "(".number_format($item->amount_converted , 2);
                                    echo "&nbsp;";
                                    echo "<strong style='font-size:10px;'>";
                                    echo $item->currency_converted;
                                    echo "</strong>)";
                                    ?>
                                    <div class="clearfix"></div>
                                    <?php
                                }
                            }
                            echo number_format($item->amount , 2);
                            echo "&nbsp;";
                            echo "<strong style='font-size:10px;'>";
                            if($item->currency_code != "")
							{
                                echo $item->currency_code;
                            }
							else
							{
                                echo $this->config->currency;
                            }
                            echo "</strong>";
                            ?>
                        </td>
                        <td class="center" data-label="<?php echo Text::_('JD_DATE'); ?>">
                            <?php
                            $method = os_jdpayments::getPaymentMethod($item->payment_method);
							if($item->payment_method != "")
							{
								$payment_name = str_replace("os_","",$item->payment_method);
								if ($method)
								{
									if(file_exists(JPATH_ROOT.'/media/com_jdonation/assets/images/payments_override/'.$payment_name.'.png'))
									{
										?>
										<img src="<?php echo Uri::base(true)?>/media/com_jdonation/assets/images/payments_override/<?php echo $payment_name?>.png" style="height:30px;" alt="<?php echo Text::_($method->getTitle()); ?>"/>
										<?php
									}
									elseif(file_exists(JPATH_ROOT.'/media/com_jdonation/assets/images/payments/'.$payment_name.'.png'))
									{
										?>
										<img src="<?php echo Uri::base(true)?>/media/com_jdonation/assets/images/payments/<?php echo $payment_name?>.png" style="height:30px;" alt="<?php echo Text::_($method->getTitle()); ?>"/>
										<?php
									}
									else
									{
										echo Text::_($method->getTitle()); 
									}
								}
							}
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php
        }
        else
        {
            echo Text::_('JD_NO_DONORS_FOUND');
        }
        if ($this->pagination->total > $this->pagination->limit)
        {
            ?>
            <div class="pagination">
                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
            <?php
        }

        ?>
        <input type="hidden" name="option" value="com_jdonation"/>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="userdonors" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
