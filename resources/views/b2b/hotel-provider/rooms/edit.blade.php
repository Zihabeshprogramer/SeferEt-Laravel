@extends('layouts.b2b')

@section('title', 'Edit Room - ' . ($room->name ?: $room->room_number))

@section('page-title', 'Edit Room')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.rooms.show', $room) }}">{{ $room->name ?: $room->room_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit mr-2"></i>Edit Room Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('b2b.hotel-provider.rooms.update', $room) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel_id" class="required">Hotel</label>
                                    <select name="hotel_id" id="hotel_id" class="form-control @error('hotel_id') is-invalid @enderror" required>
                                        <option value="">Select Hotel</option>
                                        @foreach($hotels as $hotel)
                                            <option value="{{ $hotel->id }}" 
                                                {{ old('hotel_id', $room->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                                {{ $hotel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('hotel_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name" class="required">Room Name</label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $room->name) }}" required
                                           placeholder="e.g., Presidential Suite, Deluxe Ocean View">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Each room must have a unique name within the hotel</small>
                                </div>

                                <div class="form-group">
                                    <label for="room_number" class="required">Room Number</label>
                                    <input type="text" name="room_number" id="room_number" 
                                           class="form-control @error('room_number') is-invalid @enderror" 
                                           value="{{ old('room_number', $room->formatted_room_number) }}" required>
                                    @error('room_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Room number cannot be changed to a range in edit mode</small>
                                </div>

                                <div class="form-group">
                                    <label for="category" class="required">Room Category</label>
                                    <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                        <option value="">Select Room Category</option>
                                        @foreach($roomTypeCategories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', $room->category) === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Choose the category that best describes this room's features</small>
                                </div>

                                <div class="form-group">
                                    <label for="base_price" class="required">Base Price ($)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="base_price" id="base_price" 
                                               class="form-control @error('base_price') is-invalid @enderror" 
                                               value="{{ old('base_price', $room->base_price) }}" step="0.01" min="0" required>
                                        @error('base_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Price per night in USD</small>
                                </div>
                            </div>

                            <!-- Room Specifications -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_occupancy" class="required">Maximum Occupancy</label>
                                    <input type="number" name="max_occupancy" id="max_occupancy" 
                                           class="form-control @error('max_occupancy') is-invalid @enderror" 
                                           value="{{ old('max_occupancy', $room->max_occupancy) }}" min="1" max="20" required>
                                    @error('max_occupancy')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="bed_type" class="required">Bed Type</label>
                                    <select name="bed_type" id="bed_type" class="form-control @error('bed_type') is-invalid @enderror" required>
                                        <option value="">Select Bed Type</option>
                                        @foreach(['single' => 'Single', 'double' => 'Double', 'queen' => 'Queen', 'king' => 'King', 'twin' => 'Twin', 'sofa_bed' => 'Sofa Bed', 'bunk_bed' => 'Bunk Bed'] as $value => $label)
                                            <option value="{{ $value }}" 
                                                {{ old('bed_type', $room->bed_type) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bed_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="bed_count" class="required">Number of Beds</label>
                                    <input type="number" name="bed_count" id="bed_count" 
                                           class="form-control @error('bed_count') is-invalid @enderror" 
                                           value="{{ old('bed_count', $room->bed_count) }}" min="1" max="10" required>
                                    @error('bed_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="size_sqm">Room Size (m²)</label>
                                    <input type="number" name="size_sqm" id="size_sqm" 
                                           class="form-control @error('size_sqm') is-invalid @enderror" 
                                           value="{{ old('size_sqm', $room->size_sqm) }}" min="1">
                                    @error('size_sqm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" 
                                               class="form-check-input" value="1" 
                                               {{ old('is_active', $room->is_active) ? 'checked' : '' }}>
                                        <label for="is_active" class="form-check-label">
                                            Active Room
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4" 
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Describe the room features, view, and special characteristics...">{{ old('description', $room->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Amenities -->
                        <div class="form-group">
                            <label>Room Amenities</label>
                            <div class="row">
                                @php
                                    $amenities = [
                                        'wifi' => 'Wi-Fi',
                                        'air_conditioning' => 'Air Conditioning',
                                        'heating' => 'Heating',
                                        'television' => 'Television',
                                        'cable_tv' => 'Cable TV',
                                        'satellite_tv' => 'Satellite TV',
                                        'telephone' => 'Telephone',
                                        'minibar' => 'Minibar',
                                        'refrigerator' => 'Refrigerator',
                                        'coffee_maker' => 'Coffee Maker',
                                        'safe' => 'Safe',
                                        'balcony' => 'Balcony',
                                        'terrace' => 'Terrace',
                                        'sea_view' => 'Sea View',
                                        'mountain_view' => 'Mountain View',
                                        'city_view' => 'City View',
                                        'garden_view' => 'Garden View',
                                        'private_bathroom' => 'Private Bathroom',
                                        'shared_bathroom' => 'Shared Bathroom',
                                        'bathtub' => 'Bathtub',
                                        'shower' => 'Shower',
                                        'hairdryer' => 'Hair Dryer',
                                        'toiletries' => 'Toiletries',
                                        'towels' => 'Towels',
                                        'iron' => 'Iron',
                                        'ironing_board' => 'Ironing Board',
                                        'desk' => 'Work Desk',
                                        'chair' => 'Chair',
                                        'wardrobe' => 'Wardrobe',
                                        'slippers' => 'Slippers',
                                        'room_service' => 'Room Service'
                                    ];
                                    $selectedAmenities = old('amenities', $room->amenities ?? []);
                                @endphp
                                
                                @foreach($amenities as $key => $label)
                                    <div class="col-md-4 col-lg-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="amenities[]" value="{{ $key }}" 
                                                   id="amenity_{{ $key }}" class="form-check-input"
                                                   {{ in_array($key, $selectedAmenities) ? 'checked' : '' }}>
                                            <label for="amenity_{{ $key }}" class="form-check-label">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Current Images -->
                        @if($room->images && count($room->images) > 0)
                            <div class="form-group">
                                <label>Current Images</label>
                                <div class="row" id="current-images">
                                    @foreach($room->images as $index => $image)
                                        <div class="col-md-3 mb-3" data-image-index="{{ $index }}">
                                            <div class="position-relative">
                                                <img src="{{ Storage::url($image) }}" 
                                                     class="img-fluid rounded" 
                                                     style="height: 150px; object-fit: cover; width: 100%;"
                                                     alt="Room Image">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute remove-image-btn" 
                                                        style="top: 5px; right: 5px;" data-image-index="{{ $index }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <input type="hidden" name="existing_images[]" value="{{ $image }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- New Images -->
                        <div class="form-group">
                            <label for="images">Add New Images</label>
                            <input type="file" name="images[]" id="images" 
                                   class="form-control-file @error('images') is-invalid @enderror" 
                                   multiple accept="image/*">
                            <small class="form-text text-muted">
                                Select additional images (JPG, PNG, GIF). Maximum 5MB per image.
                            </small>
                            @error('images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('images.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update Room
                            </button>
                            <a href="{{ route('b2b.hotel-provider.rooms.show', $room) }}" class="btn btn-secondary">
                                <i class="fas fa-eye mr-2"></i>View Room
                            </a>
                            <a href="{{ route('b2b.hotel-provider.rooms.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list mr-2"></i>Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Room Preview & Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye mr-2"></i>Room Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h6>{{ $room->name ?: 'Room ' . $room->room_number }}</h6>
                        <p class="text-muted">{{ $room->hotel->name }}</p>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-success mb-1">${{ number_format($room->base_price, 2) }}</div>
                            <small class="text-muted">Current Price</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-info mb-1">{{ $room->max_occupancy }}</div>
                            <small class="text-muted">Max Guests</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <strong>Status:</strong>
                        @if($room->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </div>

                    <div class="mb-2">
                        <strong>Availability:</strong>
                        @if($room->is_available)
                            <span class="badge badge-success">Available</span>
                        @else
                            <span class="badge badge-danger">Occupied</span>
                        @endif
                    </div>

                    <div class="mb-2">
                        <strong>Bed:</strong> {{ ucwords(str_replace('_', ' ', $room->bed_type)) }} ({{ $room->bed_count }})
                    </div>

                    @if($room->category)
                        <div class="mb-2">
                            <strong>Category:</strong> {{ $room->category_name }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Edit Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb mr-1"></i> Best Practices</h6>
                        <ul class="mb-0 pl-3">
                            <li>Keep room information accurate and up-to-date</li>
                            <li>Update pricing based on market conditions</li>
                            <li>Maintain high-quality room images</li>
                            <li>Review amenities regularly</li>
                        </ul>
                    </div>

                    <h6>Change Impact</h6>
                    <ul class="text-sm text-muted">
                        <li>Price changes affect future bookings</li>
                        <li>Occupancy changes need booking review</li>
                        <li>Status changes affect availability</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock mr-2"></i>Last Updated
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">{{ $room->updated_at->format('M d, Y') }}</p>
                    <small class="text-muted">{{ $room->updated_at->format('H:i') }}</small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Remove existing image functionality
            $('.remove-image-btn').on('click', function() {
                const imageIndex = $(this).data('image-index');
                const imageContainer = $(this).closest('[data-image-index="' + imageIndex + '"]');
                
                if (confirm('Are you sure you want to remove this image?')) {
                    imageContainer.remove();
                }
            });

            // Image validation
            $('#images').on('change', function() {
                const files = this.files;
                const maxFiles = 10;
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (files.length > maxFiles) {
                    alert(`Please select no more than ${maxFiles} images.`);
                    this.value = '';
                    return;
                }
                
                for (let i = 0; i < files.length; i++) {
                    if (files[i].size > maxSize) {
                        alert(`Image "${files[i].name}" is too large. Please select images under 5MB.`);
                        this.value = '';
                        return;
                    }
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;
                const requiredFields = ['hotel_id', 'name', 'room_number', 'category', 'base_price', 'max_occupancy', 'bed_type', 'bed_count'];
                
                requiredFields.forEach(function(field) {
                    const input = $(`#${field}`);
                    if (!input.val()) {
                        input.addClass('is-invalid');
                        isValid = false;
                    } else {
                        input.removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    toastr.error('Please fill in all required fields correctly.');
                }
            });

            // Clear validation on input
            $('.form-control').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endsection

@section('styles')
    <style>
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-check {
            min-height: 1.5rem;
        }
        
        .alert ul {
            margin-bottom: 0;
        }
        
        .text-sm {
            font-size: 0.875rem;
        }

        .remove-image-btn {
            opacity: 0.8;
        }

        .remove-image-btn:hover {
            opacity: 1;
        }
    </style>
@endsection
