<?php
/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;

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
$span2Class      	= $bootstrapHelper->getClassMapping('span2');
$span3Class      	= $bootstrapHelper->getClassMapping('span3');
$span5Class      	= $bootstrapHelper->getClassMapping('span5');
$span6Class      	= $bootstrapHelper->getClassMapping('span6');
$span7Class      	= $bootstrapHelper->getClassMapping('span7');
$span10Class      	= $bootstrapHelper->getClassMapping('span10');
$span12Class      	= $bootstrapHelper->getClassMapping('span12');
$Itemid				= $this->Itemid;
$config             = $this->config;
$user               = Factory::getApplication()->getIdentity();
?>
<script type="text/javascript">
    function deleteConfirm(id) {
        var msg = "<?php echo Text::_('JD_DELETE_CONFIRM'); ?>";
        if (confirm(msg)) {
            var form = document.jdform ;
            form.task.value = 'campaign.delete';
            document.getElementById('campaign_id').value = id;
            form.submit();
        }
    }
</script>
<?php
$canAdd	        = $user->authorise('core.create','com_jdonation') && $user->authorise('managecampaigns','com_jdonation');
$canEdit	    = $user->authorise('core.edit.own',			'com_jdonation') && $user->authorise('managecampaigns',			'com_jdonation');
$canDelete      = $user->authorise('core.delete',			'com_jdonation') && $user->authorise('managecampaigns',			'com_jdonation');
?>
<div id="donation-campaigns" class="<?php echo $rowFluidClass;?> dashboard-container">
    <form method="post" name="jdform" id="jdform" action="<?php echo Route::_('index.php?option=com_jdonation&view=usercampaigns&Itemid='.$this->Itemid); ?>">
        <main class="main-content">
            <div class="header-row">
	            <h1 class="page-title"><?php echo Text::_('JD_MY_CAMPAIGNS'); ?></h1>
            </div>
            <?php
            //$canAdd	    = $user->authorise('core.create', 'com_jdonation');
            if($canAdd) {
                ?>
                <div class="<?php echo $rowFluidClass ?>">
                    <div class="<?php echo $span12Class?> alignright addcampaign">
                        <a class="btn btn-success" href="<?php echo Route::_('index.php?option=com_jdonation&task=campaign.edit');?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#FFFFFF" class="bi bi-plus-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                            <?php echo Text::_('JD_ADD_CAMPAIGN');?>
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        <?php
        if (count($this->items))
        {
            ?>
            <table class="table table-striped table-bordered table-condensed" id="usercampaignstable">
                <thead>
                <tr>
                    <th><?php echo Text::_('JD_IMAGE');?></th>
                    <th class="jd-title-col">
                        <?php echo Text::_('JD_TITLE'); ?>
                    </th>
                    <th class="jd-date-col center">
                        <?php echo Text::_('JD_START_DATE'); ?> / <?php echo Text::_('JD_END_DATE'); ?>
                    </th>
                    <th class="jd-goal-col">
                        <?php echo Text::_('JD_GOAL'); ?> (<?php echo $this->config->currency_symbol?>)
                    </th>
                    <th class="jd-donated-col">
                        <?php echo Text::_('JD_RECEIVED'); ?> (<?php echo $this->config->currency_symbol?>)
                    </th>
                    <th class="jd-donated-col">
                        <?php echo Text::_('JD_PROCESS'); ?>
                    </th>
                    <th class="jd-published-col">
                        <?php echo Text::_('JD_PUBLISHED'); ?>
                    </th>
                    <th class="center jd-edit-delete">
						<?php
						echo Text::_('JD_DETAILS');
						?>
                        <?php
                        if($canEdit){
                        ?>
						/
						<?php
						echo Text::_('JD_EDIT');
						?>
                        <?php } ?>
                        <?php
                        if($$canDelete){
                        ?>
						/
						<?php
						echo Text::_('JD_DELETE');
						?>
                        <?php } ?>
					</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total = 0 ;
                $pieLabels = [];
                $pieData = [];
                for ($i = 0 , $n = count($this->items) ; $i < $n; $i++)
                {
                    $item           = $this->items[$i] ;
                    $pieLabels[]    = addslashes($item->title);
                    $pieData[]      = (int)$item->total_donated;
                    $url            = Route::_(DonationHelperRoute::getDonationFormRoute($item->id, $this->Itemid), false, $ssl);
                    ?>
                    <tr>
                        <td data-label="<?php echo Text::_('JD_IMAGE'); ?>">
                            <?php
                            if($item->campaign_photo != '')
                            {
                                if(is_file(Path::clean(JPATH_ROOT.'/images/jdonation/'.$item->campaign_photo)))
                                {
                                    $hasPicture = 1;
                                    $img = Uri::root(true).'/images/jdonation/'.$item->campaign_photo;
                                }
                                elseif(is_file(Path::clean(JPATH_ROOT.'/'.$item->campaign_photo)))
                                {
                                    $hasPicture = 1;
                                    $img = Uri::root(true).'/'.$item->campaign_photo;
                                }
                            }
                            if($hasPicture == 1)
                            {
                                ?>
                                <img src="<?php echo $img; ?>" class="campaign_photo" title="<?php echo $item->title; ?>" />
                                <?php
                            }
                            ?>
                        </td>
                        <td data-label="<?php echo Text::_('JD_TITLE'); ?>">
                            <a href="<?php echo $url; ?>" target="_blank"><?php echo $item->title; ?></a>
                        </td>
                        <td class="center" data-label="<?php echo Text::_('JD_DATE'); ?>">
							<?php
							if($item->start_date != "" && $item->start_date != "0000-00-00 00:00:00")
							{
							?>
                            <?php echo HTMLHelper::_('date', $item->start_date, $config->date_format, null); ?>
							<?php
							}
							else
							{
								echo "N/A";
							}
							?>
							<?php
							if($item->end_date != "" && $item->end_date != "0000-00-00 00:00:00")
							{
							?>
                            &nbsp;/&nbsp;
							
                            <?php echo HTMLHelper::_('date', $item->end_date, $config->date_format, null); ?>
							<?php
							}
							else
							{
								echo " &nbsp;/&nbsp; N/A";
							}
							?>
                        </td>
                        <td data-label="<?php echo Text::_('JD_GOAL'); ?>">
                            <?php echo $item->goal; ?>
                        </td>
                        <td data-label="<?php echo Text::_('JD_DONATED_AMOUNT'); ?>">
                            <?php echo (int)$item->total_donated; ?>
                        </td>
                        <td data-label="<?php echo Text::_('JD_PROCESS'); ?>">
                            <?php
                            $percent = ($item->goal > 0) ? round(((int)$item->total_donated / $item->goal) * 100) : 0;
                            if ($percent > 100) $percent = 100;
                            ?>
                            <div class="progress-bar-bg">
                                <div class="progress-bar" style="width:<?php echo $percent; ?>%;"></div>
                            </div>
                            <span style="font-size:0.98em;"><?php echo $percent; ?>%</span>
                        </td>
                        <td class="center">
                            <?php
                            if($item->published == 1)
                            {
                                ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="green" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
								  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
								</svg>
                                <?php
                            }
                            else
                            {
                                ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="red" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
								  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
								</svg>
                                <?php
                            }
                            ?>
                        </td>
                        <td class="center actions" data-label="<?php echo Text::_('JD_EDIT'); ?>/ <?php echo Text::_('JD_DELETE'); ?>" style="text-align:center;">
                            <?php
                            if($item->number_donors > 0)
                            {
                                ?>
                                <a class="action-btn view"
                                   href="<?php echo Route::_('index.php?option=com_jdonation&view=userdonors&id=' . $item->id . '&Itemid=' . $Itemid); ?>" title="<?php
                                    printf(Text::_('JD_SEE_DONORS'), $item->number_donors);
                                    ?>">
                                    <?php echo Text::_('JD_DETAILS');?>
                                </a>
								
                                <?php
                            }
                            if($canEdit)
                            {
                                ?>
                                <a class="action-btn edit"
                                   href="<?php echo Route::_('index.php?option=com_jdonation&task=campaign.edit&id=' . $item->id . '&Itemid=' . $Itemid); ?>" title="<?php echo Text::_('JD_EDIT');?>">
                                    <?php echo Text::_('JD_EDIT');?>
                                </a>
                                <?php
                            }
                            if($canDelete)
                            {
                                ?>
                                <a class="action-btn delete" href="javascript:deleteConfirm(<?php echo $item->id; ?>);" title="<?php echo Text::_('JD_DELETE');?>">
                                    <?php echo Text::_('JD_DELETE');?>
                                </a>
                                <?php
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
            echo Text::_('JD_NO_CAMPAIGNS_FOUND');
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
        <input type="hidden" name="campaign_id" id="campaign_id" value="" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="Itemid" value="<?php echo Factory::getApplication()->input->getInt('Itemid',0);?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <div class="sticky-bar" id="openModalBar">
        <div class="bar-content">
            <span class="bar-icon">📊</span>
            <?php echo Text::_('JD_VIEW_INCOME_REPORT_TOP_DONORS'); ?>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Text::_('JD_INCOME_REPORT_AND_TOP_DONORS');?></h3>
                <button class="close-modal" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-section">
                <h4><?php echo Text::_('JD_INCOME_DISTRIBUTION_BY_CAMPAIGN');?></h4>
                <div class="pie-chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
            <div class="modal-section">
                <h4><?php echo Text::_('JD_TOP_DONORS');?></h4>
                <ul class="top-donors-list" id="topDonorsList">
                    <?php if (!empty($this->topDonors)): ?>
                        <?php foreach ($this->topDonors as $donor): ?>
                            <li class="donor-item">
                                <div class="donor-info">
                                    <span><?php echo htmlspecialchars($donor->first_name. ' '.$donor->last_name); ?></span>
                                    <?php if (!empty($donor->campaign_title)): ?>
                                        <div class="donor-campaign-title" style="font-size: 0.95em; color: #888;">
                                            <?php echo htmlspecialchars($donor->campaign_title); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="donor-amount"><?php echo DonationHelperHtml::formatAmount($this->config, $donor->amount); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><?php echo Text::_('JD_NO_DONORS_FOUND');?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
$colorPool = [
    '#42a5f5', '#7e57c2', '#ef5350', '#26a69a', '#ffa726', '#789262',
    '#d32f2f', '#388e3c', '#1976d2', '#fbc02d', '#0097a7', '#c2185b'
];

$backgroundColors = [];
for ($i = 0; $i < count($pieLabels); $i++) {
    $backgroundColors[] = $colorPool[$i % count($colorPool)];
}
?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Pie chart for modal
let pieChart = null;
function renderPieChart() {
    const ctx = document.getElementById('pieChart').getContext('2d');
    if(pieChart) pieChart.destroy();
    pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($pieLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($pieData); ?>,
                backgroundColor: <?php echo json_encode($backgroundColors); ?>
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 18, padding: 18 }
                }
            }
        }
    });
}

// Modal logic
const modalOverlay = document.getElementById('modalOverlay');
const openModalBar = document.getElementById('openModalBar');
const closeModalBtn = document.getElementById('closeModalBtn');

openModalBar.onclick = function() {
    modalOverlay.style.display = "flex";
    renderPieChart();
    document.body.style.overflow = "hidden";
};
closeModalBtn.onclick = function() {
    modalOverlay.style.display = "none";
    document.body.style.overflow = "";
};
modalOverlay.onclick = function(e) {
    if(e.target === modalOverlay) {
        modalOverlay.style.display = "none";
        document.body.style.overflow = "";
    }
};
</script>
