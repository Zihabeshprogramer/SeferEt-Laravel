@extends('layouts.admin')

@section('title', 'Business Overview')
@section('page-title', 'Business Overview')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.partners.management') }}">Partner Management</a></li>
    <li class="breadcrumb-item active">Business Overview</li>
@endsection

@section('content')
    <!-- Business Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Revenue</span>
                    <span class="info-box-number">${{ number_format($overview['revenue_trends']['monthly_revenue'] ?? 0, 2) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ ($overview['revenue_trends']['growth_rate'] ?? 0) > 0 ? min(($overview['revenue_trends']['growth_rate'] ?? 0) * 10, 100) : 0 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $overview['revenue_trends']['growth_rate'] ?? 0 }}% growth this month
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Bookings</span>
                    <span class="info-box-number">{{ number_format($overview['business_metrics']['total_bookings'] ?? 0) }}</span>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: 70%"></div>
                    </div>
                    <span class="progress-description">
                        Active bookings processing
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Conversion Rate</span>
                    <span class="info-box-number">{{ number_format($overview['business_metrics']['conversion_rate'] ?? 0, 1) }}%</span>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: {{ ($overview['business_metrics']['conversion_rate'] ?? 0) }}%"></div>
                    </div>
                    <span class="progress-description">
                        From inquiries to bookings
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg. Rating</span>
                    <span class="info-box-number">{{ number_format($overview['business_metrics']['customer_satisfaction'] ?? 4.2, 1) }}</span>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: {{ (($overview['business_metrics']['customer_satisfaction'] ?? 4.2) / 5) * 100 }}%"></div>
                    </div>
                    <span class="progress-description">
                        Customer satisfaction
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="row">
        <!-- Revenue Trends -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-2"></i>
                        Revenue Trends
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary active">30 Days</button>
                            <button type="button" class="btn btn-sm btn-outline-primary">90 Days</button>
                            <button type="button" class="btn btn-sm btn-outline-primary">1 Year</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Partner Performance -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-2"></i>
                        Top Performers
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="performanceChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Partner Analytics and Recent Activity -->
    <div class="row">
        <!-- Partner Type Breakdown -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Partner Distribution
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="partnerDistributionChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Business Activity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Recent Business Activity
                    </h3>
                </div>
                <div class="card-body">
                    @if(count($overview['pending_reviews']) > 0)
                        <div class="timeline timeline-inverse">
                            @foreach($overview['pending_reviews'] as $partner)
                            <div class="time-label">
                                <span class="bg-warning">{{ $partner->created_at->format('M d') }}</span>
                            </div>
                            <div>
                                <i class="fas fa-handshake bg-warning"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i> {{ $partner->created_at->format('H:i') }}
                                    </span>
                                    <h3 class="timeline-header">New Partner Registration</h3>
                                    <div class="timeline-body">
                                        <strong>{{ $partner->name }}</strong> from {{ $partner->company_name ?? 'N/A' }} 
                                        registered as {{ ucfirst(str_replace('_', ' ', $partner->role)) }}.
                                        <div class="mt-2">
                                            <a href="{{ route('admin.partners.show', $partner->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye mr-1"></i> Review
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <h5>No Pending Reviews</h5>
                            <p>All partners have been reviewed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Business Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.partners.management') }}?approval_status=pending" class="btn btn-warning btn-block">
                                <i class="fas fa-clock mr-2"></i>
                                Review Pending Partners
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.partners.management') }}?status=active" class="btn btn-success btn-block">
                                <i class="fas fa-check-circle mr-2"></i>
                                View Active Partners
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.partners.export') }}" class="btn btn-info btn-block">
                                <i class="fas fa-download mr-2"></i>
                                Export Business Report
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.partners.management') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-handshake mr-2"></i>
                                Manage All Partners
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Revenue ($)',
                data: [12000, 19000, 15000, 25000],
                backgroundColor: 'rgba(60, 141, 188, 0.1)',
                borderColor: 'rgba(60, 141, 188, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Performance Chart (Doughnut)
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hotel Providers', 'Travel Agents', 'Transport Providers'],
            datasets: [{
                data: [45, 35, 20],
                backgroundColor: [
                    '#f39c12',
                    '#3c8dbc', 
                    '#00a65a'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Partner Distribution Chart
    const distributionCtx = document.getElementById('partnerDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: ['Active Partners', 'Pending Approval', 'Suspended'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.info-box {
    margin-bottom: 20px;
}

.timeline-item {
    margin-right: 0;
}

.btn-group .btn {
    font-size: 0.8rem;
}

.card-title i {
    color: #007bff;
}
</style>
@endpush
