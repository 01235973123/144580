<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('jquery.framework');
$jinput					= Factory::getApplication()->input;
$config					= Factory::getConfig();
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span5Class				= $bootstrapHelper->getClassMapping('span5');

?>
<script type="text/javascript" src="<?php echo Uri::root(); ?>media/com_jdonation/assets/js/jquery.flot.min.js"></script>
<script type="text/javascript" src="<?php echo Uri::root(); ?>media/com_jdonation/assets/jquery.flot.pie.min.js"></script>
<form name="adminForm" id="adminForm" action="index.php?option=com_jdonation&view=statistic" method="post">
    <div class="<?php echo $rowFluidClass; ?>" style="background-color: #f0f1f5;" id="donationStatistic">
        <div class="<?php echo $span12Class; ?>">
            <div class="donationGraph">
                <table class="table dashboard-table" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="dashboard-table-header" style="width:50%;">
                            <?php echo Text::_('JD_DONATION_REPORT'); ?>
                        </td>
						<td class="dashboard-table-header" style="width:50%; text-align:right;padding-right:30px;">
							<a href="<?php echo Uri::root();?>administrator/index.php?option=com_jdonation&view=statistic&tmpl=component&campaignId=<?php echo $jinput->getInt('campaignId', 0);?>&payment_method=<?php echo $jinput->getString('payment_method','');?>&time_period=<?php echo $jinput->getString('time_period', '0');?>" title="Print this page" target="_blank">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
								  <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
								  <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
								</svg>
							</a>
						</td>
                    </tr>
                    <tr>
                        <td style="width:100%;" colspan="2">
                            <?php
                            global $currentMonthOffset;
                            $currentMonthOffset = (int)date('m');
                            if ($jinput->getInt('month',0) != 0)
                                $currentMonthOffset = $jinput->getInt('month',0);
                            ?>
                            <div class="monthly-stats">
                                <p>
                                    <label>
                                        <strong><?php echo Text::_('JD_FILTER');?>:</strong>
                                    </label>
                                    <div style="display:flex;gap:10px;">
                                        <?php
                                        if($this->config->use_campaign)
                                        {
                                            echo $this->lists['campaigns'];
                                        }
                                        echo $this->lists['payment_method'];
                                        echo $this->lists['time_period'];
                                        ?>
                                    </div>
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
                                            $month          = $currentMonthOffset;
                                            $year           = (int) date('Y');
                                            $firstDay       = strtotime("{$year}-{$month}-01");
                                            $lastDay        = strtotime('-1 second', strtotime('+1 month', $firstDay));
                                            $after          = date('Y-m-d H:i:s', $firstDay);
                                            $before         = date('Y-m-d H:i:s', $lastDay);

                                            switch ($this->time_period)
                                            {
                                                case 'this_week':
                                                    $date   = Factory::getDate('now', $config->get('offset'));
                                                    $monday = clone $date->modify( 'Monday this week');
                                                    $monday->setTime(0, 0, 0);
                                                    $monday->setTimezone(new DateTimeZone('UCT'));
                                                    $fromDate = $monday->toSql(true);
                                                    $sunday   = clone $date->modify('Sunday this week');
                                                    $sunday->setTime(23, 59, 59);
                                                    $sunday->setTimezone(new DateTimeZone('UCT'));
                                                    $toDate = $sunday->toSql(true);
                                                    break;
                                                case 'current_month':
                                                    $date = Factory::getDate('now', $config->get('offset'));
                                                    $date->setDate($date->year, $date->month, 1);
                                                    $date->setTime(0, 0, 0);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $fromDate = $date->toSql(true);
                                                    $date     = Factory::getDate('now', $config->get('offset'));
                                                    $date->setDate($date->year, $date->month, $date->daysinmonth);
                                                    $date->setTime(23, 59, 59);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $toDate = $date->toSql(true);
                                                    break;
                                                case 'last_month':
                                                    $date = Factory::getDate('first day of last month', $config->get('offset'));
                                                    $date->setTime(0, 0, 0);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $fromDate = $date->toSql(true);
                                                    $date     = Factory::getDate('last day of last month', $config->get('offset'));
                                                    $date->setTime(23, 59, 59);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $toDate = $date->toSql(true);
                                                    break;
                                                case 'this_year':
                                                    $date = Factory::getDate('now', $config->get('offset'));
                                                    $date->setDate($date->year, 1, 1);
                                                    $date->setTime(0, 0, 0);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $fromDate = $date->toSql(true);
                                                    $date     = Factory::getDate('now', $config->get('offset'));
                                                    $date->setDate($date->year, 12, 31);
                                                    $date->setTime(23, 59, 59);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $toDate = $date->toSql(true);
                                                    break;
                                                case 'last_year':
                                                    $date = Factory::getDate('now', $config->get('offset'));
                                                    $date->setDate($date->year - 1, 1, 1);
                                                    $date->setTime(0, 0, 0);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $fromDate = $date->toSql(true);
                                                    $date     = Factory::getDate('now', $config->get('offset'));
                                                    $date->setDate($date->year - 1, 12, 31);
                                                    $date->setTime(23, 59, 59);
                                                    $date->setTimezone(new DateTimeZone('UCT'));
                                                    $toDate = $date->toSql(true);
                                                    break;
                                            }

                                            $orders         = DonationModelDonors::getMonthlyReport($this->time_period, $this->campaignId, $this->payment_method);
                                            $orderCounts    = array();
                                            $orderAmounts   = array();
                                            // Blank date ranges to begin
                                            $firstDay       = strtotime($fromDate);
                                            $lastDay        = strtotime($toDate);
                                            if ((date('m') - $currentMonthOffset)==0) :
                                                $upTo       = date('d', strtotime('NOW'));
                                            else :
                                                $upTo       = date('d', $lastDay);
                                            endif;
                                            $count          = 0;
                                            while ($count < $upTo)
                                            {
                                                $time       = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $firstDay))).'000';
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
                                                        $orderAmounts[$time] = $orderAmounts[$time] + $order->amount;
                                                    }
                                                    else
                                                    {
                                                        $orderAmounts[$time] = (float) $order->amount;
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
                                                { label: "<?php echo Text::_('JD_NUMBER_DONATIONS'); ?>", data: d },
                                                { label: "<?php echo Text::_('JD_TOTAL_DONATED_AMOUNT'); ?>", data: d2, yaxis: 2 }
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
                                                colors: ["#ffbbb3", "#b53526"]
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
                                                        if(item.series.label == "<?php echo Text::_('JD_NUMBER_DONATIONS','jigoshop'); ?>"){
                                                            var y = item.datapoint[1];
                                                            showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);
                                                        } else {
                                                            var y = item.datapoint[1].toFixed(2);
                                                            showTooltip(item.pageX, item.pageY, item.series.label + " - <?php echo '$'; ?>" + y);
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
							</td>
						</tr>
                    </tbody>
                </table>
            </div>
            <?php
            if($this->config->use_campaign)
            {
                ?>
                <div class="campaignProgressBars">
                    <div class="campaignProgressBarsheading">
                        <?php
                        echo strtoupper(Text::_('JD_CAMPAIGNS_STATISTIC'));
                        ?>
                    </div>
                    <?php
                    foreach ($this->campaigns as $item)
                    {
						if($item->goal > 0)
						{
							$donatedPercent = ceil($item->total_donated/ $item->goal *100);
						}
						else
						{
							$donatedPercent = 100;
						}
                        ?>
                        <div class="<?php echo $rowFluidClass; ?>">
                            <div class="<?php echo $span12Class; ?>">
                                <strong>
                                    <?php
                                    echo $item->title;
                                    ?>
                                </strong>
                                <?php
                                if($item->start_date != "" && $item->start_date != "0000-00-00 00:00:00")
                                {
                                    ?>
                                    <span class="campaignCreated">
                                        <?php echo $item->start_date; ?>
                                    </span>
                                    <?php
                                }
                                ?>
                                <div class="progress">
                                    <div class="bar" style="width: <?php echo $donatedPercent; ?>%"></div>
                                </div>
                                <div class="<?php echo $rowFluidClass; ?>">
                                    <div class="span6" style="color:gray;">
                                        <?php echo Text::_('JD_RAISED'); ?>
                                        <strong>
                                            <?php echo DonationHelperHtml::formatAmount($this->config, $item->total_donated,$item->currency_symbol); ?>
                                        </strong>
                                        &nbsp;
                                        -
                                        &nbsp;
                                        <?php echo Text::_('JD_DONATED'); ?>
                                        <strong>
                                            <?php echo $donatedPercent; ?>%
                                        </strong>
                                    </div>
                                    <div class="span6" style="text-align: right;">
                                        <?php echo Text::_('JD_GOAL'); ?>
                                        <strong>
                                            <?php echo DonationHelperHtml::formatAmount($this->config, $item->goal,$item->currency_symbol) ; ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            if(count($this->donated_by_countries))
            {
                ?><div class="countriesStatistic">
                    <div class="countriesStatisticheading">
                        <?php
                        echo strtoupper(Text::_('JD_DONATION_RECORDS_BY_LOCATION'));
                        ?>
                    </div>
                    <?php
                    foreach ($this->donated_by_countries as $row)
                    {
                        ?>
                        <div class="country_title">
                            <?php
                            if($row->country != "")
                            {
                                echo $row->country;
                            }
                            else
                            {
                                echo "Not set";
                            }
                            ?>
                        </div>
                        <div class="donated_amount">
                            <?php echo DonationHelperHtml::formatAmount($this->config, $row->donated_amount,$row->currency_symbol); ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
			<?php
            if($this->config->use_campaign)
            {
                ?>
				<div class="countriesStatistic">
                    <div class="countriesStatisticheading">
                        <?php
                        echo strtoupper(Text::_('JD_AVERAGE_COMPOSITION'));
                        ?>
                    </div>
                    <?php
					$dataPoints = [];
					$total_donated_amount = 0;
					foreach ($this->campaigns as $item)
                    {
						$total_donated_amount += $item->total_donated;
					}

					foreach ($this->campaigns as $item)
                    {
						$percentage = 100*$item->total_donated/$total_donated_amount;
						$dataPoints[] = ["label"=>$item->title, "symbol" => $item->title,"y"=> $percentage];
					}					
					 
					?>
					
					<script>
					window.onload = function() {
					 
					var chart = new CanvasJS.Chart("chartContainer", {
						theme: "light2",
						animationEnabled: true,
						title: {
							text: ""
						},
						data: [{
							type: "doughnut",
							indexLabel: "{symbol} - {y}",
							yValueFormatString: "#,##0.0\"%\"",
							showInLegend: true,
							legendText: "{label} : {y}",
							dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
						}]
					});
					chart.render();
					 
					}
					</script>
					
					<div id="chartContainer" style="height: 370px; width: 100%;"></div>
					<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
                </div>
				<?php
			}
			?>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
<?php
if($jinput->input->getString('tmpl','') == "component")
{
	?>
		window.print();
	<?php
}
?>
</script>
