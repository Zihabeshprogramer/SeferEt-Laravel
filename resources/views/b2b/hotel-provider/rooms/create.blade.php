@extends('layouts.b2b')

@section('title', 'Add New Room')

@section('page-title', 'Add New Room')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item active">Add New Room</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bed mr-2"></i>Room Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('b2b.hotel-provider.rooms.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel_id" class="required">Hotel</label>
                                    <select name="hotel_id" id="hotel_id" class="form-control @error('hotel_id') is-invalid @enderror" required>
                                        <option value="">Select Hotel</option>
                                        @foreach($hotels as $hotel)
                                            <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
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
                                           value="{{ old('name') }}" required
                                           placeholder="e.g., Presidential Suite, Deluxe Ocean View">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Each room must have a unique name within the hotel</small>
                                </div>

                                <div class="form-group">
                                    <label for="room_number_input" class="required">Room Number</label>
                                    <input type="text" name="room_number_input" id="room_number_input" 
                                           class="form-control @error('room_number_input') is-invalid @enderror" 
                                           value="{{ old('room_number_input') }}" required
                                           placeholder="105 or 125-130">
                                    @error('room_number_input')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Enter a single room number (e.g., "105") or a range (e.g., "125-130")
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="category" class="required">Room Category</label>
                                    <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                        <option value="">Select Room Category</option>
                                        @foreach($roomTypeCategories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
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
                                               value="{{ old('base_price') }}" step="0.01" min="0" required>
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
                                           value="{{ old('max_occupancy') }}" min="1" max="20" required>
                                    @error('max_occupancy')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="bed_type" class="required">Bed Type</label>
                                    <select name="bed_type" id="bed_type" class="form-control @error('bed_type') is-invalid @enderror" required>
                                        <option value="">Select Bed Type</option>
                                        <option value="single" {{ old('bed_type') == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="double" {{ old('bed_type') == 'double' ? 'selected' : '' }}>Double</option>
                                        <option value="queen" {{ old('bed_type') == 'queen' ? 'selected' : '' }}>Queen</option>
                                        <option value="king" {{ old('bed_type') == 'king' ? 'selected' : '' }}>King</option>
                                        <option value="twin" {{ old('bed_type') == 'twin' ? 'selected' : '' }}>Twin</option>
                                        <option value="sofa_bed" {{ old('bed_type') == 'sofa_bed' ? 'selected' : '' }}>Sofa Bed</option>
                                        <option value="bunk_bed" {{ old('bed_type') == 'bunk_bed' ? 'selected' : '' }}>Bunk Bed</option>
                                    </select>
                                    @error('bed_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="bed_count" class="required">Number of Beds</label>
                                    <input type="number" name="bed_count" id="bed_count" 
                                           class="form-control @error('bed_count') is-invalid @enderror" 
                                           value="{{ old('bed_count') }}" min="1" max="10" required>
                                    @error('bed_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="size_sqm">Room Size (m²)</label>
                                    <input type="number" name="size_sqm" id="size_sqm" 
                                           class="form-control @error('size_sqm') is-invalid @enderror" 
                                           value="{{ old('size_sqm') }}" min="1">
                                    @error('size_sqm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" 
                                               class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
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
                                      placeholder="Describe the room features, view, and special characteristics...">{{ old('description') }}</textarea>
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
                                @endphp
                                
                                @foreach($amenities as $key => $label)
                                    <div class="col-md-4 col-lg-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="amenities[]" value="{{ $key }}" 
                                                   id="amenity_{{ $key }}" class="form-check-input"
                                                   {{ in_array($key, old('amenities', [])) ? 'checked' : '' }}>
                                            <label for="amenity_{{ $key }}" class="form-check-label">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Room Images -->
                        <div class="form-group">
                            <label for="images">Room Images</label>
                            <input type="file" name="images[]" id="images" 
                                   class="form-control-file @error('images') is-invalid @enderror" 
                                   multiple accept="image/*">
                            <small class="form-text text-muted">
                                Select multiple images (JPG, PNG, GIF). Maximum 5MB per image.
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
                                <i class="fas fa-save mr-2"></i>Create Room
                            </button>
                            <a href="{{ route('b2b.hotel-provider.rooms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help & Guidelines -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Room Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb mr-1"></i> Tips for Success</h6>
                        <ul class="mb-0 pl-3">
                            <li>Give each room a unique, descriptive name</li>
                            <li>Use room number ranges for multiple similar rooms (e.g., 125-130)</li>
                            <li>Choose feature-based room categories for better marketing</li>
                            <li>Set competitive pricing based on market rates</li>
                            <li>Include high-quality room images from multiple angles</li>
                            <li>Select all applicable amenities accurately</li>
                        </ul>
                    </div>

                    <h6>Required Fields</h6>
                    <ul class="text-sm text-muted">
                        <li>Hotel selection</li>
                        <li><strong>Room name</strong> (must be unique per hotel)</li>
                        <li>Room number or range</li>
                        <li>Base price per night</li>
                        <li>Maximum occupancy</li>
                        <li>Bed type and count</li>
                    </ul>
                    
                    <h6 class="mt-3">Room Number Examples</h6>
                    <ul class="text-sm text-muted">
                        <li><strong>Single:</strong> "105", "201", "1001"</li>
                        <li><strong>Range:</strong> "125-130", "201-205"</li>
                        <li><strong>Range creates:</strong> Individual rooms for each number</li>
                    </ul>

                    <h6 class="mt-3">Image Requirements</h6>
                    <ul class="text-sm text-muted">
                        <li>Formats: JPG, PNG, GIF</li>
                        <li>Maximum: 5MB per image</li>
                        <li>Recommended: 1200x800 pixels</li>
                        <li>Multiple angles preferred</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>Pricing Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <small>
                            <strong>Dynamic Pricing:</strong> You can adjust prices later based on demand, seasonality, and market conditions through the rates management system.
                        </small>
                    </div>
                    
                    <h6>Consider These Factors:</h6>
                    <ul class="text-sm text-muted">
                        <li>Local market rates</li>
                        <li>Room size and amenities</li>
                        <li>View and location</li>
                        <li>Seasonal demand</li>
                        <li>Special events</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Image preview functionality
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

            // Room number input validation
            $('#room_number_input').on('input', function() {
                const input = $(this).val().trim();
                const isValid = /^\d+(-\d+)?$/.test(input);
                
                if (input && !isValid) {
                    $(this).addClass('is-invalid');
                    $('#room-number-feedback').text('Enter a single number (e.g., "105") or range (e.g., "125-130")');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#room-number-feedback').text('');
                }
            });
            
            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;
                const requiredFields = ['hotel_id', 'name', 'room_number_input', 'category', 'base_price', 'max_occupancy', 'bed_type', 'bed_count'];
                
                requiredFields.forEach(function(field) {
                    const input = $(`#${field}`);
                    if (!input.val()) {
                        input.addClass('is-invalid');
                        isValid = false;
                    } else {
                        input.removeClass('is-invalid');
                    }
                });
                
                // Validate room number format
                const roomNumberInput = $('#room_number_input').val().trim();
                if (roomNumberInput && !/^\d+(-\d+)?$/.test(roomNumberInput)) {
                    $('#room_number_input').addClass('is-invalid');
                    isValid = false;
                }
                
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
    </style>
@endsection
