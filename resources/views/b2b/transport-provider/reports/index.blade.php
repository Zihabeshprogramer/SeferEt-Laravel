@extends('layouts.b2b')

@section('title', 'Reports & Analytics')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-chart-bar text-info mr-2"></i>
                Reports & Analytics
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-success" disabled>
                <i class="fas fa-file-excel mr-1"></i>
                Export Reports
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-analytics mr-2"></i>
                        Business Intelligence
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Analytics & Reporting</h4>
                        <p class="text-muted">Advanced analytics and reporting features are currently under development.</p>
                        <p class="text-muted">This will include:</p>
                        <ul class="list-unstyled text-muted">
                            <li><i class="fas fa-check text-success mr-2"></i>Revenue and earnings reports</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Service performance analytics</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Customer satisfaction metrics</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Route efficiency analysis</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Seasonal trends and forecasting</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Custom report generation</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Data export capabilities</li>
                        </ul>
                        <div class="mt-4">
                            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .text-center ul {
            max-width: 350px;
            margin: 0 auto;
        }
    </style>
@stop
