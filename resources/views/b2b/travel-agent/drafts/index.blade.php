@extends('layouts.b2b')

@section('title', 'Draft Packages')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-edit text-warning mr-2"></i>
                Draft Packages
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.travel-agent.packages.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Create New Package
            </a>
            @if(request('status') !== 'expired' && $expiredDraftsCount > 0)
            <button type="button" class="btn btn-warning ml-2" onclick="cleanupExpired()">
                <i class="fas fa-trash mr-1"></i>
                Cleanup Expired
            </button>
            @endif
        </div>
    </div>
@stop

@section('content')
    {{-- Filter Tabs --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') !== 'expired' ? 'active' : '' }}" 
                               href="{{ route('b2b.travel-agent.drafts') }}">
                                <i class="fas fa-edit mr-1"></i>
                                Active Drafts
                                @if($activeDraftsCount > 0)
                                <span class="badge badge-info ml-1">{{ $activeDraftsCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') === 'expired' ? 'active' : '' }}" 
                               href="{{ route('b2b.travel-agent.drafts', ['status' => 'expired']) }}">
                                <i class="fas fa-clock mr-1"></i>
                                Expired Drafts
                                @if($expiredDraftsCount > 0)
                                <span class="badge badge-warning ml-1">{{ $expiredDraftsCount }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    @if($draftPackages->count())
                        <div class="row">
                            @foreach($draftPackages as $draft)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card {{ $draft->isExpired() ? 'card-outline card-warning' : 'card-outline card-secondary' }} h-100">
                                    <div class="card-body d-flex flex-column">
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
                                                <div class="progress-bar {{ $draft->isExpired() ? 'bg-warning' : 'bg-info' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $draft->getProgressPercentage() }}%"
                                                     aria-valuenow="{{ $draft->getProgressPercentage() }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $draft->getProgressPercentage() }}% Complete</small>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                @if(!$draft->isExpired())
                                                <a href="{{ route('b2b.travel-agent.packages.continue-draft', $draft->id) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Continue
                                                </a>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteDraft({{ $draft->id }})">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Delete
                                                </button>
                                            </div>
                                            
                                            @if($draft->isExpired())
                                            <div class="mt-2 text-center">
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Expired
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        {{-- Pagination --}}
                        @if($draftPackages->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $draftPackages->appends(request()->query())->links() }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-edit fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">
                                @if(request('status') === 'expired')
                                    No Expired Drafts Found
                                @else
                                    No Draft Packages Found
                                @endif
                            </h4>
                            <p class="text-muted">
                                @if(request('status') === 'expired')
                                    All your draft packages are still active.
                                @else
                                    Start creating a new travel package to see drafts here.
                                @endif
                            </p>
                            
                            @if(request('status') !== 'expired')
                            <div class="mt-3">
                                <a href="{{ route('b2b.travel-agent.packages.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create Your First Package
                                </a>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-outline {
            border-width: 1px;
        }
        
        .progress {
            background-color: #e9ecef;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
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

function cleanupExpired() {
    if (confirm('Are you sure you want to delete all expired drafts? This action cannot be undone.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("b2b.travel-agent.drafts.cleanup-expired") }}';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@stop
