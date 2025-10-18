@extends('layouts.b2b')

@section('title', 'Edit Package - ' . $package->name)

@section('content')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Header Section -->
            <div class="welcome-section mb-4">
                <div class="card bg-gradient-warning text-white border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h1 class="h3 mb-2">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Travel Package
                                </h1>
                                <p class="mb-0 opacity-90">
                                    Update your package details, images, pricing, and configuration. 
                                    Changes will be saved and may require re-approval depending on modifications.
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <div class="action-buttons">
                                    <a href="{{ route('b2b.travel-agent.packages.index') }}" class="btn btn-light btn-sm me-2">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Packages
                                    </a>
                                    <a href="{{ route('b2b.travel-agent.packages.show', $package->id) }}" class="btn btn-outline-light btn-sm">
                                        <i class="fas fa-eye me-1"></i> View Package
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-2">ID: #{{ $package->id }}</span>
                                    <span class="badge bg-{{ $package->status === 'active' ? 'success' : ($package->status === 'draft' ? 'secondary' : 'danger') }} me-2">
                                        <i class="fas fa-{{ $package->status === 'active' ? 'check-circle' : ($package->status === 'draft' ? 'edit' : 'pause-circle') }} me-1"></i>
                                        {{ ucfirst($package->status) }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar me-1"></i>
                                        Created {{ $package->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form action="{{ route('b2b.travel-agent.packages.update', $package->id) }}" method="POST" enctype="multipart/form-data" id="editPackageForm">
                @csrf
                @method('PUT')
                
                <!-- Section 1: Basic Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Basic Package Information
                        </h5>
                        <p class="text-muted mb-0 small">Update essential details about your travel package</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Package Name -->
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label fw-bold">
                                    Package Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $package->name) }}" 
                                       placeholder="e.g., Istanbul & Cappadocia Adventure - 7 Days" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Choose an engaging name that highlights key destinations</small>
                            </div>

                            <!-- Package Type -->
                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label fw-bold">
                                    Package Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg select2-enhanced @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select package type</option>
                                    @foreach($packageTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $package->type) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the category that best describes your package</small>
                            </div>
                        </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Short Description -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label fw-bold">
                                    Short Description <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Brief, compelling description that will appear in search results and package listings..." 
                                          maxlength="300" required>{{ old('description', $package->description) }}</textarea>
                                <div class="d-flex justify-content-between">
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <span id="descCounter">{{ strlen($package->description ?? '') }}</span>/300 characters
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Detailed Description with Summernote -->
                            <div class="col-12 mb-3">
                                <label for="detailed_description" class="form-label fw-bold">
                                    <i class="fas fa-edit me-1"></i> Detailed Description
                                </label>
                                <textarea class="form-control summernote-editor @error('detailed_description') is-invalid @enderror" 
                                          id="detailed_description" name="detailed_description" 
                                          placeholder="Write a comprehensive description including highlights, experiences, and what makes this package unique...">{{ old('detailed_description', $package->detailed_description) }}</textarea>
                                @error('detailed_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Use the rich text editor to format your package description with images, links, and styling</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Dates & Duration -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-success me-2"></i>
                            Dates & Duration
                        </h5>
                        <p class="text-muted mb-0 small">Configure travel dates and duration details</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Date Range Picker -->
                            <div class="col-md-6 mb-3">
                                <label for="date_range" class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt me-1"></i> Trip Dates <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="date_range" name="date_range" 
                                       placeholder="Select start and end dates..." 
                                       value="{{ old('date_range', ($package->start_date && $package->end_date) ? $package->start_date->format('m/d/Y') . ' - ' . $package->end_date->format('m/d/Y') : '') }}" required>
                                <!-- Hidden inputs for backend -->
                                <input type="hidden" id="start_date" name="start_date" value="{{ old('start_date', $package->start_date ? $package->start_date->format('Y-m-d') : '') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ old('end_date', $package->end_date ? $package->end_date->format('Y-m-d') : '') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the start and end dates for this travel package</small>
                            </div>
                            
                            <!-- Auto-calculated Duration -->
                            <div class="col-md-3 mb-3">
                                <label for="duration" class="form-label fw-bold">
                                    Duration (Days) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                       id="duration" name="duration" value="{{ old('duration', $package->duration) }}" 
                                       min="1" max="365" required>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Auto-calculated from dates</small>
                            </div>
                            
                            <!-- Max Participants -->
                            <div class="col-md-3 mb-3">
                                <label for="max_participants" class="form-label fw-bold">
                                    Max Participants
                                </label>
                                <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                                       id="max_participants" name="max_participants" 
                                       value="{{ old('max_participants', $package->max_participants) }}" 
                                       min="1" max="100" placeholder="20">
                                @error('max_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Difficulty Level -->
                            <div class="col-md-4 mb-3">
                                <label for="difficulty_level" class="form-label fw-bold">
                                    <i class="fas fa-mountain me-1"></i> Difficulty Level
                                </label>
                                <select class="form-select select2-enhanced @error('difficulty_level') is-invalid @enderror" id="difficulty_level" name="difficulty_level">
                                    <option value="">Select difficulty</option>
                                    <option value="easy" {{ old('difficulty_level', $package->difficulty_level) === 'easy' ? 'selected' : '' }}>🟢 Easy - Suitable for beginners</option>
                                    <option value="moderate" {{ old('difficulty_level', $package->difficulty_level) === 'moderate' ? 'selected' : '' }}>🟡 Moderate - Some experience required</option>
                                    <option value="challenging" {{ old('difficulty_level', $package->difficulty_level) === 'challenging' ? 'selected' : '' }}>🟠 Challenging - Good fitness required</option>
                                    <option value="expert" {{ old('difficulty_level', $package->difficulty_level) === 'expert' ? 'selected' : '' }}>🔴 Expert - High experience required</option>
                                </select>
                                @error('difficulty_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pricing & Currency -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-dollar-sign text-warning me-2"></i>
                            Pricing & Currency
                        </h5>
                        <p class="text-muted mb-0 small">Set pricing information for your package</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Base Price -->
                            <div class="col-md-6 mb-3">
                                <label for="base_price" class="form-label fw-bold">
                                    Base Price <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('base_price') is-invalid @enderror" 
                                           id="base_price" name="base_price" value="{{ old('base_price', $package->base_price) }}" 
                                           step="0.01" min="0" placeholder="0.00" required>
                                    <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required style="max-width: 100px;">
                                        @foreach($currencies as $code => $label)
                                            <option value="{{ $code }}" {{ old('currency', $package->currency) === $code ? 'selected' : '' }}>
                                                {{ $code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('base_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Base price per person for this package</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Package Images -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images text-info me-2"></i>
                            Package Images
                        </h5>
                        <p class="text-muted mb-0 small">Upload and manage images for your package</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="image-upload-container">
                            <!-- Upload Area -->
                            <div class="dropzone-wrapper" id="imageDropzone">
                                <div class="dropzone-message">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                    <h5>Drop images here or click to browse</h5>
                                    <p class="text-muted mb-0">Support: JPEG, PNG, WebP (Max: 5MB per image)</p>
                                    <input type="file" id="imageInput" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
                                </div>
                            </div>
                            
                            <!-- Upload Progress -->
                            <div class="upload-progress" id="uploadProgress">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">Uploading images...</small>
                            </div>
                            
                            <!-- Uploaded Images Gallery -->
                            <div class="uploaded-images-gallery" id="imagesGallery">
                                @if($package->hasImages())
                                <h6 class="mt-4 mb-3">
                                    <i class="fas fa-images me-1"></i> Current Images 
                                    <small class="text-muted">({{ $package->getImageCount() }} images)</small>
                                </h6>
                                @if(config('app.debug'))
                                <div class="alert alert-info small mb-3">
                                    <strong>Debug Info:</strong> Package has {{ count($package->getImagesWithUrls()) }} images with URLs.
                                    @if(count($package->getImagesWithUrls()) > 0)
                                        First image keys: {{ implode(', ', array_keys($package->getImagesWithUrls()[0])) }}
                                    @endif
                                </div>
                                @endif
                                <div class="row" id="imagesList">
                                    @foreach($package->getImagesWithUrls() as $image)
                                    <div class="col-md-3 col-sm-4 col-6 mb-3 image-item" data-image-id="{{ $image['id'] }}">
                                        <div class="image-card position-relative">
                                            @php
                                                // Use the best available image size with proper fallbacks
                                                $imageUrl = '';
                                                
                                                // First try the URLs array
                                                if (isset($image['urls']) && is_array($image['urls']) && !empty($image['urls'])) {
                                                    $imageUrl = $image['urls']['thumbnail'] ?? 
                                                               $image['urls']['small'] ?? 
                                                               $image['urls']['medium'] ?? 
                                                               $image['urls']['large'] ?? 
                                                               reset($image['urls']);
                                                }
                                                
                                                // Fallback to sizes array
                                                if (empty($imageUrl) && isset($image['sizes']) && is_array($image['sizes']) && !empty($image['sizes'])) {
                                                    $firstSize = reset($image['sizes']);
                                                    $imageUrl = $firstSize ? asset('storage/' . $firstSize) : '';
                                                }
                                                
                                                // Fallback to direct path
                                                if (empty($imageUrl) && !empty($image['path'])) {
                                                    $imageUrl = asset('storage/' . $image['path']);
                                                }
                                                
                                                // Fallback to URL field
                                                if (empty($imageUrl) && !empty($image['url'])) {
                                                    $imageUrl = $image['url'];
                                                }
                                                
                                                // Ultimate fallback - create a placeholder
                                                if (empty($imageUrl)) {
                                                    $imageUrl = 'data:image/svg+xml;base64,' . base64_encode('<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f8f9fa"/><text x="50%" y="50%" font-family="Arial" font-size="16" fill="#6c757d" text-anchor="middle" dy=".3em">Image Not Found</text></svg>');
                                                }
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $image['alt_text'] ?? 'Package Image' }}" class="img-fluid w-100" style="height: 180px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            
                                            <!-- Error Fallback -->
                                            <div class="d-none bg-light border rounded d-flex align-items-center justify-content-center" style="height: 180px;">
                                                <div class="text-center text-muted">
                                                    <i class="fas fa-image fa-2x mb-2"></i>
                                                    <p class="mb-0 small">Image not available</p>
                                                    @if(config('app.debug'))
                                                        <small class="text-danger">ID: {{ $image['id'] ?? 'N/A' }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Image Controls -->
                                            <div class="image-controls position-absolute top-0 end-0 p-2">
                                                @if($image['is_main'])
                                                <span class="badge bg-warning text-dark mb-1 d-block">
                                                    <i class="fas fa-star"></i> Main
                                                </span>
                                                @else
                                                <button type="button" class="btn btn-sm btn-warning mb-1 set-main-btn" data-image-id="{{ $image['id'] }}" title="Set as main image">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-danger delete-image-btn" data-image-id="{{ $image['id'] }}" title="Delete image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Drag Handle -->
                                            <div class="drag-handle position-absolute bottom-0 start-0 p-2">
                                                <i class="fas fa-grip-vertical text-white"></i>
                                            </div>
                                            
                                            <!-- Image Info -->
                                            <div class="image-info position-absolute bottom-0 start-0 end-0 p-2 bg-dark bg-opacity-50 text-white">
                                                <small class="d-block text-truncate">{{ $image['original_name'] ?? 'Image' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="empty-images-state text-center py-4" id="emptyImagesState">
                                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Images Uploaded Yet</h6>
                                    <p class="text-muted small mb-0">Upload some images to showcase your package</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Package Options -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog text-secondary me-2"></i>
                            Package Options & Features
                        </h5>
                        <p class="text-muted mb-0 small">Configure package features and booking options</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" value="1" {{ old('is_premium', $package->is_premium) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_premium">
                                        <i class="fas fa-crown text-warning me-2"></i>Premium Package
                                    </label>
                                    <small class="form-text text-muted d-block">Mark as premium with exclusive features</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_featured">
                                        <i class="fas fa-star text-warning me-2"></i>Featured Package
                                    </label>
                                    <small class="form-text text-muted d-block">Display prominently in listings</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allow_customization" name="allow_customization" value="1" {{ old('allow_customization', $package->allow_customization) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="allow_customization">
                                        <i class="fas fa-edit me-2"></i>Allow Customization
                                    </label>
                                    <small class="form-text text-muted d-block">Let customers request modifications</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="instant_booking" name="instant_booking" value="1" {{ old('instant_booking', $package->instant_booking) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="instant_booking">
                                        <i class="fas fa-bolt text-primary me-2"></i>Instant Booking
                                    </label>
                                    <small class="form-text text-muted d-block">Enable immediate bookings without approval</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="uses_b2b_services" name="uses_b2b_services" value="1" {{ old('uses_b2b_services', $package->uses_b2b_services) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="uses_b2b_services">
                                        <i class="fas fa-handshake me-2"></i>Uses B2B Services
                                    </label>
                                    <small class="form-text text-muted d-block">Package includes B2B partner services</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Status & Visibility -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-toggle-on text-success me-2"></i>
                            Status & Visibility
                        </h5>
                        <p class="text-muted mb-0 small">Control package availability and visibility</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-bold">
                                    Package Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2-enhanced @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="draft" {{ old('status', $package->status) === 'draft' ? 'selected' : '' }}>📝 Draft</option>
                                    <option value="active" {{ old('status', $package->status) === 'active' ? 'selected' : '' }}>✅ Active</option>
                                    <option value="inactive" {{ old('status', $package->status) === 'inactive' ? 'selected' : '' }}>⏸️ Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Control package visibility and bookability</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-footer p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('b2b.travel-agent.packages.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <a href="{{ route('b2b.travel-agent.packages.show', $package->id) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-1"></i> View Package
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success btn-lg" id="updatePackageBtn">
                                    <i class="fas fa-save me-2"></i> Update Package
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<!-- SortableJS for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
/* Modern Edit Page Styles */
.welcome-section .creation-stats .stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
}
.welcome-section .creation-stats .stat-label {
    font-size: 0.875rem;
    color: rgba(255,255,255,0.9);
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.form-label.fw-bold {
    color: #495057;
    font-size: 0.95rem;
}

.form-control, .form-select {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Image Upload Styles */
.image-upload-container { margin-bottom: 2rem; }
.dropzone-wrapper { 
    border: 2px dashed #dee2e6; 
    border-radius: 0.5rem; 
    background-color: #f8f9fa; 
    transition: all 0.3s ease; 
    cursor: pointer; 
    min-height: 200px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
}
.dropzone-wrapper:hover { 
    border-color: #0d6efd; 
    background-color: #e7f1ff; 
}
.dropzone-wrapper.drag-over { 
    border-color: #0d6efd; 
    background-color: #e7f1ff; 
    border-style: solid; 
    transform: scale(1.02); 
}
.dropzone-message { text-align: center; width: 100%; }
.dropzone-message .fa-cloud-upload-alt { 
    color: #6c757d; 
    margin-bottom: 1rem; 
}
.dropzone-wrapper:hover .fa-cloud-upload-alt { 
    color: #0d6efd; 
}
.upload-progress { 
    margin-top: 1rem; 
    display: none; 
}
.uploaded-images-gallery { 
    margin-top: 2rem; 
}
.image-item { 
    transition: all 0.3s ease; 
}
.image-card { 
    border-radius: 0.5rem; 
    overflow: hidden; 
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
    transition: all 0.3s ease; 
    cursor: pointer; 
}
.image-card:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); 
}
.image-controls { 
    opacity: 0; 
    transition: all 0.3s ease; 
}
.image-item:hover .image-controls { 
    opacity: 1; 
}
.image-controls .btn { 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); 
    border: none; 
    width: 32px; 
    height: 32px; 
    padding: 0; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 0.8rem; 
}
.drag-handle { 
    opacity: 0; 
    transition: all 0.3s ease; 
    cursor: grab; 
}
.image-item:hover .drag-handle { 
    opacity: 1; 
}
.drag-handle:active { 
    cursor: grabbing; 
}
.sortable-ghost { 
    opacity: 0.4; 
    transform: rotate(5deg); 
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .image-controls { opacity: 1; }
    .drag-handle { opacity: 1; }
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-content {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    text-align: center;
}
</style>
@endpush

@push('scripts')
<!-- jQuery (required for other libraries) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Moment.js (required for daterangepicker) -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- Date Range Picker JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
$(document).ready(function() {
    // Success and error messages
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // Initialize Select2
    $('.select2-enhanced').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Initialize Summernote
    $('.summernote-editor').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Initialize Date Range Picker
    $('#date_range').daterangepicker({
        startDate: moment().add(1, 'days'),
        endDate: moment().add(8, 'days'),
        minDate: moment().add(1, 'days'),
        locale: {
            format: 'MM/DD/YYYY'
        }
    }, function(start, end, label) {
        // Update hidden fields
        $('#start_date').val(start.format('YYYY-MM-DD'));
        $('#end_date').val(end.format('YYYY-MM-DD'));
        
        // Calculate and update duration
        const duration = end.diff(start, 'days') + 1;
        $('#duration').val(duration);
    });

    // Character counter for description
    $('#description').on('input', function() {
        const current = $(this).val().length;
        const maxLength = 300;
        $('#descCounter').text(current);
        
        if (current > maxLength) {
            $(this).addClass('is-invalid');
            $('#descCounter').parent().addClass('text-danger').removeClass('text-muted');
        } else {
            $(this).removeClass('is-invalid');
            $('#descCounter').parent().addClass('text-muted').removeClass('text-danger');
        }
    });

    // Image Upload Functionality
    let uploadedImagesCount = {{ $package->getImageCount() }};
    
    // Make dropzone clickable
    $('#imageDropzone').on('click', function() {
        $('#imageInput').click();
    });
    
    // Handle drag and drop
    $('#imageDropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('#imageDropzone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    $('#imageDropzone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        const files = e.originalEvent.dataTransfer.files;
        handleImageUpload(files);
    });
    
    // Handle file input change
    $('#imageInput').on('change', function() {
        const files = this.files;
        handleImageUpload(files);
    });
    
    // Image upload handler
    function handleImageUpload(files) {
        if (files.length === 0) return;
        
        const formData = new FormData();
        
        // Add files to form data
        for (let i = 0; i < files.length; i++) {
            formData.append('image', files[i]);
        }
        
        // Add CSRF token
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Show progress
        $('#uploadProgress').show();
        
        // Upload each file separately
        Array.from(files).forEach(file => {
            const singleFormData = new FormData();
            singleFormData.append('image', file);
            singleFormData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            $.ajax({
                url: '{{ route("b2b.travel-agent.packages.images.upload", $package->id) }}',
                type: 'POST',
                data: singleFormData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        addImageToGallery(response.image);
                        uploadedImagesCount++;
                        updateEmptyState();
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to upload image: ' + (xhr.responseJSON?.message || 'Unknown error'));
                },
                complete: function() {
                    $('#uploadProgress').hide();
                }
            });
        });
    }
    
    // Add image to gallery
    function addImageToGallery(image) {
        const imageHtml = `
            <div class="col-md-3 col-sm-4 col-6 mb-3 image-item" data-image-id="${image.id}">
                <div class="image-card position-relative">
                    <img src="/storage/images/packages/${image.sizes.thumbnail || image.filename}" alt="${image.alt_text || 'Package Image'}" class="img-fluid w-100" style="height: 180px; object-fit: cover;">
                    
                    <div class="image-controls position-absolute top-0 end-0 p-2">
                        ${image.is_main ? 
                            '<span class="badge bg-warning text-dark mb-1 d-block"><i class="fas fa-star"></i> Main</span>' : 
                            '<button type="button" class="btn btn-sm btn-warning mb-1 set-main-btn" data-image-id="' + image.id + '" title="Set as main image"><i class="fas fa-star"></i></button>'
                        }
                        <button type="button" class="btn btn-sm btn-danger delete-image-btn" data-image-id="${image.id}" title="Delete image">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="drag-handle position-absolute bottom-0 start-0 p-2">
                        <i class="fas fa-grip-vertical text-white"></i>
                    </div>
                    
                    <div class="image-info position-absolute bottom-0 start-0 end-0 p-2 bg-dark bg-opacity-50 text-white">
                        <small class="d-block text-truncate">${image.original_name || 'Image'}</small>
                    </div>
                </div>
            </div>
        `;
        
        $('#imagesList').append(imageHtml);
        toastr.success('Image uploaded successfully!');
    }
    
    // Update empty state
    function updateEmptyState() {
        if (uploadedImagesCount > 0) {
            $('#emptyImagesState').hide();
            $('#imagesList').show();
        } else {
            $('#emptyImagesState').show();
            $('#imagesList').hide();
        }
    }
    
    // Delete image
    $(document).on('click', '.delete-image-btn', function() {
        const imageId = $(this).data('image-id');
        const imageItem = $(this).closest('.image-item');
        
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: '{{ route("b2b.travel-agent.packages.images.delete", [$package->id, "IMAGE_ID"]) }}'.replace('IMAGE_ID', imageId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        imageItem.fadeOut(300, function() {
                            $(this).remove();
                            uploadedImagesCount--;
                            updateEmptyState();
                        });
                        toastr.success('Image deleted successfully!');
                    }
                },
                error: function() {
                    toastr.error('Failed to delete image');
                }
            });
        }
    });
    
    // Set main image
    $(document).on('click', '.set-main-btn', function() {
        const imageId = $(this).data('image-id');
        
        $.ajax({
            url: '{{ route("b2b.travel-agent.packages.images.set-main", $package->id) }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                image_id: imageId
            },
            success: function(response) {
                if (response.success) {
                    // Update UI - remove all main badges and add to selected
                    $('.image-controls .badge').replaceWith('<button type="button" class="btn btn-sm btn-warning mb-1 set-main-btn" title="Set as main image"><i class="fas fa-star"></i></button>');
                    $(`.image-item[data-image-id="${imageId}"] .set-main-btn`).replaceWith('<span class="badge bg-warning text-dark mb-1 d-block"><i class="fas fa-star"></i> Main</span>');
                    toastr.success('Main image updated!');
                }
            },
            error: function() {
                toastr.error('Failed to set main image');
            }
        });
    });
    
    // Initialize Sortable for image reordering
    if (document.getElementById('imagesList')) {
        new Sortable(document.getElementById('imagesList'), {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                const imageIds = [];
                $('#imagesList .image-item').each(function() {
                    imageIds.push($(this).data('image-id'));
                });
                
                $.ajax({
                    url: '{{ route("b2b.travel-agent.packages.images.reorder", $package->id) }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        image_ids: imageIds
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Images reordered successfully!');
                        }
                    }
                });
            }
        });
    }
    
    // Form validation
    $('#editPackageForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['name', 'type', 'description', 'duration', 'base_price', 'currency', 'status'];
        
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val() || input.val().trim() === '') {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fill in all required fields');
            return false;
        }
        
        // Show loading
        $('#updatePackageBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Updating...');
    });
    
    // Real-time validation
    $('input[required], select[required], textarea[required]').on('input change', function() {
        if ($(this).val() && $(this).val().trim() !== '') {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
