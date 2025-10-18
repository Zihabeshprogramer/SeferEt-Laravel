@extends('layouts.b2b')

@section('title', 'Route Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-route text-info mr-2"></i>
                Route Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" disabled>
                <i class="fas fa-plus mr-1"></i>
                Add Route
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
                        <i class="fas fa-map mr-2"></i>
                        Route Planning & Management
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-route fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Advanced Route Management</h4>
                        <p class="text-muted">Advanced route planning features are currently under development.</p>
                        <p class="text-muted">This will include:</p>
                        <ul class="list-unstyled text-muted">
                            <li><i class="fas fa-check text-success mr-2"></i>Interactive route mapping</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Distance and time calculations</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Traffic optimization</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Multi-stop route planning</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Route efficiency analytics</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Real-time GPS tracking</li>
                        </ul>
                        <div class="mt-4">
                            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Dashboard
                            </a>
                            <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>
                                Create Transport Service
                            </a>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Note: Basic route management is available in Transport Service creation.</small>
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
