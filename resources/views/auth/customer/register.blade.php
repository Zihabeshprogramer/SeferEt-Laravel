@extends('layouts.customer-auth')

@section('title', 'Customer Registration')
@section('subtitle', 'Create your account to start your journey')
@section('auth-image', route('get.media', ['sign-up-rt.jpg']))

@section('content')
<div class="text-center mb-4">
    <h4 class="font-weight-bold" style="color: var(--text-primary);">
        <i class="fas fa-user-plus" style="color: var(--primary-color);"></i>
        Create Your Account
    </h4>
    <p class="text-muted mb-0">Join thousands of pilgrims on their spiritual journey</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('customer.register') }}" id="registerForm">
    @csrf

    <div class="form-group">
        <label for="name">
            <i class="fas fa-user mr-1"></i> Full Name
        </label>
        <input id="name" 
               type="text" 
               class="form-control @error('name') is-invalid @enderror" 
               name="name" 
               value="{{ old('name') }}" 
               placeholder="Enter your full name"
               required 
               autocomplete="name" 
               autofocus>
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope mr-1"></i> Email Address
        </label>
        <input id="email" 
               type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ old('email') }}" 
               placeholder="Enter your email address"
               required 
               autocomplete="email">
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password">
            <i class="fas fa-lock mr-1"></i> Password
        </label>
        <div class="password-wrapper">
            <input id="password" 
                   type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" 
                   placeholder="Create a strong password"
                   required 
                   autocomplete="new-password">
            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <small class="text-muted" id="passwordStrength"></small>
    </div>

    <div class="form-group">
        <label for="password-confirm">
            <i class="fas fa-lock mr-1"></i> Confirm Password
        </label>
        <div class="password-wrapper">
            <input id="password-confirm" 
                   type="password" 
                   class="form-control" 
                   name="password_confirmation" 
                   placeholder="Confirm your password"
                   required 
                   autocomplete="new-password">
            <i class="fas fa-eye password-toggle" id="togglePasswordConfirm"></i>
        </div>
        <small class="text-muted" id="passwordMatch"></small>
    </div>

    <div class="form-group">
        <div class="icheck-primary">
            <input type="checkbox" id="agreeTerms" name="terms" required>
            <label for="agreeTerms" style="font-size: 0.9rem;">
                I agree to the <a href="#" class="text-link">Terms and Conditions</a>
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block mb-3">
        <i class="fas fa-user-plus mr-2"></i>
        Create Account
    </button>
</form>

<div class="text-center mb-3">
    <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
        Already have an account? 
        <a href="{{ route('customer.login') }}" class="text-link">
            Sign in here
        </a>
    </p>
</div>

<div class="divider">
    <span>or</span>
</div>

<div class="text-center">
    <p class="mb-2" style="color: var(--text-muted); font-size: 0.875rem;">Business registration?</p>
    <a href="{{ route('b2b.register') }}" class="btn btn-outline-info btn-sm">
        <i class="fas fa-handshake mr-1"></i> Register as Business Partner
    </a>
</div>

<div class="text-center mt-4">
    <small style="color: var(--text-muted);">
        <i class="fas fa-shield-check mr-1"></i>
        Your information is secure and will never be shared
    </small>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on name field
    $('#name').focus();
    
    // Password visibility toggle
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#password');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
    
    $('#togglePasswordConfirm').on('click', function() {
        const passwordInput = $('#password-confirm');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = getPasswordStrength(password);
        
        if (password.length > 0) {
            const colors = {
                'weak': '#EF4444',
                'medium': '#F59E0B',
                'strong': '#10B981'  // Green is OK for success indicators
            };
            $('#passwordStrength')
                .text(strength.text)
                .css('color', colors[strength.level]);
        } else {
            $('#passwordStrength').text('');
        }
        
        checkPasswordMatch();
    });
    
    // Password confirmation match
    $('#password-confirm').on('input', function() {
        checkPasswordMatch();
    });
    
    function checkPasswordMatch() {
        const password = $('#password').val();
        const confirmPassword = $('#password-confirm').val();
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                $('#passwordMatch')
                    .text('✓ Passwords match')
                    .css('color', '#10B981');
            } else {
                $('#passwordMatch')
                    .text('✗ Passwords do not match')
                    .css('color', '#EF4444');
            }
        } else {
            $('#passwordMatch').text('');
        }
    }
    
    function getPasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 3) return { level: 'weak', text: '⚠ Weak password' };
        if (score < 5) return { level: 'medium', text: '⚡ Medium strength' };
        return { level: 'strong', text: '✓ Strong password' };
    }
    
    // Form validation and loading state
    $('#registerForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#password-confirm').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            $('#passwordMatch')
                .text('✗ Passwords do not match')
                .css('color', '#EF4444');
            $('#password-confirm').focus();
            return false;
        }
        
        if (!$('#agreeTerms').is(':checked')) {
            e.preventDefault();
            alert('Please agree to the terms and conditions to continue.');
            return false;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating Account...');
    });
    
    // Enhanced form field animations
    $('.form-control').on('focus', function() {
        $(this).closest('.form-group').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.form-group').removeClass('focused');
    });
});
</script>
@endsection
