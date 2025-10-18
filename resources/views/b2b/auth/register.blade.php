@extends('layouts.b2b-auth')

@section('title', 'Partner Registration')
@section('form-class', 'floating-form-wide')
@section('auth-title', 'Join Our Partner Network')
@section('auth-subtitle', 'Register your business and unlock exclusive B2B opportunities')
@section('auth-image', route('get.media',['sign-up-rt.jpg']))
@section('auth-icon', 'fas fa-user-plus')
@section('auth-message', 'Thousands of partners trust us worldwide')

@section('content')
<!-- Progress Indicator -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between">
            <div class="progress-step active" id="step1-indicator">
                <div class="step-number">1</div>
                <div class="text-sm text-muted">Account Type</div>
            </div>
            <div class="progress-step" id="step2-indicator">
                <div class="step-number">2</div>
                <div class="text-sm text-muted">Company Details</div>
            </div>
        </div>
    </div>
</div>

<!-- Registration Card -->
<div class="card shadow border-0 rounded">
    <div class="card-body p-4">
        <h4 class="card-title text-center mb-3">
            <i class="fas fa-handshake text-success mr-2"></i>
            Partner Registration
        </h4>
        <p class="text-center text-muted mb-4">Join our network of trusted travel partners</p>

        @if ($errors->any())
            <div class="alert alert-danger border-0">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('b2b.register') }}" method="POST" id="registrationForm">
            @csrf
            
            <!-- Step 1: Account Type Selection -->
            <div class="registration-step" id="step1">
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-user-tag text-primary mr-2"></i>
                        Select Your Business Type
                    </h5>
                    
                    <!-- Account Type Tabs -->
                    <ul class="nav nav-pills nav-justified mb-4" id="accountTypeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="travel-agency-tab" data-toggle="pill" 
                               href="#travel-agency" role="tab" data-type="travel_agency">
                                <i class="fas fa-plane-departure d-block mb-1"></i>
                                <small>Travel Agency</small>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="hotel-provider-tab" data-toggle="pill" 
                               href="#hotel-provider" role="tab" data-type="hotel_provider">
                                <i class="fas fa-hotel d-block mb-1"></i>
                                <small>Hotel Service</small>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="transport-provider-tab" data-toggle="pill" 
                               href="#transport-provider" role="tab" data-type="transport_provider">
                                <i class="fas fa-bus d-block mb-1"></i>
                                <small>Transportation</small>
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="accountTypeTabsContent">
                        <!-- Travel Agency Tab -->
                        <div class="tab-pane fade show active" id="travel-agency" role="tabpanel">
                            <div class="bg-light p-3 rounded mb-3">
                                <h6 class="text-primary mb-2">Travel Agency</h6>
                                <p class="mb-0 text-muted">Perfect for travel agencies managing customer bookings and packages.</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" placeholder="Your full name" required>
                                        @error('name')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" placeholder="your@email.com" required>
                                        @error('email')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               name="phone" value="{{ old('phone') }}" placeholder="+1234567890" required>
                                        @error('phone')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Agency License Number</label>
                                        <input type="text" class="form-control travel-agency-field" 
                                               name="agency_license" value="{{ old('agency_license') }}" 
                                               placeholder="Travel agency license number">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hotel Provider Tab -->
                        <div class="tab-pane fade" id="hotel-provider" role="tabpanel">
                            <div class="bg-light p-3 rounded mb-3">
                                <h6 class="text-primary mb-2">Hotel Service Provider</h6>
                                <p class="mb-0 text-muted">For hotels, resorts, and accommodation providers.</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name_hotel" 
                                               placeholder="Your full name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email_hotel" 
                                               placeholder="your@email.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="phone_hotel" 
                                               placeholder="+1234567890">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hotel Classification</label>
                                        <select class="form-control hotel-provider-field" name="hotel_classification">
                                            <option value="">Select classification</option>
                                            <option value="budget">Budget Hotel</option>
                                            <option value="mid-range">Mid-range Hotel</option>
                                            <option value="luxury">Luxury Hotel</option>
                                            <option value="resort">Resort</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Coverage Areas</label>
                                        <input type="text" class="form-control hotel-provider-field" 
                                               name="coverage_areas_hotel" 
                                               placeholder="e.g., Mecca, Medina, Jeddah">
                                        <small class="text-muted">Separate multiple areas with commas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transport Provider Tab -->
                        <div class="tab-pane fade" id="transport-provider" role="tabpanel">
                            <div class="bg-light p-3 rounded mb-3">
                                <h6 class="text-primary mb-2">Transportation Service Provider</h6>
                                <p class="mb-0 text-muted">For bus companies, car rentals, and transport services.</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name_transport" 
                                               placeholder="Your full name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email_transport" 
                                               placeholder="your@email.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="phone_transport" 
                                               placeholder="+1234567890">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Vehicle Type</label>
                                        <select class="form-control transport-provider-field" name="vehicle_type">
                                            <option value="">Select vehicle type</option>
                                            <option value="bus">Bus</option>
                                            <option value="van">Van/Minibus</option>
                                            <option value="car">Car</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fleet Size</label>
                                        <input type="number" class="form-control transport-provider-field" 
                                               name="fleet_size" placeholder="Number of vehicles">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Operating License</label>
                                        <input type="text" class="form-control transport-provider-field" 
                                               name="operating_license" 
                                               placeholder="Transportation license number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Fields -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" placeholder="Create a strong password" required>
                                @error('password')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" 
                                       placeholder="Confirm your password" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden field to store selected user type -->
                    <input type="hidden" name="user_type" id="selected_user_type" value="travel_agency">
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" id="nextToStep2">
                        Next: Company Details <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Company Details -->
            <div class="registration-step d-none" id="step2">
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-building text-success mr-2"></i>
                        Company Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       name="company_name" value="{{ old('company_name') }}" 
                                       placeholder="Your company name" required>
                                @error('company_name')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Registration Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_registration_number') is-invalid @enderror" 
                                       name="company_registration_number" value="{{ old('company_registration_number') }}" 
                                       placeholder="Company registration number" required>
                                @error('company_registration_number')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Business Address</label>
                                <input type="text" class="form-control" name="business_address" 
                                       placeholder="Company address">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Website URL</label>
                                <input type="url" class="form-control" name="website_url" 
                                       placeholder="https://your-website.com">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Company Description</label>
                                <textarea class="form-control" name="company_description" rows="3" 
                                          placeholder="Brief description of your company and services"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="backToStep1">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </button>
                    
                    <div>
                        <div class="icheck-primary d-inline-block mr-3">
                            <input type="checkbox" id="agreeTerms" name="terms" required>
                            <label for="agreeTerms" class="text-sm">
                                I agree to the <a href="#" class="text-primary">terms and conditions</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus mr-2"></i>
                            Complete Registration
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Additional Info -->
<div class="card border-0 bg-transparent mt-3">
    <div class="card-body text-center p-2">
        <p class="mb-1 text-muted">
            Already have an account? 
            <a href="{{ route('b2b.login') }}" class="text-primary font-weight-semibold text-decoration-none">
                Login here
            </a>
        </p>
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Your account will be reviewed by our admin team before activation.
        </small>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentStep = 1;
    
    // Step Navigation Functions
    function showStep(step) {
        $('.registration-step').addClass('d-none');
        $(`#step${step}`).removeClass('d-none');
        
        // Update progress indicators
        $('.progress-step').removeClass('active completed');
        for(let i = 1; i < step; i++) {
            $(`#step${i}-indicator`).addClass('completed');
        }
        $(`#step${step}-indicator`).addClass('active');
        
        currentStep = step;
    }
    
    function validateCurrentStep() {
        const currentStepElement = $(`#step${currentStep}`);
        let isValid = true;
        
        // Clear previous validation
        currentStepElement.find('.is-invalid').removeClass('is-invalid');
        currentStepElement.find('.invalid-feedback').hide();
        
        // Validate required fields in current step
        currentStepElement.find('input[required], select[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Password confirmation check (step 1)
        if (currentStep === 1) {
            const password = $('input[name="password"]').val();
            const confirmPassword = $('input[name="password_confirmation"]').val();
            
            if (password && confirmPassword && password !== confirmPassword) {
                $('input[name="password_confirmation"]').addClass('is-invalid')
                    .after('<div class="invalid-feedback d-block">Passwords do not match</div>');
                isValid = false;
            }
            
            // Email validation
            const email = getActiveFieldValue('email');
            if (email && !isValidEmail(email)) {
                getActiveField('email').addClass('is-invalid')
                    .after('<div class="invalid-feedback d-block">Please enter a valid email address</div>');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Account Type Tab Switching
    $('#accountTypeTabs a[data-toggle="pill"]').on('click', function(e) {
        e.preventDefault();
        
        const selectedType = $(this).data('type');
        $('#selected_user_type').val(selectedType);
        
        // Activate the tab
        $(this).tab('show');
        
        // Copy data between tabs if switching
        syncFieldData();
    });
    
    // Helper functions to get active tab fields
    function getActiveField(fieldType) {
        const activeTab = $('.tab-pane.active');
        if (fieldType === 'email') {
            return activeTab.find('input[name^="email"]').length ? activeTab.find('input[name^="email"]') : activeTab.find('input[name="email"]');
        }
        if (fieldType === 'name') {
            return activeTab.find('input[name^="name"]').length ? activeTab.find('input[name^="name"]') : activeTab.find('input[name="name"]');
        }
        if (fieldType === 'phone') {
            return activeTab.find('input[name^="phone"]').length ? activeTab.find('input[name^="phone"]') : activeTab.find('input[name="phone"]');
        }
        return activeTab.find(`input[name="${fieldType}"]`);
    }
    
    function getActiveFieldValue(fieldType) {
        return getActiveField(fieldType).val();
    }
    
    function syncFieldData() {
        // This would sync common fields between tabs if needed
        // For now, we'll keep them separate as per requirements
    }
    
    // Step Navigation Event Handlers
    $('#nextToStep2').on('click', function() {
        if (validateCurrentStep()) {
            // Copy active tab data to main form fields for submission
            copyActiveTabData();
            showStep(2);
        }
    });
    
    $('#backToStep1').on('click', function() {
        showStep(1);
    });
    
    function copyActiveTabData() {
        const activeTab = $('.tab-pane.active');
        const userType = $('#selected_user_type').val();
        
        // Copy name, email, phone to the main form fields
        if (userType === 'travel_agency') {
            // These are already in the correct name attributes
        } else if (userType === 'hotel_provider') {
            $('input[name="name"]').remove();
            $('input[name="email"]').remove();
            $('input[name="phone"]').remove();
            
            // Create hidden inputs with the correct names
            $('#registrationForm').append(`<input type="hidden" name="name" value="${$('input[name="name_hotel"]').val()}">`);
            $('#registrationForm').append(`<input type="hidden" name="email" value="${$('input[name="email_hotel"]').val()}">`);
            $('#registrationForm').append(`<input type="hidden" name="phone" value="${$('input[name="phone_hotel"]').val()}">`);
        } else if (userType === 'transport_provider') {
            $('input[name="name"]').remove();
            $('input[name="email"]').remove();
            $('input[name="phone"]').remove();
            
            // Create hidden inputs with the correct names
            $('#registrationForm').append(`<input type="hidden" name="name" value="${$('input[name="name_transport"]').val()}">`);
            $('#registrationForm').append(`<input type="hidden" name="email" value="${$('input[name="email_transport"]').val()}">`);
            $('#registrationForm').append(`<input type="hidden" name="phone" value="${$('input[name="phone_transport"]').val()}">`);
        }
    }
    
    // Form submission with validation
    $('#registrationForm').on('submit', function(e) {
        if (!validateCurrentStep()) {
            e.preventDefault();
            return false;
        }
        
        // Terms checkbox validation
        if (!$('#agreeTerms').is(':checked')) {
            e.preventDefault();
            alert('Please agree to the terms and conditions to continue.');
            return false;
        }
        
        // Final data copy
        copyActiveTabData();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating Account...');
    });
    
    // Real-time validation
    $(document).on('input', 'input[required]', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').hide();
        }
    });
    
    // Password strength indicator
    $('input[name="password"]').on('input', function() {
        const password = $(this).val();
        const strength = getPasswordStrength(password);
        
        // Remove existing strength indicator
        $(this).siblings('.password-strength').remove();
        
        if (password.length > 0) {
            const strengthClass = strength.level === 'strong' ? 'text-success' : 
                                strength.level === 'medium' ? 'text-warning' : 'text-danger';
            $(this).after(`<small class="password-strength ${strengthClass}">${strength.text}</small>`);
        }
    });
    
    function getPasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 3) return { level: 'weak', text: 'Weak password' };
        if (score < 5) return { level: 'medium', text: 'Medium strength' };
        return { level: 'strong', text: 'Strong password' };
    }
    
    // Auto-focus on first input
    $('input:visible:first').focus();
    
    // Enhanced form animations
    $('.form-control').on('focus', function() {
        $(this).closest('.form-group').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.form-group').removeClass('focused');
    });
    
    // Initialize tooltips if needed
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection
