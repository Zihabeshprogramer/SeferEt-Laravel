@extends('layouts.b2b')

@section('title', 'Notifications')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-bell text-info mr-2"></i>
                Notifications
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-success btn-sm" id="markAllRead">
                <i class="fas fa-check-double mr-1"></i>
                Mark All as Read
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-inbox mr-2"></i>
                        Your Notifications
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No notifications yet</h4>
                        <p class="text-muted">When you have new notifications, they will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
