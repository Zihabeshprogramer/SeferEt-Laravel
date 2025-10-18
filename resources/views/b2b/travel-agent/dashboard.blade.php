@extends('layouts.b2b')

@section('title', 'Travel Agent Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-plane text-info mr-2"></i>
                Travel Agent Dashboard
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.travel-agent.packages.create') }}" class="btn btn-info">
                <i class="fas fa-plus mr-1"></i>
                Create Package
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['totalPackages'] ?? 0 }}</h3>
                    <p>Total Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['activePackages'] ?? 0 }}</h3>
                    <p>Active Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['draftPackages'] ?? 0 }}</h3>
                    <p>Draft Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['recentPackages'] ?? 0 }}</h3>
                    <p>Recent Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Draft Packages Section --}}
    @if(isset($draftPackages) && $draftPackages->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Draft Packages
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $draftPackages->count() }} Draft(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($draftPackages as $draft)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card card-outline card-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-1">
                                            {{ $draft->name ?? 'Untitled Package' }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $draft->updated_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-tasks mr-1"></i>
                                            Step {{ $draft->current_step }}: {{ $draft->getCurrentStepName() }}
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: {{ $draft->getProgressPercentage() }}%"
                                                 aria-valuenow="{{ $draft->getProgressPercentage() }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $draft->getProgressPercentage() }}% Complete</small>
                                    </div>
                                    
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <a href="{{ route('b2b.travel-agent.packages.continue-draft', $draft->id) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-edit mr-1"></i>
                                            Continue
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteDraft({{ $draft->id }})">
                                            <i class="fas fa-trash mr-1"></i>
                                            Delete
                                        </button>
                                    </div>
                                    
                                    @if($draft->isExpired())
                                    <div class="mt-2">
                                        <span class="badge badge-warning">Expired</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if(method_exists($draftPackages, 'hasPages') && $draftPackages->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $draftPackages->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-rocket mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-suitcase-rolling fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Welcome to Your Travel Agent Portal</h4>
                        <p class="text-muted">Start by creating your first travel package.</p>
                        
                        <div class="row justify-content-center mt-4">
                            <div class="col-md-2 mb-2">
                                <a href="{{ route('b2b.travel-agent.packages.index') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-box mr-2"></i>
                                    Packages
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="{{ route('b2b.travel-agent.bookings.all') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-bookmark mr-2"></i>
                                    All Bookings
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="{{ route('b2b.travel-agent.bookings') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    Package Bookings
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="{{ route('b2b.travel-agent.customers') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-users mr-2"></i>
                                    Customers
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="{{ route('b2b.travel-agent.commissions') }}" class="btn btn-danger btn-block">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    Commissions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box {
            border-radius: 0.5rem;
        }
        
        .card-outline {
            border-width: 1px;
        }
        
        .progress {
            background-color: #e9ecef;
        }
    </style>
@stop

@section('js')
<script>
function deleteDraft(draftId) {
    if (confirm('Are you sure you want to delete this draft? This action cannot be undone.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("b2b/travel-agent/drafts") }}/' + draftId;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@stop
