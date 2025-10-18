@extends('layouts.auth')

@section('title', 'Account Pending Approval')

@section('content')
    <div class="text-center">
        <div class="mb-4">
            <i class="fas fa-clock text-warning" style="font-size: 4rem;"></i>
        </div>
        
        <h3 class="text-warning mb-3">Account Pending Approval</h3>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Thank you for registering!</strong><br>
            Your partner account has been submitted for review.
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-tasks mr-2"></i>
                    What happens next?
                </h5>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Registration Submitted</h6>
                            <p class="timeline-description">Your registration has been successfully submitted.</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Under Review</h6>
                            <p class="timeline-description">Our admin team is reviewing your company details and credentials.</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Approval Notification</h6>
                            <p class="timeline-description">You'll receive an email notification once your account is approved.</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Access Granted</h6>
                            <p class="timeline-description">Once approved, you can login and start creating Umrah packages.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-phone"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Need Help?</span>
                            <span class="info-box-number">+251-911-285865</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Email Support</span>
                            <span class="info-box-number">support@seferet.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="mb-2">
                <small class="text-muted">
                    <i class="fas fa-clock mr-1"></i>
                    Review typically takes 24-48 hours
                </small>
            </p>
            <a href="{{ route('b2b.login') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Login
            </a>
        </div>
    </div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    height: 100%;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 60px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    font-weight: bold;
    margin-bottom: 5px;
    color: #495057;
}

.timeline-description {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.info-box-number {
    font-size: 14px !important;
    font-weight: normal;
}
</style>
@endsection
