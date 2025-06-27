<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;

HTMLHelper::_('behavior.core');

$document = Factory::getApplication()->getDocument();
$document->addScriptOptions('currencySymbol', $this->config->currency_symbol);

$strings = [
    'no_country_data' => Text::_('JD_NO_COUNTRY_DATA'),
    'total_donation_by_country' => Text::_('JD_TOTAL_DONATION_BY_COUNTRY'),
    'total_donations' => Text::_('JD_TOTAL_DONATIONS'),
    'select_dates' => Text::_('JD_PLEASE_SELECT_BOTH_START_AND_END_DATES'),
    'donation_amount' => Text::_('JD_DONATION_AMOUNT'),
    'ended_today' => Text::_('JD_ENDED_TODAY'),
    'days_left' => Text::_('JD_DAYS_LEFT'),
    'ago' => Text::_('JD_AGO'),
    'just_now' => Text::_('JD_JUST_NOW')
];
$document->addScriptOptions('languageStrings', $strings);
?>

<div class="dashboard-container">
    <?php if (!$this->hasData) : ?>
        <!-- Empty State UI - No Data Available -->
        <div class="empty-state">
            <div class="welcome-banner">
                <div class="welcome-icon">
                    <i class="icon-heart"></i>
                </div>
                <div class="welcome-content">
                    <h1><?php echo Text::_('JD_WELCOME'); ?></h1>
                    <p><?php echo Text::_('JD_WELCOME_DESCRIPTION'); ?></p>
                </div>
            </div>

            <div class="getting-started">
                <h2><?php echo Text::_('JD_GETTING_STARTED'); ?></h2>
                <p><?php echo Text::_('JD_GETTING_STARTED_DESC'); ?></p>
                
                <div class="steps-container">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="icon-cog"></i>
                        </div>
                        <h3><?php echo Text::_('JD_CONFIGURE_SETTINGS'); ?></h3>
                        <p><?php echo Text::_('JD_CONFIGURE_SETTINGS_DESC'); ?></p>
                        <a href="<?php echo Route::_('index.php?option=com_jdonation&view=configuration'); ?>" class="btn-primary">
                            <?php echo Text::_('JD_CONFIGURE_SETTINGS'); ?>
                        </a>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="icon-bullhorn"></i>
                        </div>
                        <h3><?php echo Text::_('JD_CREATE_CAMPAIGN'); ?></h3>
                        <p><?php echo Text::_('JD_CREATE_CAMPAIGN_DESC'); ?></p>
                        <a href="<?php echo Route::_('index.php?option=com_jdonation&view=campaign&layout=edit'); ?>" class="btn-primary">
                            <?php echo Text::_('JD_CREATE_CAMPAIGN'); ?>
                        </a>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="icon-palette"></i>
                        </div>
                        <h3><?php echo Text::_('JD_CUSTOMIZE_FIELDS'); ?></h3>
                        <p><?php echo Text::_('JD_CUSTOMIZE_FIELDS_DESC'); ?></p>
                        <a href="<?php echo Route::_('index.php?option=com_jdonation&view=fields'); ?>" class="btn-primary">
                            <?php echo Text::_('JD_CUSTOMIZE_FIELDS'); ?>
                        </a>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <i class="icon-globe"></i>
                        </div>
                        <h3><?php echo Text::_('JD_PUBLISH_PAYMENT_PLUGINS'); ?></h3>
                        <p><?php echo Text::_('JD_PUBLISH_PAYMENT_PLUGINS_EXPLAIN'); ?></p>
                        <a href="<?php echo Route::_('index.php?option=com_jdonation&view=plugins'); ?>" class="btn-primary">
                            <?php echo Text::_('JD_PUBLISH_PAYMENT'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <!--
            <div class="sample-data">
                <div class="sample-data-card">
                    <div class="sample-data-icon">
                        <i class="icon-database"></i>
                    </div>
                    <div class="sample-data-content">
                        <h3><?php echo Text::_('JD_SAMPLE_DATA_TITLE'); ?></h3>
                        <p><?php echo Text::_('JD_SAMPLE_DATA_DESC'); ?></p>
                        <button class="btn-secondary" id="install-sample-data">
                            <?php echo Text::_('JD_INSTALL_SAMPLE_DATA'); ?>
                        </button>
                    </div>
                </div>
            </div>
            -->
        </div>
    <?php else : ?>
        <!-- Dashboard UI - When Data Available -->
        <div class="jdsidebar">
            <div class="logo-container">
                <img src="https://joomdonation.com/images/logo.png" alt="Joom Donation" class="logo">
            </div>
            <!-- Alert trong sidebar -->
            <?php
            $updateResult = $this->updateResult;
            if($updateResult['status'] == 2)
            {
            ?>
                <div class="jd-sidebar-alert">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span>
                        New version <?php echo $updateResult['version']?> available!<br>
                        <a href="https://joomdonation.com/joomla-extensions/joom-donation-joomla-paypal-donation.html" target="_blank">Update now</a>
                    </span>
                    <button type="button" class="jd-alert-close" onclick="this.parentElement.style.display='none';" title="Dismiss">&times;</button>
                </div>
            <?php
            }
            ?>

            <nav class="jdsidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="icon-dashboard"></i> <?php echo Text::_('JD_DASHBOARD'); ?></a></li>
                    <li><a href="<?php echo Route::_('index.php?option=com_jdonation&view=campaigns'); ?>"><i class="icon-flag"></i> <?php echo Text::_('JD_CAMPAIGNS'); ?></a></li>
                    <li><a href="<?php echo Route::_('index.php?option=com_jdonation&view=donors'); ?>"><i class="icon-users"></i> <?php echo Text::_('JD_DONORS'); ?></a></li>
                    <li><a href="<?php echo Route::_('index.php?option=com_jdonation&view=fields'); ?>"><i class="icon-list"></i> <?php echo Text::_('JD_CUSTOM_FIELDS'); ?></a></li>
                    <li><a href="<?php echo Route::_('index.php?option=com_jdonation&view=plugins'); ?>"><i class="icon-credit"></i> <?php echo Text::_('JD_PAYMENTS'); ?></a></li>
                    <li><a href="<?php echo Route::_('index.php?option=com_jdonation&view=reports'); ?>"><i class="icon-chart"></i> <?php echo Text::_('JD_REPORTS'); ?></a></li>
                    <li><a href="<?php echo Route::_('index.php?option=com_jdonation&view=configuration'); ?>"><i class="icon-cog"></i> <?php echo Text::_('JD_SETTINGS'); ?></a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p><?php echo Text::_('JD_VERSION'); ?>: <?php echo DonationHelper::getInstalledVersion();?></p>
            </div>
        </div>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1><?php echo Text::_('JD_DASHBOARD'); ?></h1>
                
                <div class="date-filter">
                    <select id="date-range" class="form-select">
                        <option value="7days"><?php echo Text::_('JD_LAST_7_DAYS'); ?></option>
                        <option value="30days"><?php echo Text::_('JD_LAST_30_DAYS'); ?></option>
                        <option value="90days"><?php echo Text::_('JD_LAST_90_DAYS'); ?></option>
                        <option value="year"><?php echo Text::_('JD_THIS_YEAR'); ?></option>
                        <option value="custom"><?php echo Text::_('JD_CUSTOM_RANGE'); ?></option>
                    </select>
                    
                    <div id="custom-date-container" class="custom-date-container hidden">
                        <input type="date" id="date-start" class="form-control">
                        <span>to</span>
                        <input type="date" id="date-end" class="form-control">
                        <button id="apply-date" class="btn btn-sm btn-primary"><?php echo Text::_('JD_APPLY'); ?></button>
                    </div>
                </div>
            </div>
            
            <!-- Overview Cards -->
            <div class="overview-cards">
                <div class="overview-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                        <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                        <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                        <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3><?php echo Text::_('JD_TOTAL_DONATIONS'); ?></h3>
                        <p class="card-value"><?php echo DonationHelperHtml::formatAmount($this->config, $this->statistics['total_amount']); //echo HTMLHelper::_('number.currency', $this->statistics['total_amount'], 'USD'); ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="card-icon">
                        <i class="icon-users"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo Text::_('JD_TOTAL_DONORS'); ?></h3>
                        <p class="card-value"><?php echo number_format($this->statistics['total_donors']); ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="card-icon">
                        <i class="icon-flag"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo Text::_('JD_ACTIVE_CAMPAIGNS'); ?></h3>
                        <p class="card-value"><?php echo number_format($this->statistics['total_campaigns']); ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-bar-chart-fill" viewBox="0 0 16 16">
                        <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1z"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3><?php echo Text::_('JD_AVG_DONATION'); ?></h3>
                        <p class="card-value"><?php echo DonationHelperHtml::formatAmount($this->config, $this->statistics['avg_donation']); //echo HTMLHelper::_('number.currency', $this->statistics['avg_donation'], 'USD'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Donation Time Chart -->
            <div class="charts-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <h2><?php echo Text::_('JD_DONATION_TRENDS'); ?></h2>
                        <div class="chart-actions">
                            <button data-type="daily" class="btn-chart-type active"><?php echo Text::_('JD_DAILY'); ?></button>
                            <button data-type="weekly" class="btn-chart-type"><?php echo Text::_('JD_WEEKLY'); ?></button>
                            <button data-type="monthly" class="btn-chart-type"><?php echo Text::_('JD_MONTHLY'); ?></button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="donationsTimeChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h2><?php echo Text::_('JD_CAMPAIGN_DISTRIBUTION'); ?></h2>
                        <select id="campaign-chart-type" class="form-select">
                            <option value="amount"><?php echo Text::_('JD_BY_AMOUNT'); ?></option>
                            <option value="count"><?php echo Text::_('JD_BY_DONORS'); ?></option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="campaignDistributionChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Campaigns Section -->
            <div class="campaigns-section">
                <div class="campaigns-card">
                    <h2><?php echo Text::_('JD_TOP_CAMPAIGNS'); ?></h2>
                    <div class="campaigns-list">
                        <?php foreach ($this->topCampaigns as $campaign) : ?>
                            <div class="campaign-item">
                                <h3><?php echo $campaign->title; ?></h3>
                                <div class="campaign-stats">
                                    <span class="raised"><?php echo DonationHelperHtml::formatAmount($this->config, $campaign->donated_amount); // echo HTMLHelper::_('number.currency', $campaign->donated_amount, 'USD'); ?></span>
                                    <span class="of"><?php echo Text::_('JD_OF'); ?></span>
                                    <span class="goal"><?php echo DonationHelperHtml::formatAmount($this->config, $campaign->goal); //echo HTMLHelper::_('number.currency', $campaign->goal, 'USD'); ?></span>
                                </div>
                                <div class="progress">
                                    <?php $percentage = ($campaign->goal > 0) ? min(100, ($campaign->donated_amount / $campaign->goal) * 100) : 0; ?>
                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <div class="campaign-meta">
                                    <span class="donors">
                                        <i class="icon-users"></i> <?php echo $campaign->donors_count; ?> <?php echo Text::_('JD_DONORS'); ?>
                                    </span>
                                    <a href="<?php echo Route::_('index.php?option=com_jdonation&view=campaign&id=' . $campaign->id); ?>" class="campaign-link">
                                        <?php echo Text::_('JD_VIEW_DETAILS'); ?> <i class="icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                 
                <div class="campaigns-card">
                    <h2><?php echo Text::_('JD_ENDING_SOON'); ?></h2>
                    <div class="campaigns-list">
                        <?php foreach ($this->endingSoonCampaigns as $campaign) : ?>
                            <?php 
                                $endDate = new DateTime($campaign->end_date);
                                $now = new DateTime();
                                $interval = $now->diff($endDate);
                                $daysLeft = $interval->days;
                                $percentage = ($campaign->goal > 0) ? min(100, ($campaign->donated_amount / $campaign->goal) * 100) : 0;
                            ?>
                            <div class="campaign-item">
                                <h3><?php echo $campaign->title; ?></h3>
                                <div class="campaign-stats">
                                    <span class="raised"><?php echo DonationHelperHtml::formatAmount($this->config, $campaign->donated_amount); //HTMLHelper::_('number.currency', $campaign->donated_amount, 'USD'); ?></span>
                                    <span class="of"><?php echo Text::_('JD_OF'); ?></span>
                                    <span class="goal"><?php echo DonationHelperHtml::formatAmount($this->config, $campaign->goal); //HTMLHelper::_('number.currency', $campaign->goal, 'USD'); ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <div class="campaign-meta">
                                    <span class="time-left countdown" data-end="<?php echo $campaign->end_date; ?>">
                                        <i class="icon-clock"></i> 
                                        <?php echo $daysLeft; ?> <?php echo Text::_('JD_DAYS_LEFT'); ?>
                                    </span>
                                    <a href="<?php echo Route::_('index.php?option=com_jdonation&view=campaign&id=' . $campaign->id); ?>" class="campaign-link">
                                                                                <?php echo Text::_('JD_VIEW_DETAILS'); ?> <i class="icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Donors Section -->
            <div class="donors-section">
                <div class="map-card">
                    <h2><?php echo Text::_('JD_DONOR_LOCATIONS'); ?></h2>
                    <div class="map-container">
                        <div class="donor-locations-list">
                            <h3><?php echo Text::_('JD_TOP_LOCATIONS'); ?></h3>
                            <ul>
                                <?php foreach ($this->donorLocations as $location) : ?>
                                    <div class="location-item">
                                        <span class="country-name"><?php echo $location->country; ?></span>
                                        <div class="location-stats">
                                            <span class="donor-count"><?php echo $location->count; ?> <?php echo Text::_('JD_DONORS'); ?></span>
                                            <span class="amount"><?php echo DonationHelperHtml::formatAmount($this->config, $location->total_amount); //HTMLHelper::_('number.currency', $location->total_amount, 'USD'); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="recent-donations-card">
                    <h2><?php echo Text::_('JD_RECENT_DONATIONS'); ?></h2>
                    <div class="donations-list">
                        <?php foreach ($this->recentDonations as $donation) : ?>
                            <?php 
                                $donationDate = new DateTime($donation->payment_date);
                                $now = new DateTime();
                                $interval = $now->diff($donationDate);
                                
                                // Format the time ago
                                if ($interval->y > 0) {
                                    $timeAgo = $interval->format('%y ' . Text::_('JD_YEARS_AGO'));
                                } elseif ($interval->m > 0) {
                                    $timeAgo = $interval->format('%m ' . Text::_('JD_MONTHS_AGO'));
                                } elseif ($interval->d > 0) {
                                    $timeAgo = $interval->format('%d ' . Text::_('JD_DAYS_AGO'));
                                } elseif ($interval->h > 0) {
                                    $timeAgo = $interval->format('%h ' . Text::_('JD_HOURS_AGO'));
                                } elseif ($interval->i > 0) {
                                    $timeAgo = $interval->format('%i ' . Text::_('JD_MINUTES_AGO'));
                                } else {
                                    $timeAgo = Text::_('JD_JUST_NOW');
                                }
                            ?>
                            <div class="donation-item">
                                <div class="donor-avatar">
                                    <?php echo substr($donation->first_name, 0, 1) . substr($donation->last_name, 0, 1); ?>
                                </div>
                                <div class="donation-info">
                                    <h3><?php echo $donation->first_name . ' ' . $donation->last_name; ?></h3>
                                    <p class="donation-campaign"><?php echo Text::_('JD_DONATED_TO'); ?> <?php echo $donation->campaign_title; ?></p>
                                </div>
                                <div class="donation-amount">
                                    <span class="amount"><?php echo DonationHelperHtml::formatAmount($this->config, $donation->amount); //HTMLHelper::_('number.currency', $donation->amount, 'USD'); ?></span>
                                    <span class="time-ago"><?php echo $timeAgo; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="view-all-link">
                        <a href="<?php echo Route::_('index.php?option=com_jdonation&view=donors'); ?>">
                            <?php echo Text::_('JD_VIEW_ALL_DONATIONS'); ?> <i class="icon-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    <?php endif; ?>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dashboard with PHP data
        var dashboardData = {
            hasData: <?php echo $this->hasData ? 'true' : 'false'; ?>,
            <?php if ($this->hasData) : ?>
            donationTimeline: <?php echo json_encode($this->donationTimeline); ?>,
            campaignDistribution: <?php echo json_encode($this->campaignDistribution); ?>,
            donorLocations: <?php echo json_encode($this->donorLocations); ?>
            <?php endif; ?>
        };
        
        if (window.JoomDonation) {
            window.JoomDonation.Dashboard.init(dashboardData);
        }
    });
</script>

