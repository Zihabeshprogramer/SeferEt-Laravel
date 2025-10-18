@extends('layouts.auth')

@section('title', 'Admin Login')

@section('content')
    <p class="login-box-msg">
        <i class="fas fa-shield-alt text-primary mr-2"></i>
        Sign in to Admin Dashboard
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

    <form action="{{ route('admin.login') }}" method="POST">
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
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </div>
        </div>
    </form>

    <div class="text-center mt-3">
        <p class="mb-1">
            <a href="#" class="text-primary">I forgot my password</a>
        </p>
        <p class="mb-0">
            <small class="text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Admin access only. For customer login, visit the main website.
            </small>
        </p>
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
