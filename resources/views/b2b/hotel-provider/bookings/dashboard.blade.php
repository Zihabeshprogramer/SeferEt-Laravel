@extends('layouts.b2b')

@section('title', 'Booking Statistics Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-chart-line text-info mr-2"></i>
                Booking Statistics Dashboard
            </h1>
            <p class="text-muted">Comprehensive analytics and insights for your booking performance</p>
        </div>
        <div class="col-md-4 text-right">
            <div class="btn-group">
                <button class="btn btn-outline-info" id="refreshDashboard">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Refresh
                </button>
                <div class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                        <i class="fas fa-download mr-1"></i>
                        Export Reports
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="exportReport('summary')">
                            <i class="fas fa-file-pdf mr-2"></i>Summary Report (PDF)
                        </a>
                        <a class="dropdown-item" href="#" onclick="exportReport('detailed')">
                            <i class="fas fa-file-excel mr-2"></i>Detailed Report (Excel)
                        </a>
                        <a class="dropdown-item" href="#" onclick="exportReport('charts')">
                            <i class="fas fa-chart-bar mr-2"></i>Charts & Graphs (PNG)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label small mb-1">Time Period</label>
                                <select class="form-control form-control-sm" id="timePeriod">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month" selected>This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" id="customDateRange" style="display: none;">
                            <div class="form-group mb-0">
                                <label class="form-label small mb-1">Custom Date Range</label>
                                <input type="text" class="form-control form-control-sm" id="dateRangePicker" placeholder="Select date range">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label small mb-1">Hotel Filter</label>
                                <select class="form-control form-control-sm" id="hotelFilter">
                                    <option value="">All Hotels</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <label class="form-label small mb-1">&nbsp;</label>
                            <div>
                                <button class="btn btn-primary btn-sm btn-block" onclick="updateDashboard()">
                                    <i class="fas fa-search mr-1"></i>
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Bookings</span>
                    <span class="info-box-number" id="totalBookings">-</span>
                    <div class="progress">
                        <div class="progress-bar bg-white" id="totalBookingsProgress" style="width: 0%"></div>
                    </div>
                    <span class="progress-description" id="totalBookingsChange">vs previous period</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Confirmed</span>
                    <span class="info-box-number" id="confirmedBookings">-</span>
                    <div class="progress">
                        <div class="progress-bar bg-white" id="confirmedBookingsProgress" style="width: 0%"></div>
                    </div>
                    <span class="progress-description" id="confirmedBookingsChange">vs previous period</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number" id="pendingBookings">-</span>
                    <div class="progress">
                        <div class="progress-bar bg-white" id="pendingBookingsProgress" style="width: 0%"></div>
                    </div>
                    <span class="progress-description" id="pendingBookingsChange">needs attention</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Revenue</span>
                    <span class="info-box-number" id="totalRevenue">-</span>
                    <div class="progress">
                        <div class="progress-bar bg-white" id="totalRevenueProgress" style="width: 0%"></div>
                    </div>
                    <span class="progress-description" id="totalRevenueChange">vs previous period</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box bg-gradient-secondary">
                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Occupancy</span>
                    <span class="info-box-number" id="occupancyRate">-</span>
                    <div class="progress">
                        <div class="progress-bar bg-white" id="occupancyRateProgress" style="width: 0%"></div>
                    </div>
                    <span class="progress-description" id="occupancyRateChange">average rate</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box bg-gradient-dark">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ADR</span>
                    <span class="info-box-number" id="averageDailyRate">-</span>
                    <div class="progress">
                        <div class="progress-bar bg-white" id="averageDailyRateProgress" style="width: 0%"></div>
                    </div>
                    <span class="progress-description" id="averageDailyRateChange">avg daily rate</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Booking Trends Chart -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-2"></i>
                        Booking Trends
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="bookingTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Revenue Chart -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Revenue Analysis
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics Row -->
    <div class="row mb-4">
        <!-- Booking Status Distribution -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Booking Status Distribution
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="statusDistributionChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top Hotels Performance -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-2"></i>
                        Top Performing Hotels
                    </h3>
                </div>
                <div class="card-body">
                    <div id="topHotelsChart">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p class="mt-2 text-muted">Loading data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Occupancy Rates -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Monthly Occupancy
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="occupancyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Tables -->
    <div class="row">
        <!-- Hotel Performance Table -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>
                        Hotel Performance Breakdown
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportTable('hotels')">
                            <i class="fas fa-download mr-1"></i>
                            Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="hotelPerformanceTable">
                            <thead>
                                <tr>
                                    <th>Hotel Name</th>
                                    <th>Total Bookings</th>
                                    <th>Confirmed</th>
                                    <th>Revenue</th>
                                    <th>Occupancy %</th>
                                    <th>ADR</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody id="hotelPerformanceBody">
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p class="mt-2 text-muted">Loading performance data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Bookings Summary -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="timeline" id="recentActivityTimeline">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p class="mt-2 text-muted">Loading recent activity...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Insights -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Key Insights & Recommendations
                    </h3>
                </div>
                <div class="card-body">
                    <div id="keyInsights">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p class="mt-2 text-muted">Analyzing data for insights...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .info-box-number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .progress {
        height: 4px;
        margin: 5px 0;
    }
    
    .progress-description {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.8);
    }
    
    .timeline {
        position: relative;
        margin: 0;
        padding: 0;
    }
    
    .timeline-item {
        position: relative;
        background-color: transparent;
        color: #495057;
        margin: 0;
        padding-left: 31px;
        padding-bottom: 15px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 12px;
        border-left: 2px solid #dee2e6;
        height: calc(100% + 20px);
        top: 0;
    }
    
    .timeline-item:last-child::before {
        height: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 3px;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        border: 2px solid #fff;
        text-align: center;
        line-height: 21px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .timeline-content {
        margin-left: 5px;
    }
    
    .timeline-content h6 {
        margin: 0 0 5px 0;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .timeline-content p {
        margin: 0;
        font-size: 0.8rem;
        color: #666;
    }
    
    .trend-up {
        color: #28a745;
    }
    
    .trend-down {
        color: #dc3545;
    }
    
    .trend-neutral {
        color: #6c757d;
    }
    
    .insight-card {
        border-left: 4px solid #007bff;
        background: #f8f9ff;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 0 5px 5px 0;
    }
    
    .insight-card.warning {
        border-left-color: #ffc107;
        background: #fffdf0;
    }
    
    .insight-card.success {
        border-left-color: #28a745;
        background: #f0fff4;
    }
    
    .insight-card.danger {
        border-left-color: #dc3545;
        background: #fff5f5;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let bookingTrendsChart, revenueChart, statusDistributionChart, occupancyChart;

$(document).ready(function() {
    // Initialize date range picker
    $('#dateRangePicker').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
    });

    $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
    });

    // Show/hide custom date range
    $('#timePeriod').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });
    
    // Auto-refresh dashboard
    $('#refreshDashboard').on('click', function() {
        updateDashboard();
    });
    
    // Initialize dashboard
    updateDashboard();
});

