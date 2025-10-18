@extends('layouts.admin')

@section('title', 'System Settings')

@section('page-title', 'System Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
    <div class="row">
        <!-- General Settings -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog mr-2"></i>
                        General Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" class="form-control" id="site_name" value="SeferEt">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Site Description</label>
                            <textarea class="form-control" id="site_description" rows="3">Your trusted partner for Umrah journeys</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" value="info@seferet.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="text" class="form-control" id="contact_phone" value="+1 (555) 123-4567">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- System Configuration -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server mr-2"></i>
                        System Configuration
                    </h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="maintenance_mode">
                                <label class="custom-control-label" for="maintenance_mode">Maintenance Mode</label>
                            </div>
                            <small class="form-text text-muted">Enable to put the site under maintenance</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="partner_registration" checked>
                                <label class="custom-control-label" for="partner_registration">Partner Registration</label>
                            </div>
                            <small class="form-text text-muted">Allow new partner registrations</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="auto_approve_partners">
                                <label class="custom-control-label" for="auto_approve_partners">Auto-approve Partners</label>
                            </div>
                            <small class="form-text text-muted">Automatically approve new partner registrations</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="max_upload_size">Maximum Upload Size (MB)</label>
                            <input type="number" class="form-control" id="max_upload_size" value="10">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Update Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Email Settings -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-envelope mr-2"></i>
                        Email Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" class="form-control" id="smtp_host" value="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port</label>
                            <input type="number" class="form-control" id="smtp_port" value="587">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_username">SMTP Username</label>
                            <input type="email" class="form-control" id="smtp_username" value="noreply@seferet.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" class="form-control" id="smtp_password" placeholder="Enter SMTP password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Email Settings
                        </button>
                        <button type="button" class="btn btn-secondary ml-2">
                            <i class="fas fa-paper-plane mr-2"></i>Test Email
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Payment Settings -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Payment Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="currency">Default Currency</label>
                            <select class="form-control" id="currency">
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="SAR">SAR - Saudi Riyal</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="commission_rate">Commission Rate (%)</label>
                            <input type="number" class="form-control" id="commission_rate" value="5" step="0.1">
                            <small class="form-text text-muted">Platform commission rate from bookings</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stripe_enabled" checked>
                                <label class="custom-control-label" for="stripe_enabled">Enable Stripe</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="paypal_enabled">
                                <label class="custom-control-label" for="paypal_enabled">Enable PayPal</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Payment Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Settings form handling
    $('form').on('submit', function(e) {
        e.preventDefault();
        // Add settings save functionality here
        alert('Settings saved successfully!');
    });
    
    // Test email functionality
    $('[data-test-email]').on('click', function() {
        // Add test email functionality here
        alert('Test email sent!');
    });
});
</script>
@endsection
