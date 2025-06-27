/**
 * Joom Donation Dashboard JavaScript
 * 
 * This file contains all the JavaScript functionality for the Joom Donation dashboard.
 */
window.JoomDonation = window.JoomDonation || {};

JoomDonation.Dashboard = (function() {
    'use strict';
    
    // Chart objects
    let donationsTimeChart = null;
    let campaignDistributionChart = null;
    let locationMap = null;

    const currencySymbol = Joomla.getOptions('currencySymbol');

    const chartStrings = Joomla.getOptions('languageStrings');
    // Configuration
    const config = {
        chartColors: [
            'rgba(74, 137, 220, 0.8)',  // Primary blue
            'rgba(93, 156, 236, 0.8)',  // Secondary blue
            'rgba(255, 107, 107, 0.8)', // Coral
            'rgba(55, 188, 155, 0.8)',  // Green
            'rgba(246, 187, 66, 0.8)',  // Yellow
            'rgba(218, 68, 83, 0.8)',   // Red
            'rgba(170, 178, 189, 0.8)', // Gray
        ],
        currencies: {
            'USD': '$',
            'EUR': '�',
            'GBP': '�'
        },
        currency: 'USD',  // Default currency
    };
    
    /**
     * Initialize the dashboard
     * 
     * @param {Object} data - Dashboard data from PHP
     */
    function init(data) {
        // Check if we're in empty state or have data
        
        if (!data.hasData) {
            initEmptyState();
            return;
        }
        // Initialize all dashboard components
        initDateRangeFilter();
        initDonationsTimeChart(data.donationTimeline);
        initCampaignDistributionChart(data.campaignDistribution);
        //initDonorCountryChart(data.donorLocations);
        initCountdowns();
        
        // Register event handlers
        registerEventHandlers();
    }

    let donorCountryChart = null;

    function initDonorCountryChart(data) {
        //console.log('Donor Country Chart Data:', data);

        const chartElement = document.getElementById('donorCountryChart');
        if (!chartElement || !data || !data.length) {
            if (chartElement) {
                chartElement.outerHTML = '<div class="map-placeholder">${chartStrings.no_country_data}</div>';
            }
            return;
        }

        // Gom nhóm donation theo country (dùng total_amount)
        const countryTotals = {};
        data.forEach(item => {
            const country = item.country || 'Unknown';
            if (!countryTotals[country]) countryTotals[country] = 0;
            countryTotals[country] += Number(item.total_amount) || 0;
        });

        // Lấy top 15 quốc gia
        const sortedCountries = Object.entries(countryTotals)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 15);

        const labels = sortedCountries.map(([country]) => country);
        const values = sortedCountries.map(([, total]) => total);

        // Tăng chiều rộng canvas nếu nhiều quốc gia
        //const chartElement = document.getElementById('donorCountryChart');
        chartElement.width = Math.max(600, labels.length * 60);

        if (donorCountryChart) donorCountryChart.destroy();

        donorCountryChart = new Chart(chartElement, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: chartStrings.total_donation_by_country,
                    data: values,
                    backgroundColor: 'rgba(74, 137, 220, 0.8)'
                }]
            },
            options: {
                responsive: false, // Để chiều rộng theo canvas
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        title: { display: true, text: 'Country' },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: false
                        }
                    },
                    y: {
                        title: { display: true, text: chartStrings.total_donations },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return formatCurrency(value, true); }
                        }
                    }
                }
            }
        });
    }


    
    /**
     * Initialize empty state functionality
     */
    function initEmptyState() {
        // Handle sample data installation
        const sampleDataBtn = document.getElementById('install-sample-data');
        if (sampleDataBtn) {
            sampleDataBtn.addEventListener('click', function() {
                if (confirm('This will install sample campaigns and donation data for demonstration purposes. Continue?')) {
                    // Show loading state
                    this.textContent = 'Installing...';
                    this.disabled = true;
                    
                    // AJAX call to install sample data
                    fetch('index.php?option=com_joomdonation&task=dashboard.installSampleData', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': Joomla.getOptions('csrf.token')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Sample data installed successfully! Refreshing dashboard...');
                            window.location.reload();
                        } else {
                            alert('Error installing sample data: ' + data.message);
                            this.textContent = 'Install Sample Data';
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error installing sample data:', error);
                        alert('Error installing sample data. Please try again.');
                        this.textContent = 'Install Sample Data';
                        this.disabled = false;
                    });
                }
            });
        }
        
        // Animate introduction of step cards
        const stepCards = document.querySelectorAll('.step-card');
        stepCards.forEach((card, index) => {
            // Add staggered animation delay for each card
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    }
    
    /**
     * Initialize date range filter
     */
    function initDateRangeFilter() {
        const dateRangeSelect = document.getElementById('date-range');
        const customDateContainer = document.getElementById('custom-date-container');
        const applyDateBtn = document.getElementById('apply-date');
        const dateStartInput = document.getElementById('date-start');
        const dateEndInput = document.getElementById('date-end');
        
        if (!dateRangeSelect) return;
        
        // Set default date range inputs to today and 30 days ago
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        if (dateStartInput && dateEndInput) {
            dateStartInput.valueAsDate = thirtyDaysAgo;
            dateEndInput.valueAsDate = today;
        }
        
        // Handle date range change
        dateRangeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateContainer.classList.remove('hidden');
            } else {
                customDateContainer.classList.add('hidden');
                updateDashboardWithDateRange(this.value);
            }
        });
        
        // Handle apply date button
        if (applyDateBtn) {
            applyDateBtn.addEventListener('click', function() {
                if (dateStartInput.value && dateEndInput.value) {
                    updateDashboardWithCustomDateRange(dateStartInput.value, dateEndInput.value);
                } else {
                    alert(chartStrings.select_dates);
                }
            });
        }
    }
    
    /**
     * Update dashboard with the selected date range
     * 
     * @param {string} range - Selected date range (e.g. '7days', '30days')
     */
    function updateDashboardWithDateRange(range) {
        const today = new Date();
        let startDate;
        
        switch(range) {
            case '7days':
                startDate = new Date();
                startDate.setDate(today.getDate() - 7);
                break;
            case '30days':
                startDate = new Date();
                startDate.setDate(today.getDate() - 30);
                break;
            case '90days':
                startDate = new Date();
                startDate.setDate(today.getDate() - 90);
                break;
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                break;
            default:
                startDate = new Date();
                startDate.setDate(today.getDate() - 30);
        }
        
        const formattedStartDate = formatDate(startDate);
        const formattedEndDate = formatDate(today);
        
        fetchDashboardData(formattedStartDate, formattedEndDate);
    }
    
    /**
     * Update dashboard with custom date range
     * 
     * @param {string} startDate - Start date in YYYY-MM-DD format
     * @param {string} endDate - End date in YYYY-MM-DD format
     */
    function updateDashboardWithCustomDateRange(startDate, endDate) {
        fetchDashboardData(startDate, endDate);
    }
    
    /**
     * Format a date as YYYY-MM-DD
     * 
     * @param {Date} date - Date to format
     * @return {string} Formatted date
     */
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    /**
     * Fetch dashboard data for the specified date range
     * 
     * @param {string} startDate - Start date in YYYY-MM-DD format
     * @param {string} endDate - End date in YYYY-MM-DD format
     */
    function fetchDashboardData(startDate, endDate) {
        // Show loading indicator
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.classList.add('loading');
        }
        
        // Fetch updated dashboard data
        const url = `index.php?option=com_jdonation&task=dashboard.getData&startDate=${startDate}&endDate=${endDate}&format=json`;

        // Alert URL ajax
        //alert('AJAX URL: ' + url);

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': Joomla.getOptions('csrf.token')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data);
                updateDashboardUI(data.data);
            } else {
                alert('Error fetching dashboard data: ' + data.message);
            }
            
            // Hide loading indicator
            if (mainContent) {
                mainContent.classList.remove('loading');
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            alert('Error fetching dashboard data. Please try again.');
            
            // Hide loading indicator
            if (mainContent) {
                mainContent.classList.remove('loading');
            }
        });
    }
    
    /**
     * Update dashboard UI with new data
     * 
     * @param {Object} data - Dashboard data
     */
    function updateDashboardUI(data) {
        // Update statistics cards
        //console.log(data.data.statistics);
        updateStatisticCards(data.data.statistics);
        
        // Update charts
        updateDonationsTimeChart(data.data.donationTimeline);
        updateCampaignDistributionChart(data.data.campaignDistribution);
        
        // Update other sections
        updateTopCampaigns(data.data.topCampaigns);
        updateEndingSoonCampaigns(data.data.endingSoonCampaigns);
        //updateDonorLocations(data.donorLocations);
        updateRecentDonations(data.data.recentDonations);
    }
    
    /**
     * Update statistics cards with new data
     * 
     * @param {Object} statistics - Statistics data
     */
    function updateStatisticCards(statistics) {
        //console.log(statistics);
        if (!statistics) return;
        
        // Update total amount
        const totalAmountElement = document.querySelector('.overview-card:nth-child(1) .card-value');
        if (totalAmountElement) {
            totalAmountElement.textContent = formatCurrency(statistics.total_amount);
        }
        
        // Update total donors
        const totalDonorsElement = document.querySelector('.overview-card:nth-child(2) .card-value');
        if (totalDonorsElement) {
            totalDonorsElement.textContent = formatNumber(statistics.total_donors);
        }
        
        // Update active campaigns
        const activeCampaignsElement = document.querySelector('.overview-card:nth-child(3) .card-value');
        if (activeCampaignsElement) {
            activeCampaignsElement.textContent = formatNumber(statistics.total_campaigns);
        }
        
        // Update average donation
        const avgDonationElement = document.querySelector('.overview-card:nth-child(4) .card-value');
        if (avgDonationElement) {
            avgDonationElement.textContent = formatCurrency(statistics.avg_donation);
        }
    }
    
    /**
     * Initialize donations time chart
     * 
     * @param {Array} data - Time series data for donations
     */
    function initDonationsTimeChart(data) {
        if (!data) return;
        
        const ctx = document.getElementById('donationsTimeChart');
        if (!ctx) return;
        
        // Format data for chart
        const labels = data.map(item => item.date_group);
        const amounts = data.map(item => item.total);
        const counts = data.map(item => item.count);
        
        // Create chart
        donationsTimeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: chartStrings.donation_amount,
                        data: amounts,
                        borderColor: config.chartColors[0],
                        backgroundColor: hexToRgba(config.chartColors[0], 0.1),
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: chartStrings.donation_amount,
                        data: counts,
                        borderColor: config.chartColors[1],
                        backgroundColor: hexToRgba(config.chartColors[1], 0.1),
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                
                                if (context.datasetIndex === 0) {
                                    return label + ': ' + formatCurrency(context.raw);
                                } else {
                                    return label + ': ' + context.raw + ' donations';
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Amount'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value, true);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Count'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Update donations time chart with new data
     * 
     * @param {Array} data - Time series data for donations
     */
    function updateDonationsTimeChart(data) {
        if (!donationsTimeChart || !data) return;
        
        // Format data for chart
        const labels = data.map(item => item.date_group);
        const amounts = data.map(item => item.total);
        const counts = data.map(item => item.count);
        
        // Update chart data
        donationsTimeChart.data.labels = labels;
        donationsTimeChart.data.datasets[0].data = amounts;
        donationsTimeChart.data.datasets[1].data = counts;
        
        // Update chart
        donationsTimeChart.update();
    }
    
    /**
     * Initialize campaign distribution chart
     * 
     * @param {Array} data - Campaign distribution data
     */
    function initCampaignDistributionChart(data) {
        if (!data) return;
        
        const ctx = document.getElementById('campaignDistributionChart');
        if (!ctx) return;
        
        // Format data for chart
        const labels = data.map(item => item.title);
        const amounts = data.map(item => item.total_amount);
        const counts = data.map(item => item.donors_count);
        const backgroundColors = data.map((_, index) => config.chartColors[index % config.chartColors.length]);
        
        // Create chart
        campaignDistributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [
                    {
                        data: amounts,
                        backgroundColor: backgroundColors,
                        hoverOffset: 10
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'start'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Add event listener for chart type change
        const chartTypeSelect = document.getElementById('campaign-chart-type');
        if (chartTypeSelect) {
            chartTypeSelect.addEventListener('change', function() {
                const chartType = this.value;
                let newData;
                
                if (chartType === 'amount') {
                    newData = amounts;
                } else {
                    newData = counts;
                }
                
                campaignDistributionChart.data.datasets[0].data = newData;
                campaignDistributionChart.options.plugins.tooltip.callbacks.label = function(context) {
                    const label = context.label || '';
                    const value = context.raw;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = Math.round((value / total) * 100);
                    
                    if (chartType === 'amount') {
                        return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                    } else {
                        return `${label}: ${value} donors (${percentage}%)`;
                    }
                };
                
                campaignDistributionChart.update();
            });
        }
    }
    
    /**
     * Update campaign distribution chart with new data
     * 
     * @param {Array} data - Campaign distribution data
     */
    function updateCampaignDistributionChart(data) {
        if (!campaignDistributionChart || !data) return;
        
        // Format data for chart
        const labels = data.map(item => item.title);
        const amounts = data.map(item => item.total_amount);
        const counts = data.map(item => item.donors_count);
        const backgroundColors = data.map((_, index) => config.chartColors[index % config.chartColors.length]);
        
        // Get current chart type
        const chartTypeSelect = document.getElementById('campaign-chart-type');
        const chartType = chartTypeSelect ? chartTypeSelect.value : 'amount';
        
        // Update chart data
        campaignDistributionChart.data.labels = labels;
        campaignDistributionChart.data.datasets[0].backgroundColor = backgroundColors;
        
        if (chartType === 'amount') {
            campaignDistributionChart.data.datasets[0].data = amounts;
        } else {
            campaignDistributionChart.data.datasets[0].data = counts;
        }
        
        // Update chart
        campaignDistributionChart.update();
    }
    
    /**
     * Initialize location map
     * 
     * @param {Array} data - Donor location data
     */
    function initLocationMap(data) {
        if (!data || typeof google === 'undefined' || !google.maps) {
            // If Google Maps is not loaded, show a message
            const mapElement = document.getElementById('donor-map');
            if (mapElement) {
                mapElement.innerHTML = '<div class="map-placeholder">Loading map data...</div>';
            }
            return;
        }
        const mapElement = document.getElementById('donor-map');
        if (!mapElement) return;
        
        // Create map
        locationMap = new google.maps.Map(mapElement, {
            center: { lat: 25, lng: 0 },
            zoom: 2,
            styles: [
                { elementType: "geometry", stylers: [{ color: "#f5f7fa" }] },
                { elementType: "labels.text.stroke", stylers: [{ color: "#f5f7fa" }] },
                { elementType: "labels.text.fill", stylers: [{ color: "#656d78" }] },
                { featureType: "water", elementType: "geometry", stylers: [{ color: "#c9d7de" }] },
                { featureType: "administrative", elementType: "geometry.stroke", stylers: [{ color: "#c9d7de" }] },
                { featureType: "administrative.land_parcel", stylers: [{ visibility: "off" }] },
                { featureType: "administrative.neighborhood", stylers: [{ visibility: "off" }] },
                { featureType: "road", stylers: [{ visibility: "off" }] },
                { featureType: "poi", stylers: [{ visibility: "off" }] }
            ]
        });
        
        // Add markers for each location
        updateDonorLocations(data);
    }

     
    /**
     * Update donor locations on the map
     * 
     * @param {Array} data - Donor location data
     */
    function updateDonorLocations(data) {
        if (!locationMap || !data) return;
        
        // Clear existing markers
        if (locationMap.markers) {
            locationMap.markers.forEach(marker => marker.setMap(null));
        }
        
        locationMap.markers = [];
        
        // Geocode each country and add a marker
        data.forEach(location => {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'address': location.country }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    const marker = new google.maps.Marker({
                        map: locationMap,
                        position: results[0].geometry.location,
                        title: location.country,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: Math.min(15, Math.max(5, Math.sqrt(location.count) * 3)),
                            fillColor: '#4a89dc',
                            fillOpacity: 0.7,
                            strokeColor: '#ffffff',
                            strokeWeight: 2
                        }
                    });
                    
                    // Add info window
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="map-info-window">
                                <h3>${location.country}</h3>
                                <p>${location.count} donors</p>
                                <p>${formatCurrency(location.total_amount)}</p>
                            </div>
                        `
                    });
                    
                    marker.addListener('click', function() {
                        infoWindow.open(locationMap, marker);
                    });
                    
                    locationMap.markers.push(marker);
                }
            });
        });
        
        // Update the donor locations list
        updateDonorLocationsList(data);
    }
    
    /**
     * Update the donor locations list
     * 
     * @param {Array} data - Donor location data
     */
    function updateDonorLocationsList(data) {
        if (!data) return;
        
        const locationsListContainer = document.querySelector('.donor-locations-list ul');
        if (!locationsListContainer) return;
        
        // Clear existing list
        locationsListContainer.innerHTML = '';
        
        // Add each location to the list
        data.forEach(location => {
            const locationItem = document.createElement('div');
            locationItem.className = 'location-item';
            locationItem.innerHTML = `
                <span class="country-name">${location.country}</span>
                <div class="location-stats">
                    <span class="donor-count">${location.count} donors</span>
                    <span class="amount">${formatCurrency(location.total_amount)}</span>
                </div>
            `;
            
            locationsListContainer.appendChild(locationItem);
        });
    }
    
    /**
     * Update top campaigns section with new data
     * 
     * @param {Array} data - Top campaigns data
     */
    function updateTopCampaigns(data) {
        if (!data) return;
        
        const campaignsListContainer = document.querySelector('.campaigns-card:nth-child(1) .campaigns-list');
        if (!campaignsListContainer) return;
        
        // Clear existing list
        campaignsListContainer.innerHTML = '';
        
        // Add each campaign to the list
        data.forEach(campaign => {
            const percentage = (campaign.goal > 0) ? Math.min(100, (campaign.donated_amount / campaign.goal) * 100) : 0;
            
            const campaignItem = document.createElement('div');
            campaignItem.className = 'campaign-item';
            campaignItem.innerHTML = `
                <h3>${campaign.title}</h3>
                <div class="campaign-stats">
                    <span class="raised">${formatCurrency(campaign.donated_amount)}</span>
                    <span class="of">of</span>
                    <span class="goal">${formatCurrency(campaign.goal)}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: ${percentage}%"></div>
                </div>
                <div class="campaign-meta">
                    <span class="donors">
                        <i class="icon-users"></i> ${campaign.donors_count} donors
                    </span>
                    <a href="index.php?option=com_joomdonation&view=campaign&id=${campaign.id}" class="campaign-link">
                        View Details <i class="icon-arrow-right"></i>
                    </a>
                </div>
            `;
            
            campaignsListContainer.appendChild(campaignItem);
        });
    }
    
    /**
     * Update ending soon campaigns section with new data
     * 
     * @param {Array} data - Ending soon campaigns data
     */
    function updateEndingSoonCampaigns(data) {
        if (!data) return;
        
        const campaignsListContainer = document.querySelector('.campaigns-card:nth-child(2) .campaigns-list');
        if (!campaignsListContainer) return;
        
        // Clear existing list
        campaignsListContainer.innerHTML = '';
        
        // Add each campaign to the list
        data.forEach(campaign => {
            const endDate = new Date(campaign.end_date);
            const now = new Date();
            const interval = Math.floor((endDate - now) / (1000 * 60 * 60 * 24));
            const daysLeft = Math.max(0, interval);
            
            const percentage = (campaign.goal > 0) ? Math.min(100, (campaign.donated_amount / campaign.goal) * 100) : 0;
            
            const campaignItem = document.createElement('div');
            campaignItem.className = 'campaign-item';
            campaignItem.innerHTML = `
                <h3>${campaign.title}</h3>
                <div class="campaign-stats">
                    <span class="raised">${formatCurrency(campaign.donated_amount)}</span>
                    <span class="of">of</span>
                    <span class="goal">${formatCurrency(campaign.goal)}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: ${percentage}%"></div>
                </div>
                <div class="campaign-meta">
                    <span class="time-left countdown" data-end="${campaign.end_date}">
                        <i class="icon-clock"></i> ${daysLeft} days left
                    </span>
                    <a href="index.php?option=com_joomdonation&view=campaign&id=${campaign.id}" class="campaign-link">
                        View Details <i class="icon-arrow-right"></i>
                    </a>
                </div>
            `;
            
            campaignsListContainer.appendChild(campaignItem);
        });
        
        // Reinitialize countdowns
        initCountdowns();
    }
    
    /**
     * Update recent donations section with new data
     * 
     * @param {Array} data - Recent donations data
     */
    function updateRecentDonations(data) {
        if (!data) return;
        
        const donationsListContainer = document.querySelector('.donations-list');
        if (!donationsListContainer) return;
        
        // Clear existing list
        donationsListContainer.innerHTML = '';
        
        // Add each donation to the list
        data.forEach(donation => {
            const donationDate = new Date(donation.payment_date);
            const now = new Date();
            const timeAgo = formatTimeAgo(donationDate, now);
            
            const donationItem = document.createElement('div');
            donationItem.className = 'donation-item';
            donationItem.innerHTML = `
                <div class="donor-avatar">
                    ${donation.first_name.substring(0, 1)}${donation.last_name.substring(0, 1)}
                </div>
                                <div class="donation-info">
                    <h3>${donation.first_name} ${donation.last_name}</h3>
                    <p class="donation-campaign">Donated to ${donation.campaign_title}</p>
                </div>
                <div class="donation-amount">
                    <span class="amount">${formatCurrency(donation.amount)}</span>
                    <span class="time-ago">${timeAgo}</span>
                </div>
            `;
            
            donationsListContainer.appendChild(donationItem);
        });
    }
    
    /**
     * Initialize countdown timers
     */
    function initCountdowns() {
        const countdownElements = document.querySelectorAll('.countdown');
        
        countdownElements.forEach(element => {
            const endDate = new Date(element.getAttribute('data-end'));
            
            // Update countdown immediately
            updateCountdown(element, endDate);
            
            // Update countdown every day
            setInterval(() => {
                updateCountdown(element, endDate);
            }, 86400000); // 24 hours
        });
    }
    
    /**
     * Update a countdown element
     * 
     * @param {Element} element - Countdown element
     * @param {Date} endDate - End date for countdown
     */
    function updateCountdown(element, endDate) {
        const now = new Date();
        const interval = Math.floor((endDate - now) / (1000 * 60 * 60 * 24));
        const daysLeft = Math.max(0, interval);
        
        if (daysLeft === 0) {
            element.innerHTML = '<i class="icon-clock"></i> ' + chartStrings.ended_today;
            element.classList.add('ended');
        } else {
            element.innerHTML = `<i class="icon-clock"></i> ${daysLeft} ` + chartStrings.days_left;
            
            // Add class for urgent countdowns
            if (daysLeft <= 3) {
                element.classList.add('urgent');
            } else {
                element.classList.remove('urgent');
            }
        }
    }
    
    /**
     * Format time ago string
     * 
     * @param {Date} date - Date to format
     * @param {Date} now - Current date
     * @return {string} Formatted time ago string
     */
    function formatTimeAgo(date, now) {
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) {
            return chartStrings.just_now;
        }
        
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) {
            return `${minutes} minute${minutes > 1 ? 's' : ''} ${chartStrings.ago}` ;
        }
        
        const hours = Math.floor(minutes / 60);
        if (hours < 24) {
            return `${hours} hour${hours > 1 ? 's' : ''} ${chartStrings.ago}`;
        }
        
        const days = Math.floor(hours / 24);
        if (days < 30) {
            return `${days} day${days > 1 ? 's' : ''} ${chartStrings.ago}`;
        }
        
        const months = Math.floor(days / 30);
        if (months < 12) {
            return `${months} month${months > 1 ? 's' : ''} ${chartStrings.ago}`;
        }
        
        const years = Math.floor(months / 12);
        return `${years} year${years > 1 ? 's' : ''} ${chartStrings.ago}`;
    }
     
    /**
     * Register event handlers
     */
    function registerEventHandlers() {
        // Chart type buttons for donation trends
        const chartTypeButtons = document.querySelectorAll('.btn-chart-type');
        chartTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                
                // Update active state
                chartTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Fetch new data for the selected chart type
                fetch(`index.php?option=com_joomdonation&task=dashboard.getTimelineData&type=${type}&format=json`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': Joomla.getOptions('csrf.token')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDonationsTimeChart(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching timeline data:', error);
                });
            });
        });
    }
    
    /**
     * Format a number with comma separators
     * 
     * @param {number} value - Number to format
     * @return {string} Formatted number
     */
    function formatNumber(value) {
        return new Intl.NumberFormat().format(value);
    }
    
    /**
     * Format a currency value
     * 
     * @param {number} value - Value to format
     * @param {boolean} abbreviated - Whether to abbreviate large numbers
     * @return {string} Formatted currency
     */
    function formatCurrency(value, abbreviated = false) {
        if (abbreviated && value >= 1000) {
            if (value >= 1000000) {
                return currencySymbol + (value / 1000000).toFixed(1) + 'M';
            } else {
                return currencySymbol + (value / 1000).toFixed(1) + 'K';
            }
        }
        
        return new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency: config.currency
        }).format(value);
    }
    
    /**
     * Convert hex color to rgba
     * 
     * @param {string} hex - Hex color
     * @param {number} alpha - Alpha value
     * @return {string} RGBA color
     */
    function hexToRgba(hex, alpha) {
        hex = hex.replace('rgba(', '').replace(')', '').split(',');
        return `rgba(${hex[0]}, ${hex[1]}, ${hex[2]}, ${alpha})`;
    }
    
    // Public API
    return {
        init: init
    };
})();