// Update dashboard with current filters
function updateDashboard() {
    const filters = {
        period: $('#timePeriod').val(),
        dateRange: $('#dateRangePicker').val(),
        hotel: $('#hotelFilter').val()
    };
    
    // Show loading states
    showLoadingStates();
    
    // Fetch dashboard data
    $.get('{{ route("b2b.hotel-provider.bookings.dashboard.data") }}', filters)
        .done(function(response) {
            if (response.success) {
                updateKPIs(response.data.kpis);
                updateCharts(response.data.charts);
                updateTables(response.data.tables);
                updateInsights(response.data.insights);
            } else {
                showError('Failed to load dashboard data: ' + response.message);
            }
        })
        .fail(function() {
            showError('Network error. Please try again.');
        });
}

// Show loading states
function showLoadingStates() {
    // KPI loading states
    $('.info-box-number').text('-');
    $('.progress-description').text('loading...');
    $('.progress-bar').css('width', '0%');
}

// Update KPIs
function updateKPIs(kpis) {
    $('#totalBookings').text(formatNumber(kpis.total_bookings));
    $('#confirmedBookings').text(formatNumber(kpis.confirmed_bookings));
    $('#pendingBookings').text(formatNumber(kpis.pending_bookings));
    $('#totalRevenue').text('$' + formatMoney(kpis.total_revenue));
    $('#occupancyRate').text(kpis.occupancy_rate + '%');
    $('#averageDailyRate').text('$' + formatMoney(kpis.average_daily_rate));
    
    // Update progress bars and changes
    updateKPIProgress('totalBookings', kpis.total_bookings_change);
    updateKPIProgress('confirmedBookings', kpis.confirmed_bookings_change);
    updateKPIProgress('pendingBookings', kpis.pending_bookings_change, true);
    updateKPIProgress('totalRevenue', kpis.total_revenue_change);
    updateKPIProgress('occupancyRate', kpis.occupancy_rate_change);
    updateKPIProgress('averageDailyRate', kpis.average_daily_rate_change);
}

