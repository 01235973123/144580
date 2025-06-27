<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

ToolbarHelper::title(Text::_('ESHOP_DASHBOARD'), 'generic.png');
?>
<div class="clearfix">
	<div class="dashboard-left">
		<div class="bs-example bs-shop-statistics">
			<table class="table dashboard-table">
				<thead>
					<tr>
						<th style="width: 35%"><?php echo Text::_('ESHOP_SHOP_INFORMATION'); ?></th>
						<th style="width: 35%"><?php echo Text::_('ESHOP_ORDERS'); ?></th>
						<?php
						if (EShopHelper::getConfigValue('show_eshop_update', 1))
						{
							?>
							<th style="width: 30%"><?php echo Text::_('ESHOP_UPDATE_CHECKING'); ?></th>
							<?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=products'); ?>"><?php echo $this->shopStatistics['products']. ' ' . Text::_('ESHOP_PRODUCTS'); ?></a><br/>
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=categories'); ?>"><?php echo $this->shopStatistics['categories']. ' ' . Text::_('ESHOP_CATEGORIES'); ?></a><br/>
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=manufacturers'); ?>"><?php echo $this->shopStatistics['manufacturers']. ' ' . Text::_('ESHOP_MANUFACTURERS'); ?></a><br/>
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=customers'); ?>"><?php echo $this->shopStatistics['customers']. ' ' . Text::_('ESHOP_CUSTOMERS'); ?></a><br/>
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=reviews'); ?>"><?php echo $this->shopStatistics['reviews']. ' ' . Text::_('ESHOP_REVIEWS'); ?></a>
						</td>
						<td>
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=orders&order_status_id=' . intval(EShopHelper::getConfigValue('order_status_id', 8))); ?>"><?php echo $this->shopStatistics['pending_orders'].' '.Text::_('ESHOP_PENDING'); ?></a><br />
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=orders&order_status_id=' . intval(EShopHelper::getConfigValue('complete_status_id', 4))); ?>"><?php echo $this->shopStatistics['complete_orders'].' '.Text::_('ESHOP_COMPLETED'); ?></a><br />
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=orders&order_status_id=' . intval(EShopHelper::getConfigValue('shipped_status_id', 13))); ?>"><?php echo $this->shopStatistics['shipped_orders'].' '.Text::_('ESHOP_SHIPPED'); ?></a><br />
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=orders&order_status_id=' . intval(EShopHelper::getConfigValue('canceled_status_id', 1))); ?>"><?php echo $this->shopStatistics['canceled_orders'].' '.Text::_('ESHOP_CANCELED'); ?></a><br />
							<a href="<?php echo Route::_('index.php?option=com_eshop&view=orders&order_status_id=' . intval(EShopHelper::getConfigValue('refunded_status_id', 7))); ?>"><?php echo $this->shopStatistics['failed_orders'].' '.Text::_('ESHOP_FAILED'); ?></a>
						</td>
						<?php
						if (EShopHelper::getConfigValue('show_eshop_update', 1))
						{
							?>
							<td>
								<div id="cpanel">
									<div id="update-check">
										<div class="icon">
											<a href="index.php?option=com_installer&view=update" title="<?php echo Text::_('ESHOP_UPDATE_CHECKING'); ?>">
												<img src="<?php echo Uri::base(true); ?>/components/com_eshop/assets/icons/icon-48-update-found.png" alt="<?php echo Text::_('ESHOP_UPDATE_CHECKING'); ?>" title="<?php echo Text::_('ESHOP_UPDATE_CHECKING'); ?>">
												<span><?php echo Text::_('ESHOP_UPDATE_CHECKING'); ?></span>
											</a>
										</div>
									</div>
								</div>	
							</td>
							<?php
						}
						?>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="bs-example bs-recentorders">
			<table class="table dashboard-table">
				<thead>
					<tr>
						<th style="width: 10%;"><?php echo Text::_('ESHOP_ORDER_ID'); ?></th>
						<th style="width: 20%"><?php echo Text::_('ESHOP_CUSTOMER'); ?></th>
						<th style="width: 15%;"><?php echo Text::_('ESHOP_ORDER_STATUS'); ?></th>
						<th style="width: 10%;"><?php echo Text::_('ESHOP_TOTAL'); ?></th>
						<th style="width: 20%;"><?php echo Text::_('ESHOP_DATE_ADDED'); ?></th>
						<th style="width: 10%;"><?php echo Text::_('ESHOP_ACTION'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (empty($this->recentOrders))
					{
						?>
						<tr>
							<td colspan="6"><?php echo Text::_('ESHOP_NO_ORDERS'); ?></td>
						</tr>
						<?php
						}
					else
					{
						foreach ($this->recentOrders as $order)
						{
							?>
							<tr>
								<td><?php echo $order->id; ?></td>
								<td><?php echo $order->firstname . ' ' . $order->lastname; ?></td>
								<td><?php echo $order->orderstatus_name; ?></td>
								<td><?php echo $this->currency->format($order->total, $order->currency_code, $order->currency_exchanged_value); ?></td>
								<td>
									<?php
									if ($order->created_date != $this->nullDate)
										echo HTMLHelper::_('date', $order->created_date,EShopHelper::getConfigValue('date_format', 'm-d-Y'), null);
									?>
								</td>
								<td><a href="<?php echo Route::_('index.php?option=com_eshop&task=order.edit&cid[]='.$order->id); ?>"><?php echo Text::_('ESHOP_VIEW'); ?></a></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
		
		<div class=" bs-top-sales">
			<div class="bs-example ">
			<table class="table dashboard-table">
				<thead>
					<tr>
						<th style="width: 70%;"><?php echo Text::_('ESHOP_PRODUCT'); ?></th>
						<th style="width: 30%;"><?php echo Text::_('ESHOP_NUMBER_SALES'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (empty($this->topSales))
					{
						?>
						<tr>
							<td colspan="3"><?php echo Text::_('ESHOP_NO_ORDERS'); ?></td>
						</tr>
						<?php
					}
					else
					{
						foreach ($this->topSales as $product)
						{
							?>
							<tr>
								<td><?php echo $product->product_name; ?></td>
								<td><?php echo $product->sales; ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
			</div>
		</div>
		<div class=" bs-top-hits">
		<div class="bs-example ">
			<table class="table dashboard-table">
				<thead>
					<tr>
						<th style="width: 70%;"><?php echo Text::_('ESHOP_PRODUCT'); ?></th>
						<th style="width: 30%;"><?php echo Text::_('ESHOP_NUMBER_HITS'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (empty($this->topHits))
					{
						?>
						<tr>
							<td colspan="3"><?php echo Text::_('ESHOP_NO_PRODUCTS'); ?></td>
						</tr>
						<?php
					}
					else
					{
						foreach ($this->topHits as $product)
						{
							?>
							<tr>
								<td><?php echo $product->product_name; ?></td>
								<td><?php echo $product->hits; ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
		</div>
	</div>
	<div class="dashboard-right">
		<div class="bs-example bs-monthlyreport">
			<table class="table dashboard-table">
				<tbody>
					<tr>
						<td>
						<?php
						global $currentMonthOffset;
						$currentMonthOffset = (int)date('m');
						$input = Factory::getApplication()->input;
						if ($input->getInt('month') != 0)
							$currentMonthOffset = $input->getInt('month');
						$rootUri = Uri::root(true);
						?>
						<script type="text/javascript" src="<?php echo $rootUri; ?>/administrator/components/com_eshop/assets/js/jquery.flot.min.js"></script>
						<script type="text/javascript" src="<?php echo $rootUri; ?>/administrator/components/com_eshop/assets/js/jquery.flot.pie.min.js"></script>
							<div class="monthly-stats">
								<p>
									<?php
									if($currentMonthOffset != date('m'))
									{
										?><a href="index.php?option=com_eshop&amp;view=dashboard&amp;month=<?php echo $currentMonthOffset + 1; ?>" class="next"><?php echo Text::_('ESHOP_NEXT_MONTH'); ?></a>
										<?php
									}
									?>
									<a href="index.php?option=com_eshop&amp;view=dashboard&amp;month=<?php echo $currentMonthOffset - 1; ?>" class="previous"><?php echo Text::_('ESHOP_PREVIOUS_MONTH'); ?></a>
								</p>
								<div class="inside">
									<div id="placeholder" style="width:100%; height:300px; position:relative;"></div>
										<script type="text/javascript">
										/* <![CDATA[ */
										jQuery(function(){
											function weekendAreas(axes)
											{
												var markings = [];
												var d = new Date(axes.xaxis.min);
												// go to the first Saturday
												d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7));
												d.setUTCSeconds(0);
												d.setUTCMinutes(0);
												d.setUTCHours(0);
												var i = d.getTime();
												do
												{
													// when we don't set yaxis, the rectangle automatically
													// extends to infinity upwards and downwards
													markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
													i += 7 * 24 * 60 * 60 * 1000;
												}
												while(i < axes.xaxis.max);
												return markings;
											}
											<?php
											global $currentMonthOffset;
											$month = $currentMonthOffset;
											$year = (int) date('Y');
											$firstDay = strtotime("{$year}-{$month}-01");
											$lastDay = strtotime('-1 second', strtotime('+1 month', $firstDay));
											$after = date('Y-m-d H:i:s', $firstDay);
											$before = date('Y-m-d H:i:s', $lastDay);
											$orders = $this->model->getMonthlyReport($currentMonthOffset, $before, $after);
											$orderCounts = array();
											$orderAmounts = array();
											// Blank date ranges to begin
											$month = $currentMonthOffset;
											$year = (int) date('Y');
											$firstDay = strtotime("{$year}-{$month}-01");
											$lastDay = strtotime('-1 second', strtotime('+1 month', $firstDay));
											if ((date('m') - $currentMonthOffset)==0) :
												$upTo = date('d', strtotime('NOW'));
											else :
												$upTo = date('d', $lastDay);
											endif;
											$count = 0;
											while ($count < $upTo)
											{
												$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $firstDay))).'000';
												$orderCounts[$time] = 0;
												$orderAmounts[$time] = 0;
												$count++;
											}
											if ($orders)
											{
												foreach ($orders as $order)
												{
													$time = strtotime(date('Ymd', strtotime($order->created_date))) . '000';
													if (isset($orderCounts[$time]))
													{
														$orderCounts[$time]++;
													}
													else
													{
														$orderCounts[$time] = 1;
													}
													if (isset($orderAmounts[$time]))
													{
														$orderAmounts[$time] = $orderAmounts[$time] + $order->total;
													}
													else
													{
														$orderAmounts[$time] = (float) $order->total;
													}
												}
											}
											?>
											var d = [
												<?php
												$values = array();
												foreach ($orderCounts as $key => $value)
												{
													$values[] = "[$key, $value]";
												}
												echo implode(',', $values);
												?>
											];
											for(var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
											var d2 = [
												<?php
												$values = array();
												foreach ($orderAmounts as $key => $value)
												{
													$values[] = "[$key, $value]";
												}
												echo implode(',', $values);
												?>
											];
											for(var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;
											var plot = jQuery.plot(jQuery("#placeholder"), [
												{ label: "<?php echo Text::_('ESHOP_NUMBER_OF_SALES'); ?>", data: d },
												{ label: "<?php echo Text::_('ESHOP_SALES_AMOUNT'); ?>", data: d2, yaxis: 2 }
											], {
												series: {
													lines: { show: true },
													points: { show: true }
												},
												grid: {
													show: true,
													aboveData: false,
													color: '#ccc',
													backgroundColor: '#fff',
													borderWidth: 2,
													borderColor: '#ccc',
													clickable: false,
													hoverable: true,
													markings: weekendAreas
												},
												xaxis: {
													mode: "time",
													timeformat: "%d %b",
													tickLength: 1,
													minTickSize: [1, "day"]
												},
												yaxes: [
													{ min: 0, tickSize: 1, tickDecimals: 0 },
													{ position: "right", min: 0, tickDecimals: 2 }
												],
												colors: ["#21759B", "#ed8432"]
												});
											function showTooltip(x, y, contents){
												jQuery('<div id="tooltip">' + contents + '</div>').css({
													position: 'absolute',
													display: 'none',
													top: y + 5,
													left: x + 5,
													border: '1px solid #fdd',
													padding: '2px',
													'background-color': '#fee',
													opacity: 0.80
												}).appendTo("body").fadeIn(200);
											}
											var previousPoint = null;
											jQuery("#placeholder").bind("plothover", function(event, pos, item){
												if(item){
													if(previousPoint != item.dataIndex){
														previousPoint = item.dataIndex;
														jQuery("#tooltip").remove();
														if(item.series.label == "<?php echo Text::_('ESHOP_NUMBER_OF_SALES','jigoshop'); ?>"){
															var y = item.datapoint[1];
															showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);
														} else {
															var y = item.datapoint[1].toFixed(2);
															showTooltip(item.pageX, item.pageY, item.series.label + " - <?php echo $this->defaultCurrency->left_symbol; ?>" + y + "<?php echo $this->defaultCurrency->right_symbol; ?>");
														}
													}
												}
												else {
													jQuery("#tooltip").remove();
													previousPoint = null;
												}
											});
										});
									/* ]]> */
									</script>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="bs-example bs-recentreviews">
			<table class="table dashboard-table">
				<thead>
					<tr>
						<th style="width: 10%; "><?php echo Text::_('ESHOP_ID'); ?></th>
						<th style="width: 20%;"><?php echo Text::_('ESHOP_AUTHOR'); ?></th>
						<th style="width: 15%;"><?php echo Text::_('ESHOP_RATING'); ?></th>
						<th style="width: 10%"><?php echo Text::_('ESHOP_REVIEW_STATUS'); ?></th>
						<th style="width: 20%;"><?php echo Text::_('ESHOP_DATE_ADDED'); ?></th>
						<th style="width: 10%;"><?php echo Text::_('ESHOP_ACTION'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (empty($this->recentReviews))
					{
						?>
						<tr>
							<td colspan="6"><?php echo Text::_('ESHOP_NO_REVIEWS'); ?></td>
						</tr>
						<?php
					}
					else
					{
						foreach ($this->recentReviews as $review)
						{
							?>
							<tr>
								<td><?php echo $review->id; ?></td>
								<td><?php echo $review->author; ?></td>
								<td><?php echo $review->rating; ?></td>
								<td><span class="icon-<?php echo ($review->published) ? 'publish' : 'unpublish'; ?>"></span></td>
								<td>
									<?php
									if ($review->created_date != $this->nullDate)
										echo HTMLHelper::_('date', $review->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null);
									?>
								</td>
								<td><a href="<?php echo Route::_('index.php?option=com_eshop&task=review.edit&cid[]='.$review->id); ?>"><?php echo Text::_('ESHOP_VIEW'); ?></a></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
if (EShopHelper::getConfigValue('show_eshop_update', 1))
{
	$baseUri = Uri::base(true);
	?>
	<script type="text/javascript">
	    var upToDateImg = '<?php echo $baseUri.'/components/com_eshop/assets/icons/icon-48-up-to-date.png' ?>';
	    var updateFoundImg = '<?php echo $baseUri.'/components/com_eshop/assets/icons/icon-48-update-found.png';?>';
	    var errorFoundImg = '<?php echo $baseUri.'/components/com_eshop/assets/icons/icon-48-deny.png';?>';
	    jQuery(document).ready(function() {
	        jQuery.ajax({
	            type: 'POST',
	            url: 'index.php?option=com_eshop&task=checkUpdate',
	            dataType: 'json',
	            success: function(msg, textStatus, xhr)
	            {
	                if (msg.status == 1)
	                {
	                    jQuery('#update-check').find('img').attr('src', upToDateImg).attr('title', msg.message);
	                    jQuery('#update-check').find('span').text(msg.message);
	                }
	                else if (msg.status == 2)
	                {
	                    jQuery('#update-check').find('img').attr('src', updateFoundImg).attr('title', msg.message);
	                    jQuery('#update-check').find('a').attr('href', 'index.php?option=com_installer&view=update');
	                    jQuery('#update-check').find('span').text(msg.message);
	                }
	                else
	                {
	                    jQuery('#update-check').find('img').attr('src', errorFoundImg);
	                    jQuery('#update-check').find('span').text('<?php echo Text::_('ESHOP_UPDATE_CHECKING_ERROR'); ?>');
	                }
	            },
	            error: function(jqXHR, textStatus, errorThrown)
	            {
	                jQuery('#update-check').find('img').attr('src', errorFoundImg);
	                jQuery('#update-check').find('span').text('<?php echo Text::_('ESHOP_UPDATE_CHECKING_ERROR'); ?>');
	            }
	        });
	    });
	</script>	
	<?php
}