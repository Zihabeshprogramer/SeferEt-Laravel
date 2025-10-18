@extends('layouts.auth')

@section('title', 'Customer Login')

@section('content')
    <p class="login-box-msg">
        <i class="fas fa-user text-success mr-2"></i>
        Sign in to Your Account
    </p>

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

    <form action="{{ route('customer.login') }}" method="POST">
        @csrf
        
        <div class="input-group mb-3">
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   placeholder="Email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Password" 
                   name="password" 
                   required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        Remember Me
                    </label>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </div>
        </div>
    </form>

    <div class="text-center mt-3">
        <p class="mb-1">
            <a href="#" class="text-success">I forgot my password</a>
        </p>
        <p class="mb-1">
            Don't have an account? <a href="{{ route('customer.register') }}" class="text-success">Register here</a>
        </p>
        <p class="mb-0">
            <small class="text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Customer portal for booking Umrah packages.
            </small>
        </p>
        <div class="mt-3">
            <p class="mb-0"><small class="text-muted">Looking for business access?</small></p>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('b2b.login') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-handshake mr-1"></i>B2B Login
                </a>
                <a href="{{ route('admin.login') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-shield-alt mr-1"></i>Admin Login
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on email field
    $('input[name="email"]').focus();
    
    // Form validation
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...');
    });
});
</script>
@endsection
