@extends('layouts.b2b')

@section('title', 'Profile Settings')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-user-cog text-info mr-2"></i>
                Profile Settings
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Transport Provider Profile
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-user-cog fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Profile Management</h4>
                        <p class="text-muted">Transport provider profile management features are currently under development.</p>
                        <p class="text-muted">This will include:</p>
                        <ul class="list-unstyled text-muted">
                            <li><i class="fas fa-check text-success mr-2"></i>Business profile customization</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Contact information management</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Service area configuration</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Certification and license uploads</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Business documentation</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Payment and billing settings</li>
                        </ul>
                        <div class="mt-4">
                            <a href="{{ route('b2b.profile') }}" class="btn btn-primary mr-2">
                                <i class="fas fa-user mr-2"></i>
                                General Profile
                            </a>
                            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Note: General profile settings are available in the common profile section.</small>
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
