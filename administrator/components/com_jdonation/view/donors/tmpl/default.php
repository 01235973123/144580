<?php
/**
 * @version        5.1
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Dang Thuc Dam
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;


defined('_JEXEC') or die;
$config	= $this->config;
HTMLHelper::_('bootstrap.tooltip');
if(!DonationHelper::isJoomla4())
{
	ToolbarHelper::custom('donor.resendEmail','mail','mail','JD_RESEND_EMAIL',true);
}
else
{
	Factory::getApplication()->getDocument()->getWebAssetManager()
		->useScript('table.columns')
		->useScript('multiselect');
}
if (count($this->items))
{
    ToolBarHelper::custom('donor.export', 'download', 'download', 'JD_EXPORT_DONORS', false);
    ToolBarHelper::custom('donor.exportpdf', 'download', 'download', 'JD_EXPORT_PDF', false);
}

if (count($this->items))
{
    ToolBarHelper::custom('donor.home', 'home', 'home', 'JD_DASHBOARD', false);
}

ToolBarHelper::preferences('com_jdonation');

$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');

$customOptions = array(
	'filtersHidden'       => true,
	'defaultLimit'        => Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#filter_full_ordering'
);
HTMLHelper::_('searchtools.form', '#adminForm', $customOptions);

$db = Factory::getContainer()->get('db');
$query = $db->getQuery(true);
?>
<style>
    .icon-32-export
    {
        background-image:url("components/com_jdonation/assets/icons/export.png");
    }
</style>
<?php
if(DonationHelper::isJoomla4())
{
	$searchBtnClass = "btn-primary";
	$cancelSearchBtnClass = "btn-secondary";
}
else
{
	$searchBtnClass = "";
	$cancelSearchBtnClass = "";
}
?>
<form action="index.php?option=com_jdonation&view=donors" method="post" name="adminForm" id="adminForm">
    <?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>
    <div class="<?php echo $rowFluidClass; ?>">
		<?php
		if(DonationHelper::isJoomla4())
		{
			?>
			<div class="js-stools" role="search">
				<div class="js-stools-container-bar">
					<div id="filter-bar" class="btn-toolbar">
						<div class="btn-group">
							<div class="input-group input-append">
								<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip input-medium form-control" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER'); ?>" inputmode="search" aria-describedby="filter_search-desc"/>
								
								<button type="submit" class="btn <?php echo $searchBtnClass;?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
								
							</div>
						</div>
						<div class="btn-group">
							<button type="button" class="btn btn-primary js-stools-btn-filter">
								Filter Options	<span class="icon-angle-down" aria-hidden="true"></span>
							</button>
							<button type="button" class="btn <?php echo $cancelSearchBtnClass; ?> hasTooltip js-stools-btn-clear" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"><span class="icon-remove"></span></button>
						</div>
						<div class="ordering-select">
							<div class="js-stools-field-list">
								<?php
								echo $this->pagination->getLimitBox();
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="js-stools-container-filters clearfix <?php echo $this->showFilterForm;?>">
					<?php
					if ($this->config->show_pending_records == 1)
					{				
					?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['paid_status'];
							?>
						</div>
					<?php
					}		
					?>
					<?php
					if(DonationHelper::isMultipleCurrencies())
					{
					?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['currencies'];
							?>
						</div>
					<?php
					}		
					?>
					<?php
					if ($this->config->use_campaign)
					{
					?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['filter_campaign_id'] ;
							?>
						</div>
					<?php
					}		
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['filter_year'] ;
						?>
					</div>
					<?php
					if(DonationHelper::isAvailablePayments())
					{
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['payments'] ;
						?>
					</div>
					<?php
					}
					if($config->enable_hide_donor)
					{
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['anonymous'] ;
						?>
					</div>
					<?php
					}
					if($config->enable_gift_aid)
					{
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['gift_aid'] ;
						?>
					</div>
					<?php
					}
					?>
					<div class="js-stools-field-filter">
						<?php echo HTMLHelper::_('calendar', Factory::getApplication()->input->getString('start_date',''), 'start_date', 'start_date','%Y-%m-%d', array('class' => 'input-small form-control ishort','placeholder' => Text::_('JD_FROM'))) ; ?>
					</div>
					<div class="js-stools-field-filter">
						<?php echo HTMLHelper::_('calendar', Factory::getApplication()->input->getString('end_date',''), 'end_date', 'end_date','%Y-%m-%d', array('class' => 'input-small form-control ishort', 'placeholder' => Text::_('JD_TO'))) ; ?>
					</div>
				</div>
			<?php
		}
		else
		{
			//Filter form on Joomla 3
			?>
			<div class="js-stools clearfix" role="search">
				<div class="clearfix">
					<div class="js-stools-container-bar">
						<div id="filter-bar" class="js-stools-container-bar">
							<div class="btn-wrapper input-append">
								<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip input-medium form-control" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER'); ?>" inputmode="search" aria-describedby="filter_search-desc"/>
								
								<button type="submit" class="btn <?php echo $searchBtnClass;?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
								
							</div>
							<div class="btn-wrapper">
								<button type="button" class="btn hasTooltip js-stools-btn-filter">
									Filter Options	<span class="caret" aria-hidden="true"></span>
								</button>
								<button type="button" class="btn <?php echo $cancelSearchBtnClass; ?> hasTooltip js-stools-btn-clear" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_campaign_id').value='0';document.getElementById('filter_paid_status').value='-1';document.getElementById('currency').value='';document.getElementById('filter_year').value='0';document.getElementById('start_date').value='';document.getElementById('end_date').value='';document.getElementById('filter_hide').value='-1';this.form.submit();"><span class="icon-remove"></span></button>
							</div>
						</div>
					</div>
					<div class="js-stools-container-list hidden-phone hidden-tablet shown">
						<div class="js-stools-field-list">
							<?php
							echo $this->pagination->getLimitBox();
							?>
						</div>
					</div>
				</div>
				<div class="js-stools-container-filters clearfix <?php echo $this->showFilterForm;?>">
					<?php
					if ($this->config->show_pending_records == 1)
					{				
					?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['paid_status'];
							?>
						</div>
					<?php
					}		
					?>
					<?php
					if(DonationHelper::isMultipleCurrencies())
					{
					?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['currencies'];
							?>
						</div>
					<?php
					}		
					?>
					<?php
					if ($this->config->use_campaign)
					{
					?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['filter_campaign_id'] ;
							?>
						</div>
					<?php
					}		
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['filter_year'] ;
						?>
					</div>
					<?php
					if(DonationHelper::isAvailablePayments())
					{
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['payments'] ;
						?>
					</div>
					<?php
					}
					if($config->enable_hide_donor)
					{
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['anonymous'] ;
						?>
					</div>
					<?php
					}
					if($config->enable_gift_aid)
					{
					?>
					<div class="js-stools-field-filter">
						<?php
						echo $this->lists['gift_aid'] ;
						?>
					</div>
					<?php
					}
					?>
					<div class="js-stools-field-filter">
						<?php echo HTMLHelper::_('calendar', Factory::getApplication()->input->getString('start_date',''), 'start_date', 'start_date','%Y-%m-%d', array('class' => 'input-small form-control ishort','placeholder' => Text::_('JD_FROM'))) ; ?>
					</div>
					<div class="js-stools-field-filter">
						<?php echo HTMLHelper::_('calendar', Factory::getApplication()->input->getString('end_date',''), 'end_date', 'end_date','%Y-%m-%d', array('class' => 'input-small form-control ishort', 'placeholder' => Text::_('JD_TO'))) ; ?>
					</div>
				</div>
			</div>
<?php
		}
		?>
		<!--
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
				<div class="input-group input-append">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="input-large form-control hasTooltip" />
            
					<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
					<button type="button" class="btn btn-warning hasTooltip js-stools-btn-clear" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
				</div>
            </div>
            <div class="btn-group pull-right hidden-phone">
                <?php
				if(DonationHelper::isMultipleCurrencies())
				{
					echo $this->lists['currencies'];
				}
                if ($this->config->use_campaign)
                {
                    echo $this->lists['filter_campaign_id'] ;
                }
				echo $this->lists['filter_year'] ;
				if ($this->config->show_pending_records == 1){
					echo $this->lists['paid_status'];
				}
                ?>
            </div>
			-->
        </div>
    </div>
    <div id="editcell">
        <?php
        if(count($this->items) > 0) 
		{

			if (!DonationHelper::isJoomla4())
			{
				$tableClass = "table-striped";
			}
			else
			{
				$tableClass = "itemList";
			}
			?>
            <table class="adminlist table <?php echo $tableClass;?>">
            <thead>
            <tr>
                <th width="5" class="center">
                    <?php echo Text::_( '#' ); ?>
                </th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                </th>
                <th class="title text_left" style="width:18%;">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_FIRST_NAME', 'tbl.first_name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <th class="title text_left">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_LAST_NAME', 'tbl.last_name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <th class="title">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_DONATION_DATE', 'tbl.created_date', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <?php
					if($this->config->use_campaign)
					{
						$cols = 12;
						?>
						<th class="title">
							<?php echo HTMLHelper::_('grid.sort',  'JD_CAMPAIGN', 'cp.title', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>
						<?php
					}
					else
					{
						$cols = 11;
					}
                    if ($this->config->enable_recurring)
                    {
                        $cols += 3;
                    ?>
                        <th class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_DONATION_TYPE', 'tbl.donation_type', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>
						<?php
						if(!DonationHelper::isJoomla4())
						{
						?>
                        <th class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_FREQUENCY', 'tbl.r_frequency', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>
                        <th class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_NUMBER_PAYMENTS', 'tbl.r_times', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>
						<?php } ?>
                    <?php
                    }
                    else
                    {
                        //$cols = 11;
                    }
                ?>
                <th class="title center">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_AMOUNT', 'tbl.amount', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <th class="title">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_PAYMENT_METHOD', 'tbl.payment_method', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <th class="title">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_TRANSACTION_ID', 'tbl.transaction_id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <?php 
				if($this->config->store_ip_address == 1 && !DonationHelper::isJoomla4())
				{
				?>
                <th class="title">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_USER_IP', 'tbl.ip_address', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
                <?php } ?>
                <th class="title">
                    <?php echo HTMLHelper::_('grid.sort',  'JD_PAID', 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
				<?php
				if($config->activate_donation_receipt_feature)
				{
					$cols++;
					?>
					<th width="3%" nowrap="nowrap">
						<?php echo Text::_('JD_INVOICE'); ?>
					</th>
					<?php
				}
				?>
                <th width="1%" nowrap="nowrap">
                    <?php echo HTMLHelper::_('grid.sort',  'ID', 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="<?php echo $cols; ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
            <?php
            $k = 0;
            for ($i=0, $n=count( $this->items ); $i < $n; $i++)
            {
                $row = &$this->items[$i];
                $link 	= Route::_( 'index.php?option=com_jdonation&task=donor.edit&id='. $row->id );
                $checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
                $published = HTMLHelper::_('jgrid.published', $row->published, $i) ;
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td class="center">
                        <?php echo $this->pagination->getRowOffset( $i ); ?>
                    </td>
                    <td>
                        <?php echo $checked; ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>" style="text-decoration:none;">
                            <?php echo $row->first_name; ?>
                        </a>
                        <?php
                        if ($row->user_id)
                        {
                            ?>
                            <span class="admin-tag">
								<a href="index.php?option=com_users&task=user.edit&id=<?php echo $row->user_id; ?>">
									<?php echo $row->username; ?>
								</a>
                            </span>
                        <?php
                        }
                        ?>
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
						if($row->hide_me == 1)
						{
							?>
                            <BR />
							<span class="dedicated">
								<?php
								echo Text::_('JD_HIDE_DONOR');
								?>
							</span>
							<?php
						}
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->last_name; ?>
                        </a>
                    </td>
                    <td class="center" >
                        <?php 
						echo HTMLHelper::_('date', $row->created_date, $this->config->date_format);
						?>
                    </td>

                    <?php
						if($this->config->use_campaign)
						{
							?>
							<td>
								<?php echo $row->title; ?>
							</td>
							<?php
						}
                        if ($this->config->enable_recurring)
                        {
                        ?>
                            <td class="center">
                                <?php
                                if ($row->donation_type == 'R')
                                {
                                    echo Text::_('JD_RECURRING') ;
									if($row->recurring_donation_cancelled == 1)
									{
										?>
										<div class="clearfix"></div>
										<span style="color:red;">(<?php echo Text::_('JD_RECURRING_DONATION_CANCELLED');?>)</span>
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
											if( $row->recurring_donation_cancelled == 0 && (($row->r_times > 0 && $row->r_times > $row->payment_made) || $payment_method == "os_stripe"))
											{
												?>
												<div class="clearfix"></div>
												<a href="javascript:void(0);" onclick="javascript:cancelRecurringDonation(<?php echo $row->id;?>);">
													<?php echo Text::_('JD_CANCEL_DONATION');?>
												</a>
												<?php
											}
										}
									}
										
                                }
                                else
                                {
                                    echo Text::_('JD_ONETIME') ;
                                }
                                ?>
                            </td>
							<?php
							if(!DonationHelper::isJoomla4())
							{
							?>
                            <td class="center" style="font-size:12px;">
                                <?php
								if ($row->donation_type == 'R')
								{
									if ($row->r_frequency)
									{
										switch ($row->r_frequency)
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
                            </td>
                            <td class="center" style="font-weight: 600;">
                                <?php
                                if ($row->donation_type == 'R')
                                {
                                    if (!$row->r_times)
                                    {
                                        $times = 'Un-limit' ;
                                    }
                                    else
                                    {
                                        $times = $row->r_times ;
                                    }
                                    echo $row->payment_made.' / '.$times ;
                                }
                                ?>
                            </td>
							<?php } ?>
                        <?php
                        }
                    ?>
                    <td class="center">
                        <?php
						$show_amount = 0;
                        if(DonationHelper::isMultipleCurrencies())
						{
                            if(($row->amount_converted > 0) && ($row->currency_converted != $row->currency_code) && $row->currency_converted !='' )
							{
								$show_amount = 1;
                                ?>
                                <span class="amount">
                                    <?php
                                    echo number_format($row->amount_converted , 2);
                                    echo "&nbsp;";
                                    echo "<strong style='font-size:12px;'>";
                                    echo $row->currency_converted;
                                    echo "</strong>";
                                ?>
                                </span>
                                <div class="clearfix"></div>
                                <?php
								
								if($this->config->convert_currency_before_donation)
								{
									$show_amount = 1;
									?>
									<span class="amount">
										<?php
										echo number_format($row->amount , 2);
										echo "&nbsp;";
										echo "<strong style='font-size:12px;'>";
										if($row->currency_code != ""){
											echo $row->currency_code;
										}else{
											echo $this->config->currency;
										}
										echo "</strong>";
										?>
									</span>
									<?php
								}
                            }
							else
							{
								$show_amount = 1;
								?>
								<span class="amount">
									<?php
									echo number_format($row->amount , 2);
									echo "&nbsp;";
									echo "<strong style='font-size:12px;'>";
									if($row->currency_code != ""){
										echo $row->currency_code;
									}else{
										echo $this->config->currency;
									}
									echo "</strong>";
									?>
								</span>
								<?php
							}
                        }

						if($show_amount == 0)
						{
							?>
							<span class="amount">
								<?php
								echo number_format($row->amount , 2);
								echo "&nbsp;";
								echo "<strong style='font-size:12px;'>";
								if($row->currency_code != ""){
									echo $row->currency_code;
								}else{
									echo $this->config->currency;
								}
								echo "</strong>";
								?>
							</span>
							<?php
						}			
						?>
                    </td>
                    <td class="center">
                        <?php
                        $method = os_jdpayments::getPaymentMethod($row->payment_method);
                        if ($method)
                        {
                            echo $method->getTitle();
                        }
                        ?>
                    </td>
                    <td class="center" style="font-size:12px;">
                        <?php echo $row->transaction_id ;?>
                    </td>
                    <?php if($this->config->store_ip_address == 1 && !DonationHelper::isJoomla4()){?>
                        <td style="font-size:12px;">
                            <?php echo $row->ip_address ; ?>
                        </td>
                    <?php } ?>
                    <td class="center">
                        <?php echo $published ; ?>
                    </td>
					<?php
					if($config->activate_donation_receipt_feature)
					{
					?>
						<td class="center" style="text-align:center;">
							<?php
							if(!$config->generated_invoice_for_paid_donation_only || ($config->generated_invoice_for_paid_donation_only && $row->published == 1))
							{
							?>
								<a href="<?php echo Uri::root().'administrator/index.php?option=com_jdonation&f=1&task=download_receipt&id='.$row->id; ?>" title="<?php echo Text::_('JD_DOWNLOAD'); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
									  <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
									  <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.716 5.716 0 0 1-.911-.95 11.642 11.642 0 0 0-1.997.406 11.311 11.311 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.647 12.647 0 0 1 1.01-.193 11.666 11.666 0 0 1-.51-.858 20.741 20.741 0 0 1-.5 1.05zm2.446.45c.15.162.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.881 3.881 0 0 0-.612-.053zM8.078 5.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
									</svg>
								</a>
							<?php
							}	
							?>
						</td>
					<?php } ?>
                    <td class="center">
                        <?php echo $row->id; ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
            </tbody>
            </table>
        <?php }else{
            ?>
            <div class="jd-no-items-wrapper">
				<div class="jd-alert-no-items alert alert-info text-center">
					<i class="fas fa-search-minus fa-2x mb-2 text-secondary"></i><br>
					<?php echo Text::_('JD_NO_MATCHING_RESULTS'); ?>
				</div>
			</div>
        <?php
        } ?>
    </div>
</div>
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
    function cancelRecurringDonation(id)
    {
        var answer = confirm("<?php echo Text::_('JD_DO_YOU_WANT_TO_CANCEL_THE_RECURRING_DONATION');?>");
        if(answer == 1)
        {
            location.href = "<?php echo Uri::root(); ?>administrator/index.php?option=com_jdonation&task=donor.cancelrecurringdonation&id=" + id;
        }
    }
</script>
