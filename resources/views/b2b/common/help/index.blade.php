@extends('layouts.b2b')

@section('title', 'Help & Support')

@section('content_header')
    <div class="row">
        <div class="col-md-12">
            <h1 class="m-0">
                <i class="fas fa-question-circle text-info mr-2"></i>
                Help & Support
            </h1>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book mr-2"></i>
                        Frequently Asked Questions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Help Documentation Coming Soon</h4>
                        <p class="text-muted">FAQs and documentation will be available here.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-headset mr-2"></i>
                        Contact Support
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> support@seferet.com</p>
                    <p><strong>Phone:</strong> +1 (555) 123-4567</p>
                    <p><strong>Hours:</strong> 24/7</p>
                    
                    <hr>
                    
                    <button class="btn btn-primary btn-block">
                        <i class="fas fa-envelope mr-2"></i>
                        Send Message
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop
