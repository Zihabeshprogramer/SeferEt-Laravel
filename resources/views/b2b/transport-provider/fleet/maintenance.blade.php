@extends('layouts.b2b')

@section('title', 'Maintenance Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-tools text-info mr-2"></i>
                Maintenance Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-warning" disabled>
                <i class="fas fa-wrench mr-1"></i>
                Schedule Maintenance
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
                        <i class="fas fa-cogs mr-2"></i>
                        Vehicle Maintenance
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Maintenance Management System</h4>
                        <p class="text-muted">Vehicle maintenance management features are currently under development.</p>
                        <p class="text-muted">This will include:</p>
                        <ul class="list-unstyled text-muted">
                            <li><i class="fas fa-check text-success mr-2"></i>Preventive maintenance scheduling</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Maintenance history tracking</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Service reminders</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Cost tracking and budgeting</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Vendor management</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Maintenance reports and analytics</li>
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
