@extends('layouts.b2b')

@section('title', 'Edit Hotel - ' . $hotel->name)

@push('styles')
<style>
    /* Full width container */
    .content-wrapper .content {
        padding: 0;
    }
    .section-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
    }
    
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem 0.5rem 0 0;
        margin: -1px -1px 0 -1px;
    }
    
    .section-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .section-header small {
        opacity: 0.9;
        font-size: 0.875rem;
    }
    
    .image-preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .image-preview-item {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 0.5rem;
        transition: all 0.3s;
    }
    
    .image-preview-item:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .image-preview-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 0.375rem;
    }
    
    .image-preview-item .remove-image {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .image-preview-item .remove-image:hover {
        background: rgba(220, 53, 69, 1);
        transform: scale(1.1);
    }
    
    .upload-area {
        border: 2px dashed #ced4da;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #f8f9fa;
    }
    
    .upload-area:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }
    
    .upload-area.dragover {
        border-color: #667eea;
        background: #e3e9ff;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .select2-container .select2-selection--single {
        height: 38px !important;
        padding: 6px 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    .select2-container {
        width: 100% !important;
    }
    .section-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: #fff;
    }

    @media (max-width: 767.98px) {
        .section-card {
            margin-bottom: 1rem;
        }
    }
    
    .existing-image {
        transition: all 0.3s ease;
    }
    
    .existing-image.removing {
        opacity: 0.3;
        border-color: #dc3545 !important;
    }
</style>
@endpush

@section('page-title', 'Edit Hotel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.hotels.index') }}">My Hotels</a></li>
    <li class="breadcrumb-item active">Edit Hotel</li>
@endsection

@section('content')
    <!-- Hotel Form -->
    <form action="{{ route('b2b.hotel-provider.hotels.update', $hotel) }}" method="POST" enctype="multipart/form-data" id="hotelForm">
        @csrf
        @method('PUT')
        <div class="container my-4">
             <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-10">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-12">
                            <!-- Basic Information -->
                            <div class="card section-card">
                                <div class="section-header">
                                    <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                                    <small>Essential details about your hotel</small>
                                </div>
                                <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="name" class="form-label">
                                                    <i class="fas fa-hotel me-1"></i>Hotel Name <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                    id="name" name="name" value="{{ old('name', $hotel->name) }}" 
                                                    placeholder="Enter your hotel name" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="type" class="form-label">
                                                    <i class="fas fa-building me-1"></i>Hotel Type <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control select2 @error('type') is-invalid @enderror" 
                                                        id="type" name="type" data-placeholder="Select Hotel Type" required>
                                                    <option value=""></option>
                                                    @foreach(\App\Models\Hotel::TYPES as $value => $label)
                                                        <option value="{{ $value }}" {{ old('type', $hotel->type) == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('type')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="star_rating" class="form-label">
                                                    <i class="fas fa-star me-1"></i>Star Rating <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control select2 @error('star_rating') is-invalid @enderror" 
                                                        id="star_rating" name="star_rating" data-placeholder="Select Star Rating" required>
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <option value="{{ $i }}" {{ old('star_rating', $hotel->star_rating) == $i ? 'selected' : '' }}>
                                                            {{ str_repeat('★', $i) }} {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                @error('star_rating')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="description" class="form-label">
                                                    <i class="fas fa-align-left me-1"></i>Description
                                                </label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                                        id="description" name="description" rows="4" 
                                                        placeholder="Describe your hotel's features, amenities, and unique selling points...">{{ old('description', $hotel->description) }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <!-- Location Information -->
                            <div class="card section-card">
                                <div class="section-header">
                                    <h5><i class="fas fa-map-marker-alt me-2"></i>Location Information</h5>
                                    <small>Where is your hotel located?</small>
                                </div>
                                <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="address" class="form-label">
                                                    <i class="fas fa-map-marked-alt me-1"></i>Address <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                                        id="address" name="address" rows="2" 
                                                        placeholder="Enter full street address" required>{{ old('address', $hotel->address) }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-5 mb-3">
                                                <label for="city" class="form-label">
                                                    <i class="fas fa-city me-1"></i>City <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                                    id="city" name="city" value="{{ old('city', $hotel->city) }}" 
                                                    placeholder="e.g., Mecca, Medina" required>
                                                @error('city')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="country" class="form-label">
                                                    <i class="fas fa-globe me-1"></i>Country <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control select2 @error('country') is-invalid @enderror" 
                                                        id="country" name="country" data-placeholder="Select Country" required>
                                                    <option value=""></option>
                                                    <option value="Saudi Arabia" {{ $hotel->country == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                                                    <option value="Turkey" {{ $hotel->country == 'Turkey' ? 'selected' : '' }}>Turkey</option>
                                                    <option value="UAE" {{ $hotel->country == 'UAE' ? 'selected' : '' }}>UAE</option>
                                                    <option value="Egypt" $hotel->country == 'Egypt' ? 'selected' : '' }}>Egypt</option>
                                                    <option value="Jordan" {{ $hotel->country == 'Jordan' ? 'selected' : '' }}>Jordan</option>
                                                </select>
                                                @error('country')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label for="postal_code" class="form-label">
                                                    <i class="fas fa-mail-bulk me-1"></i>Postal Code
                                                </label>
                                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                    id="postal_code" name="postal_code" value="{{ old('postal_code',$hotel->postal_code) }}" 
                                                    placeholder="Optional">
                                                @error('postal_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="distance_to_haram" class="form-label">
                                                    <i class="fas fa-kaaba me-1"></i>Distance to Haram (KM)
                                                </label>
                                                <input type="number" step="0.1" class="form-control @error('distance_to_haram') is-invalid @enderror" 
                                                    id="distance_to_haram" name="distance_to_haram" value="{{ old('distance_to_haram',$hotel->distance_to_haram) }}" 
                                                    placeholder="0.0">
                                                @error('distance_to_haram')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="distance_to_airport" class="form-label">
                                                    <i class="fas fa-plane me-1"></i>Distance to Airport (KM)
                                                </label>
                                                <input type="number" step="0.1" class="form-control @error('distance_to_airport') is-invalid @enderror" 
                                                    id="distance_to_airport" name="distance_to_airport" value="{{ old('distance_to_airport',$hotel->distance_to_airport) }}" 
                                                    placeholder="0.0">
                                                @error('distance_to_airport')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="latitude" class="form-label">
                                                    <i class="fas fa-map-pin me-1"></i>Latitude
                                                </label>
                                                <input type="number" step="0.00000001" class="form-control @error('latitude') is-invalid @enderror" 
                                                    id="latitude" name="latitude" value="{{ old('latitude',$hotel->latitude) }}" 
                                                    placeholder="e.g., 21.4225">
                                                @error('latitude')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="longitude" class="form-label">
                                                    <i class="fas fa-map-pin me-1"></i>Longitude
                                                </label>
                                                <input type="number" step="0.00000001" class="form-control @error('longitude') is-invalid @enderror" 
                                                    id="longitude" name="longitude" value="{{ old('longitude',$hotel->longitude) }}" 
                                                    placeholder="e.g., 39.8262">
                                                @error('longitude')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <!-- Contact Information -->
                            <div class="card section-card">
                                <div class="section-header">
                                    <h5><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                                    <small>How can guests reach you?</small>
                                </div>
                                <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="phone" class="form-label">
                                                    <i class="fas fa-phone me-1"></i>Phone
                                                </label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                    id="phone" name="phone" value="{{ old('phone',$hotel->phone) }}" 
                                                    placeholder="+966 50 123 4567">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="email" class="form-label">
                                                    <i class="fas fa-envelope me-1"></i>Email
                                                </label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                    id="email" name="email" value="{{ old('email',$hotel->email) }}" 
                                                    placeholder="info@hotel.com">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="website" class="form-label">
                                                    <i class="fas fa-globe me-1"></i>Website
                                                </label>
                                                <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                                    id="website" name="website" value="{{ old('website',$hotel->website) }}" 
                                                    placeholder="https://www.example.com">
                                                @error('website')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <!-- Hotel Policies -->
                            <div class="card section-card">
                                <div class="section-header">
                                    <h5><i class="fas fa-clipboard-list me-2"></i>Hotel Policies & Amenities</h5>
                                    <small>Define your check-in/out times and available amenities</small>
                                </div>
                                <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="check_in_time" class="form-label">
                                                    <i class="fas fa-sign-in-alt me-1"></i>Check-in Time
                                                </label>
                                                <input type="time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                                    id="check_in_time" name="check_in_time" value="{{ old('check_in_time', $hotel->check_in_time ? $hotel->check_in_time->format('H:i') : '15:00') }}">
                                                @error('check_in_time')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="check_out_time" class="form-label">
                                                    <i class="fas fa-sign-out-alt me-1"></i>Check-out Time
                                                </label>
                                                <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                                    id="check_out_time" name="check_out_time" value="{{ old('check_out_time', $hotel->check_out_time ? $hotel->check_out_time->format('H:i') : '11:00') }}">
                                                @error('check_out_time')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="amenities" class="form-label">
                                                    <i class="fas fa-concierge-bell me-1"></i>Amenities
                                                </label>
                                                @php
                                                    $hotelAmenities = old('amenities', $hotel->amenities ?? []);
                                                @endphp
                                                <select class="form-control select2-multiple @error('amenities') is-invalid @enderror" 
                                                        id="amenities" name="amenities[]" multiple="multiple">
                                                    <option value="wifi" {{ in_array('wifi', $hotelAmenities) ? 'selected' : '' }}>Free WiFi </option>
                                                    <option value="parking" {{ in_array('parking', $hotelAmenities) ? 'selected' : '' }}>Parking</option>
                                                    <option value="pool" {{ in_array('pool', $hotelAmenities) ? 'selected' : '' }}>Swimming Pool</option>
                                                    <option value="gym" {{ in_array('gym', $hotelAmenities) ? 'selected' : '' }}>Fitness Center</option>
                                                    <option value="spa" {{ in_array('spa', $hotelAmenities) ? 'selected' : '' }}>Spa</option>
                                                    <option value="restaurant" {{ in_array('restaurant', $hotelAmenities) ? 'selected' : '' }}>Restaurant</option>
                                                    <option value="room_service" {{ in_array('room_service', $hotelAmenities) ? 'selected' : '' }}>Room Service</option>
                                                    <option value="laundry" {{ in_array('laundry', $hotelAmenities) ? 'selected' : '' }}>Laundry Service</option>
                                                    <option value="concierge" {{ in_array('concierge', $hotelAmenities) ? 'selected' : '' }}>Concierge</option>
                                                    <option value="business_center" {{ in_array('business_center', $hotelAmenities) ? 'selected' : '' }}>Business Center</option>
                                                    <option value="conference_room" {{ in_array('conference_room', $hotelAmenities) ? 'selected' : '' }}>Conference Room</option>
                                                    <option value="airport_shuttle" {{ in_array('airport_shuttle', $hotelAmenities) ? 'selected' : '' }}>Airport Shuttle</option>
                                                </select>
                                                <small class="form-text text-muted">Select multiple amenities available at your hotel</small>
                                                @error('amenities')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-12">
                            <!-- Hotel Images -->
                            <div class="card section-card">
                                <div class="section-header">
                                    <h5><i class="fas fa-images me-2"></i>Hotel Images</h5>
                                    <small>Upload up to 10 images</small>
                                </div>
                                <div class="card-body">
                                    <!-- Existing Images -->
                                    @if(!empty($hotel->images) && is_array($hotel->images))
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="fas fa-images me-2"></i>Current Images</h6>
                                        <div class="image-preview-container">
                                            @foreach($hotel->images as $image)
                                                <div class="image-preview-item existing-image" data-image-id="{{ $image['id'] }}">
                                                    <img src="{{ Storage::url($image['sizes']['medium'] ?? $image['sizes']['original']) }}" alt="Hotel Image">
                                                    <button type="button" class="remove-image" onclick="removeExistingImage('{{ $image['id'] }}')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    @if($image['is_main'] ?? false)
                                                        <div class="badge bg-success" style="position: absolute; bottom: 0.5rem; left: 0.5rem;">
                                                            <i class="fas fa-star"></i> Main
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="remove_images" id="remove_images" value="">
                                    </div>
                                    @endif
                                    
                                    <!-- Upload New Images -->
                                    <div class="mb-3">
                                        <h6 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Add New Images</h6>
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Drag & Drop Images</h5>
                                            <p class="text-muted mb-0">or click to browse</p>
                                            <input type="file" class="d-none" id="imageInput" name="new_images[]" 
                                                accept="image/jpeg,image/jpg,image/png" multiple>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle"></i> Accepted: JPG, JPEG, PNG (Max 5MB each, up to 10 images total)
                                        </small>
                                        @error('new_images')
                                            <div class="text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                        @error('new_images.*')
                                            <div class="text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- New Image Previews -->
                                    <div id="imagePreviewContainer" class="image-preview-container"></div>
                                </div>
                            </div>
                            
                            <!-- Quick Info Card -->
                            <div class="card section-card">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Quick Tips</h6>
                                    <ul class="list-unstyled small text-muted mb-0">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use high-quality images</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Include exterior & interior photos</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Show amenities & facilities</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Accurate location information</li>
                                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Complete contact details</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card section-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Hotels
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i> Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Update Hotel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<!-- jQuery Input Mask -->
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
<script>
// Global variables
let selectedFiles = [];
let imagesToRemove = [];
let existingImageCount = {{ !empty($hotel->images) ? count($hotel->images) : 0 }};

function resetForm() {
    if (confirm('Are you sure you want to reset all form data?')) {
        document.getElementById('hotelForm').reset();
        selectedFiles = [];
        document.getElementById('imagePreviewContainer').innerHTML = '';
        // Reset Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2, .select2-multiple').val(null).trigger('change');
        }
    }
}

// Initialize page-specific functionality
$(document).ready(function() {
    console.log('Hotel edit page loaded');
    
    // Initialize input masks
    if (typeof Inputmask !== 'undefined') {
        // Phone number mask - international format
        Inputmask({
            mask: '+999 99 999 9999',
            placeholder: '+___ __ ___ ____',
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: false
        }).mask('#phone');
        
        // Postal code mask - alphanumeric
        Inputmask({
            mask: '999999',
            placeholder: '',
            showMaskOnHover: false,
            clearIncomplete: false
        }).mask('#postal_code');
        
        // Distance masks - decimal with 2 decimal places, max 9999.99 km
        Inputmask({
            alias: 'decimal',
            groupSeparator: '',
            digits: 2,
            digitsOptional: true,
            placeholder: '0',
            rightAlign: false,
            min: 0,
            max: 9999.99,
            autoUnmask: true
        }).mask('#distance_to_haram, #distance_to_airport');
        
        // Latitude mask - between -90 and 90, up to 8 decimal places
        Inputmask({
            alias: 'decimal',
            groupSeparator: '',
            digits: 8,
            digitsOptional: true,
            placeholder: '0',
            rightAlign: false,
            min: -90,
            max: 90,
            autoUnmask: true,
            allowMinus: true
        }).mask('#latitude');
        
        // Longitude mask - between -180 and 180, up to 8 decimal places
        Inputmask({
            alias: 'decimal',
            groupSeparator: '',
            digits: 8,
            digitsOptional: true,
            placeholder: '0',
            rightAlign: false,
            min: -180,
            max: 180,
            autoUnmask: true,
            allowMinus: true
        }).mask('#longitude');
        
        console.log('Input masks initialized');
    } else {
        console.error('Inputmask library not loaded');
    }
    
    // Wait for global Select2 to initialize, then customize multi-select
    setTimeout(function() {
        // Reinitialize multi-select with custom options
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-multiple').each(function() {
                // Only destroy if already initialized
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            
            // Now initialize with custom options
            $('.select2-multiple').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Select amenities',
                allowClear: true,
                closeOnSelect: false
            });
            console.log('Multi-select initialized with custom options');
        }
    }, 400); // Wait for global init + 200ms
    
    // Image upload functionality
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    // Click to upload
    uploadArea.addEventListener('click', () => {
        imageInput.click();
    });
    
    // Drag and drop events
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });
    
    // File input change
    imageInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFiles(files);
    });
    
    function handleFiles(files) {
        // Filter for image files only
        const imageFiles = files.filter(file => file.type.match('image.*'));
        
        // Check total limit (existing + new - removed)
        const totalImages = (existingImageCount - imagesToRemove.length) + selectedFiles.length + imageFiles.length;
        if (totalImages > 10) {
            alert('Maximum 10 images allowed. You currently have ' + (existingImageCount - imagesToRemove.length) + ' existing images.');
            return;
        }
        
        // Check file size (5MB max)
        const oversizedFiles = imageFiles.filter(file => file.size > 5 * 1024 * 1024);
        if (oversizedFiles.length > 0) {
            alert('Some files are larger than 5MB and will be skipped');
            imageFiles = imageFiles.filter(file => file.size <= 5 * 1024 * 1024);
        }
        
        imageFiles.forEach(file => {
            selectedFiles.push(file);
            displayImagePreview(file);
        });
        
        updateFileInput();
    }
    
    function displayImagePreview(file) {
        const reader = new FileReader();
        const index = selectedFiles.length - 1;
        
        reader.onload = (e) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'image-preview-item';
            previewItem.dataset.index = index;
            
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            previewContainer.appendChild(previewItem);
        };
        
        reader.readAsDataURL(file);
    }
    
    // Function to remove new images (not yet uploaded)
    window.removeImage = function(index) {
        // Remove from array
        selectedFiles.splice(index, 1);
        
        // Remove preview (only from new images container)
        const newPreviewItems = document.querySelectorAll('#imagePreviewContainer .image-preview-item');
        if (newPreviewItems[index]) {
            newPreviewItems[index].remove();
        }
        
        // Update remaining indices
        document.querySelectorAll('#imagePreviewContainer .image-preview-item').forEach((item, i) => {
            item.dataset.index = i;
            const btn = item.querySelector('.remove-image');
            btn.setAttribute('onclick', `removeImage(${i})`);
        });
        
        updateFileInput();
    };
    
    // Function to remove existing images (mark for deletion)
    window.removeExistingImage = function(imageId) {
        if (confirm('Are you sure you want to remove this image?')) {
            // Add to removal list
            imagesToRemove.push(imageId);
            
            // Update hidden input
            updateRemoveImagesInput();
            
            // Hide the image visually
            const imageElement = document.querySelector(`.existing-image[data-image-id="${imageId}"]`);
            if (imageElement) {
                imageElement.style.opacity = '0.3';
                imageElement.style.border = '2px solid #dc3545';
                const removeBtn = imageElement.querySelector('.remove-image');
                if (removeBtn) {
                    removeBtn.innerHTML = '<i class="fas fa-undo"></i>';
                    removeBtn.setAttribute('onclick', `undoRemoveExistingImage('${imageId}')`);
                    removeBtn.style.background = 'rgba(40, 167, 69, 0.9)';
                }
            }
        }
    };
    
    // Function to undo removal of existing image
    window.undoRemoveExistingImage = function(imageId) {
        // Remove from removal list
        imagesToRemove = imagesToRemove.filter(id => id !== imageId);
        
        // Update hidden input
        updateRemoveImagesInput();
        
        // Restore image visually
        const imageElement = document.querySelector(`.existing-image[data-image-id="${imageId}"]`);
        if (imageElement) {
            imageElement.style.opacity = '1';
            imageElement.style.border = '2px dashed #dee2e6';
            const removeBtn = imageElement.querySelector('.remove-image');
            if (removeBtn) {
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.setAttribute('onclick', `removeExistingImage('${imageId}')`);
                removeBtn.style.background = 'rgba(220, 53, 69, 0.9)';
            }
        }
    };
    
    function updateRemoveImagesInput() {
        document.getElementById('remove_images').value = JSON.stringify(imagesToRemove);
    }
    
    function updateFileInput() {
        // Create a new DataTransfer object to update the file input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        imageInput.files = dataTransfer.files;
    }
});

// Form submission validation - wait for DOM to be ready
$(document).ready(function() {
    const hotelForm = document.getElementById('hotelForm');
    if (hotelForm) {
        hotelForm.addEventListener('submit', function(e) {
            // Remove the file input from form if no files are selected to avoid validation error
            const imageInput = document.getElementById('imageInput');
            if (imageInput && imageInput.files.length === 0) {
                imageInput.remove();
            }
            
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating Hotel...';
            }
        });
    }
});
</script>
@endpush
