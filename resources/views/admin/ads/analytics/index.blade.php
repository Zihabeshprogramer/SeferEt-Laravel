@extends('adminlte::page')

@section('title', 'Ad Analytics Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>Ad Analytics Dashboard</h1>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('admin.ads.analytics.export') }}" method="GET" style="display: inline;">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
                <input type="hidden" name="ad_id" value="{{ $filters['ad_id'] }}">
                <input type="hidden" name="owner_id" value="{{ $filters['owner_id'] }}">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    {{-- Filters --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.ads.analytics.index') }}" method="GET" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ad</label>
                            <select name="ad_id" class="form-control select2">
                                <option value="">All Ads</option>
                                @foreach($ads as $ad)
                                    <option value="{{ $ad->id }}" {{ $filters['ad_id'] == $ad->id ? 'selected' : '' }}>
                                        {{ $ad->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Owner</label>
                            <select name="owner_id" class="form-control select2">
                                <option value="">All Owners</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}" {{ $filters['owner_id'] == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.ads.analytics.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($data['summary']['total_impressions']) }}</h3>
                    <p>Total Impressions</p>
                    @if(isset($data['trends']['changes']['impressions']))
                        <small class="text-{{ $data['trends']['changes']['impressions'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-arrow-{{ $data['trends']['changes']['impressions'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($data['trends']['changes']['impressions']) }}% vs previous period
                        </small>
                    @endif
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($data['summary']['total_clicks']) }}</h3>
                    <p>Total Clicks</p>
                    @if(isset($data['trends']['changes']['clicks']))
                        <small class="text-{{ $data['trends']['changes']['clicks'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-arrow-{{ $data['trends']['changes']['clicks'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($data['trends']['changes']['clicks']) }}% vs previous period
                        </small>
                    @endif
                </div>
                <div class="icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $data['summary']['average_ctr'] }}%</h3>
                    <p>Average CTR</p>
                    @if(isset($data['trends']['changes']['ctr']))
                        <small class="text-{{ $data['trends']['changes']['ctr'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-arrow-{{ $data['trends']['changes']['ctr'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($data['trends']['changes']['ctr']) }}% vs previous period
                        </small>
                    @endif
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($data['summary']['total_conversions']) }}</h3>
                    <p>Total Conversions</p>
                    @if(isset($data['trends']['changes']['conversions']))
                        <small class="text-{{ $data['trends']['changes']['conversions'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-arrow-{{ $data['trends']['changes']['conversions'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($data['trends']['changes']['conversions']) }}% vs previous period
                        </small>
                    @endif
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Impressions & Clicks Over Time</h3>
                </div>
                <div class="card-body">
                    <canvas id="timelineChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Device Breakdown</h3>
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Performing Ads</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ad</th>
                                <th>Impressions</th>
                                <th>Clicks</th>
                                <th>CTR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_ads'] as $topAd)
                                <tr>
                                    <td>
                                        @if(isset($topAd['ad']))
                                            <a href="{{ route('admin.ads.analytics.show', $topAd['ad']['id']) }}">
                                                {{ $topAd['ad']['title'] ?? 'N/A' }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ number_format($topAd['total_impressions'] ?? 0) }}</td>
                                    <td>{{ number_format($topAd['total_clicks'] ?? 0) }}</td>
                                    <td>{{ number_format($topAd['avg_ctr'] ?? 0, 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Placement Performance</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Placement</th>
                                <th>Impressions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['placement_breakdown'] as $placement => $count)
                                <tr>
                                    <td>{{ ucfirst($placement) }}</td>
                                    <td>{{ number_format($count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Timeline Chart
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    const timelineChart = new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['daily_data']->pluck('date')) !!},
            datasets: [
                {
                    label: 'Impressions',
                    data: {!! json_encode($data['daily_data']->pluck('impressions')) !!},
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Clicks',
                    data: {!! json_encode($data['daily_data']->pluck('clicks')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Device Chart
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    const deviceData = @json($data['device_breakdown']);
    const deviceChart = new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(deviceData).map(d => d.charAt(0).toUpperCase() + d.slice(1)),
            datasets: [{
                data: Object.values(deviceData),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });
</script>
@stop

@section('css')
<style>
    .small-box small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
    }
</style>
@stop
