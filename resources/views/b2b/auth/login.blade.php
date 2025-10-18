@extends('layouts.b2b-auth')

@section('title', 'Partner Login')
@section('auth-title', 'Welcome Back, Partner!')
@section('auth-subtitle', 'Sign in to access your B2B dashboard and manage your travel business')
@section('auth-image', route('get.media', ['login-rt.jpg']))
@section('auth-icon', 'fas fa-sign-in-alt')
@section('auth-message', 'Secure access to your partner portal')

@section('content')
<!-- Login Card -->
<div class="card shadow border-0 rounded">
    <div class="card-body p-4">
        <h4 class="card-title text-center mb-3">
            <i class="fas fa-handshake text-primary mr-2"></i>
            Partner Login
        </h4>
        <p class="text-center text-muted mb-4">Sign in to access your B2B dashboard</p>

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

        <form action="{{ route('b2b.login') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="email" class="text-muted mb-2">
                    <i class="fas fa-envelope mr-1"></i> Email Address
                </label>
                <input type="email" 
                       class="form-control form-control-lg border-0 bg-light @error('email') is-invalid @enderror" 
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
                <label for="password" class="text-muted mb-2">
                    <i class="fas fa-lock mr-1"></i> Password
                </label>
                <input type="password" 
                       class="form-control form-control-lg border-0 bg-light @error('password') is-invalid @enderror" 
                       id="password"
                       name="password" 
                       placeholder="Enter your password"
                       required>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group d-flex justify-content-between align-items-center">
                <div class="icheck-primary">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="text-muted">
                        Remember Me
                    </label>
                </div>
                <a href="#" class="text-primary text-decoration-none">
                    <i class="fas fa-question-circle mr-1"></i>
                    Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm mb-3">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>

        <div class="text-center">
            <p class="mb-2 text-muted">
                Don't have an account? 
                <a href="{{ route('b2b.register') }}" class="text-primary font-weight-semibold text-decoration-none">
                    Register here
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Additional Info -->
<div class="card border-0 bg-transparent mt-3">
    <div class="card-body text-center p-2">
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            B2B partner access only. For customer login, visit the main website.
        </small>
    </div>
</div>

<!-- Status Info -->
<div class="card border-0 bg-light mt-2">
    <div class="card-body p-3">
        <h6 class="text-primary mb-2">
            <i class="fas fa-info-circle mr-2"></i>Account Status Information
        </h6>
        <ul class="mb-0 pl-3 text-sm text-muted">
            <li><strong>Pending:</strong> Under review by admin team</li>
            <li><strong>Active:</strong> Full access to partner features</li>
            <li><strong>Suspended:</strong> Temporarily restricted access</li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on email field
    $('input[name="email"]').focus();
    
    // Form validation and loading state
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...');
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
