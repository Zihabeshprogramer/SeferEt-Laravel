@extends('layouts.b2b')

@section('title', 'Vehicle Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-truck text-info mr-2"></i>
                Vehicle Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" disabled>
                <i class="fas fa-plus mr-1"></i>
                Add Vehicle
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
                        <i class="fas fa-list mr-2"></i>
                        Vehicle Fleet
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Vehicle Management</h4>
                        <p class="text-muted">Advanced vehicle management features are currently under development.</p>
                        <p class="text-muted">This will include:</p>
                        <ul class="list-unstyled text-muted">
                            <li><i class="fas fa-check text-success mr-2"></i>Individual vehicle tracking</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Maintenance scheduling</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Vehicle specifications</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Availability management</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Performance analytics</li>
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
            max-width: 300px;
            margin: 0 auto;
        }
    </style>
@stop
