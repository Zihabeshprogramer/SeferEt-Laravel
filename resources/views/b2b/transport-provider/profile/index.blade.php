@extends('layouts.b2b')

@section('title', 'My Profile')

@section('content_header')
    <div class="row">
        <div class="col-md-12">
            <h1 class="m-0">
                <i class="fas fa-user-circle text-info mr-2"></i>
                My Profile
            </h1>
            <p class="text-muted">Manage your account information and business details</p>
        </div>
    </div>
@stop

@section('content')
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Error Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Profile Statistics -->
        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <div class="profile-user-img-wrapper mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <h3 class="profile-username text-center">{{ $provider->name }}</h3>
                        <p class="text-muted text-center">
                            <i class="fas fa-truck mr-1"></i> Transport Provider
                        </p>
                    </div>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Services</b> <a class="float-right">{{ $stats['total_services'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Active Services</b> <a class="float-right text-success">{{ $stats['active_services'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Vehicles</b> <a class="float-right">{{ $stats['total_vehicles'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Drivers</b> <a class="float-right">{{ $stats['total_drivers'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Total Bookings</b> <a class="float-right">{{ $stats['total_bookings'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Completed Trips</b> <a class="float-right text-success">{{ $stats['completed_bookings'] }}</a>
                        </li>
                    </ul>

                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar mr-1"></i>
                            Member since {{ $provider->created_at->format('M Y') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link mr-2"></i>Quick Links</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="{{ route('b2b.transport-provider.services.index') }}" class="nav-link">
                                <i class="fas fa-bus text-primary mr-2"></i> My Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('b2b.transport-provider.fleet.vehicles') }}" class="nav-link">
                                <i class="fas fa-car text-success mr-2"></i> Fleet Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('b2b.transport-provider.operations.bookings') }}" class="nav-link">
                                <i class="fas fa-calendar-check text-info mr-2"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('b2b.transport-provider.reports.index') }}" class="nav-link">
                                <i class="fas fa-chart-line text-warning mr-2"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profile Edit Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Profile Information</h3>
                </div>
                <form action="{{ route('b2b.transport-provider.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-12">
                                <h5 class="text-muted mb-3">Personal Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $provider->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $provider->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Personal Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $provider->phone) }}">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_phone">Business Contact Phone</label>
                                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                           id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $provider->contact_phone) }}">
                                    @error('contact_phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div class="col-12 mt-3">
                                <h5 class="text-muted mb-3">Business Information</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                           id="company_name" name="company_name" value="{{ old('company_name', $provider->company_name) }}">
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_registration_number">Registration Number</label>
                                    <input type="text" class="form-control @error('company_registration_number') is-invalid @enderror" 
                                           id="company_registration_number" name="company_registration_number" value="{{ old('company_registration_number', $provider->company_registration_number) }}">
                                    @error('company_registration_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_number">Tax Number</label>
                                    <input type="text" class="form-control @error('tax_number') is-invalid @enderror" 
                                           id="tax_number" name="tax_number" value="{{ old('tax_number', $provider->tax_number) }}">
                                    @error('tax_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website">Website</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website', $provider->website) }}" 
                                           placeholder="https://example.com">
                                    @error('website')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Street Address</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                           id="address" name="address" value="{{ old('address', $provider->address) }}">
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $provider->city) }}">
                                    @error('city')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">State/Province</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $provider->state) }}">
                                    @error('state')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $provider->postal_code) }}">
                                    @error('postal_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                           id="country" name="country" value="{{ old('country', $provider->country) }}">
                                    @error('country')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="company_description">Business Description</label>
                                    <textarea class="form-control @error('company_description') is-invalid @enderror" 
                                              id="company_description" name="company_description" rows="4" 
                                              placeholder="Tell customers about your transport services...">{{ old('company_description', $provider->company_description) }}</textarea>
                                    @error('company_description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Change Password -->
                            <div class="col-12 mt-3">
                                <h5 class="text-muted mb-3">Change Password</h5>
                                <p class="text-muted small">Leave blank if you don't want to change your password</p>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" placeholder="Enter new password">
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                        <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.profile-user-img-wrapper {
    position: relative;
    display: inline-block;
}
.nav-pills .nav-link {
    color: #6c757d;
}
.nav-pills .nav-link:hover {
    background-color: #f8f9fa;
    color: #007bff;
}
</style>
@stop