// Update KPI progress and change indicators
function updateKPIProgress(kpiId, change, inverse = false) {
    const progressBar = $(`#${kpiId}Progress`);
    const changeText = $(`#${kpiId}Change`);
    
    if (change !== undefined && change !== null) {
        const isPositive = inverse ? change < 0 : change > 0;
        const absChange = Math.abs(change);
        const progressWidth = Math.min(absChange * 10, 100); // Scale for visual
        
        progressBar.css('width', progressWidth + '%');
        
        const icon = isPositive ? '↑' : '↓';
        const colorClass = isPositive ? 'text-success' : 'text-danger';
        changeText.html(`<span class="${colorClass}">${icon} ${absChange.toFixed(1)}%</span> vs previous`);
    }
}

// Update charts
function updateCharts(chartData) {
    updateBookingTrendsChart(chartData.booking_trends);
    updateRevenueChart(chartData.revenue_trends);
    updateStatusDistributionChart(chartData.status_distribution);
    updateOccupancyChart(chartData.occupancy_trends);
    updateTopHotels(chartData.top_hotels);
}

// Update booking trends chart
function updateBookingTrendsChart(data) {
    const ctx = document.getElementById('bookingTrendsChart').getContext('2d');
    
    if (bookingTrendsChart) {
        bookingTrendsChart.destroy();
    }
    
    bookingTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Total Bookings',
                data: data.total,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true
            }, {
                label: 'Confirmed',
                data: data.confirmed,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Update revenue chart
function updateRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    if (revenueChart) {
        revenueChart.destroy();
    }
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Revenue',
                data: data.revenue,
                backgroundColor: 'rgba(0, 123, 255, 0.8)',
                borderColor: '#007bff',
                borderWidth: 1
            }, {
                label: 'Target',
                data: data.target,
                type: 'line',
                borderColor: '#dc3545',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + formatMoney(value);
                        }
                    }
                }
            }
        }
    });
}

// Update status distribution chart
function updateStatusDistributionChart(data) {
    const ctx = document.getElementById('statusDistributionChart').getContext('2d');
    
    if (statusDistributionChart) {
        statusDistributionChart.destroy();
    }
    
    statusDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#28a745',  // confirmed
                    '#ffc107',  // pending  
                    '#dc3545',  // cancelled
                    '#6c757d',  // no_show
                    '#17a2b8'   // others
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

// Update occupancy chart
function updateOccupancyChart(data) {
    const ctx = document.getElementById('occupancyChart').getContext('2d');
    
    if (occupancyChart) {
        occupancyChart.destroy();
    }
    
    occupancyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Occupancy Rate',
                data: data.occupancy,
                backgroundColor: 'rgba(108, 117, 125, 0.8)',
                borderColor: '#6c757d',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

// Update top hotels
function updateTopHotels(data) {
    let html = '';
    
    if (data && data.length > 0) {
        data.forEach(function(hotel, index) {
            const rank = index + 1;
            const medalClass = rank <= 3 ? 'fas fa-medal text-warning' : 'fas fa-hotel text-muted';
            
            html += `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="${medalClass} mr-2"></i>
                        <div>
                            <strong>${hotel.name}</strong>
                            <br>
                            <small class="text-muted">${hotel.bookings} bookings</small>
                        </div>
                    </div>
                    <div class="text-right">
                        <strong>$${formatMoney(hotel.revenue)}</strong>
                        <br>
                        <small class="text-muted">${hotel.occupancy}% occupancy</small>
                    </div>
                </div>
            `;
        });
    } else {
        html = '<p class="text-muted text-center py-4">No data available</p>';
    }
    
    $('#topHotelsChart').html(html);
}

// Update tables
function updateTables(tableData) {
    updateHotelPerformanceTable(tableData.hotel_performance);
    updateRecentActivity(tableData.recent_activity);
}

// Update hotel performance table
function updateHotelPerformanceTable(data) {
    let html = '';
    
    if (data && data.length > 0) {
        data.forEach(function(hotel) {
            const trendIcon = getTrendIcon(hotel.trend);
            const trendClass = getTrendClass(hotel.trend);
            
            html += `
                <tr>
                    <td><strong>${hotel.name}</strong></td>
                    <td>${formatNumber(hotel.total_bookings)}</td>
                    <td>${formatNumber(hotel.confirmed_bookings)}</td>
                    <td>$${formatMoney(hotel.revenue)}</td>
                    <td>${hotel.occupancy_rate}%</td>
                    <td>$${formatMoney(hotel.adr)}</td>
                    <td class="${trendClass}">${trendIcon} ${hotel.trend_percentage}%</td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    No data available for the selected period
                </td>
            </tr>
        `;
    }
    
    $('#hotelPerformanceBody').html(html);
}

// Update recent activity timeline
function updateRecentActivity(activities) {
    let html = '';
    
    if (activities && activities.length > 0) {
        activities.forEach(function(activity) {
            const markerClass = getActivityMarkerClass(activity.type);
            
            html += `
                <div class="timeline-item">
                    <div class="timeline-marker ${markerClass}">
                        <i class="${getActivityIcon(activity.type)}"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>${activity.title}</h6>
                        <p>${activity.description}</p>
                        <small class="text-muted">${formatDateTime(activity.created_at)}</small>
                    </div>
                </div>
            `;
        });
    } else {
        html = '<p class="text-muted text-center py-4">No recent activity</p>';
    }
    
    $('#recentActivityTimeline').html(html);
}

// Update insights
function updateInsights(insights) {
    let html = '';
    
    if (insights && insights.length > 0) {
        insights.forEach(function(insight) {
            html += `
                <div class="insight-card ${insight.type}">
                    <h6>
                        <i class="${getInsightIcon(insight.type)} mr-2"></i>
                        ${insight.title}
                    </h6>
                    <p>${insight.description}</p>
                    ${insight.action ? `<small><strong>Recommended Action:</strong> ${insight.action}</small>` : ''}
                </div>
            `;
        });
    } else {
        html = `
            <div class="insight-card">
                <h6><i class="fas fa-info-circle mr-2"></i>No specific insights at this time</h6>
                <p>Continue monitoring your booking performance. Insights will appear as data patterns emerge.</p>
            </div>
        `;
    }
    
    $('#keyInsights').html(html);
}

// Show error message
function showError(message) {
    // You could show a toast or alert here
    console.error('Dashboard Error:', message);
    alert('Error: ' + message);
}

// Export functions
function exportReport(type) {
    const filters = {
        period: $('#timePeriod').val(),
        dateRange: $('#dateRangePicker').val(),
        hotel: $('#hotelFilter').val(),
        type: type
    };
    
    const params = new URLSearchParams(filters).toString();
    window.open(`{{ route('b2b.hotel-provider.bookings.dashboard.export') }}?${params}`, '_blank');
}

function exportTable(table) {
    const filters = {
        period: $('#timePeriod').val(),
        dateRange: $('#dateRangePicker').val(),
        hotel: $('#hotelFilter').val(),
        table: table
    };
    
    const params = new URLSearchParams(filters).toString();
    window.open(`{{ route('b2b.hotel-provider.bookings.dashboard.export') }}?${params}`, '_blank');
}

// Helper functions
function formatNumber(num) {
    return new Intl.NumberFormat().format(num || 0);
}

function formatMoney(amount) {
    return parseFloat(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDateTime(dateTime) {
    return moment(dateTime).format('MMM DD, YYYY HH:mm');
}

function getTrendIcon(trend) {
    if (trend > 0) return '↗';
    if (trend < 0) return '↘';
    return '→';
}

function getTrendClass(trend) {
    if (trend > 0) return 'trend-up';
    if (trend < 0) return 'trend-down';
    return 'trend-neutral';
}

function getActivityMarkerClass(type) {
    const classes = {
        'booking': 'bg-primary',
        'cancellation': 'bg-danger',
        'confirmation': 'bg-success',
        'payment': 'bg-info',
        'checkin': 'bg-warning',
        'checkout': 'bg-secondary'
    };
    return classes[type] || 'bg-secondary';
}

function getActivityIcon(type) {
    const icons = {
        'booking': 'fas fa-calendar-plus',
        'cancellation': 'fas fa-times',
        'confirmation': 'fas fa-check',
        'payment': 'fas fa-credit-card',
        'checkin': 'fas fa-sign-in-alt',
        'checkout': 'fas fa-sign-out-alt'
    };
    return icons[type] || 'fas fa-info';
}

function getInsightIcon(type) {
    const icons = {
        'success': 'fas fa-check-circle',
        'warning': 'fas fa-exclamation-triangle',
        'danger': 'fas fa-exclamation-circle',
        'info': 'fas fa-info-circle'
    };
    return icons[type] || 'fas fa-lightbulb';
}
</script>
@stop
