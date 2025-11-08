@extends('layouts.customer-auth')

@section('title', 'Customer Login')
@section('subtitle', 'Sign in to book your Umrah journey')
@section('auth-image', route('get.media', ['login-rt.jpg']))

@section('content')
<div class="text-center mb-4">
    <h4 class="font-weight-bold" style="color: var(--text-primary);">
        <i class="fas fa-sign-in-alt" style="color: var(--primary-color);"></i>
        Welcome Back!
    </h4>
    <p class="text-muted mb-0">Sign in to continue your journey</p>
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

@if (session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('customer.login') }}" method="POST" id="loginForm">
    @csrf
    
    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope mr-1"></i> Email Address
        </label>
        <input type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               id="email"
               name="email" 
               value="{{ old('email') }}" 
               placeholder="Enter your email address"
               required 
               autofocus>
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
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password"
                   name="password" 
                   placeholder="Enter your password"
                   required>
            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="icheck-primary">
            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">
                Remember Me
            </label>
        </div>
        <a href="#" class="text-link" style="font-size: 0.9rem;">
            <i class="fas fa-question-circle mr-1"></i>
            Forgot Password?
        </a>
    </div>

    <button type="submit" class="btn btn-primary btn-block mb-3">
        <i class="fas fa-sign-in-alt mr-2"></i>
        Sign In
    </button>
</form>

<div class="text-center mb-3">
    <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
        Don't have an account? 
        <a href="{{ route('customer.register') }}" class="text-link">
            Register here
        </a>
    </p>
</div>

<div class="divider">
    <span>or</span>
</div>

<div class="text-center">
    <p class="mb-2" style="color: var(--text-muted); font-size: 0.875rem;">Looking for business access?</p>
    <div class="btn-group btn-group-sm" role="group">
        <a href="{{ route('b2b.login') }}" class="btn btn-outline-info">
            <i class="fas fa-handshake mr-1"></i> B2B Portal
        </a>
        <a href="{{ route('admin.login') }}" class="btn btn-outline-primary">
            <i class="fas fa-shield-alt mr-1"></i> Admin
        </a>
    </div>
</div>

<div class="text-center mt-4">
    <small style="color: var(--text-muted);">
        <i class="fas fa-shield-check mr-1"></i>
        Secure customer portal for Umrah bookings
    </small>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on email field
    $('#email').focus();
    
    // Password visibility toggle
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#password');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        
        // Toggle eye icon
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
    
    // Form validation and loading state
    $('#loginForm').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...');
    });
    
    // Enhanced form field animations
    $('.form-control').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
});
</script>
@endsection
