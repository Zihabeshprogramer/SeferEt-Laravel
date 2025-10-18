@extends('layouts.b2b')

@section('title', 'Add New Hotel')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('b2b.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('b2b.hotel-provider.hotels.index') }}">My Hotels</a>
                        </li>
                        <li class="breadcrumb-item active">Add New Hotel</li>
                    </ol>
                </div>
                <h4 class="page-title">Add New Hotel</h4>
            </div>
        </div>
    </div>

    <!-- Hotel Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hotel Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('b2b.hotel-provider.hotels.store') }}" method="POST" enctype="multipart/form-data" id="hotelForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="section-title">Basic Information</h6>
                                <hr>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Hotel Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Hotel Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        @foreach(\App\Models\Hotel::TYPES as $value => $label)
                                            <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="star_rating" class="form-label">Star Rating <span class="text-danger">*</span></label>
                                    <select class="form-select @error('star_rating') is-invalid @enderror" id="star_rating" name="star_rating" required>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ old('star_rating', 3) == $i ? 'selected' : '' }}>
                                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('star_rating')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="Describe your hotel's features, amenities, and unique selling points...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="section-title">Location Information</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                    <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="Saudi Arabia" {{ old('country') == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                                        <option value="Turkey" {{ old('country') == 'Turkey' ? 'selected' : '' }}>Turkey</option>
                                        <option value="UAE" {{ old('country') == 'UAE' ? 'selected' : '' }}>UAE</option>
                                        <option value="Egypt" {{ old('country') == 'Egypt' ? 'selected' : '' }}>Egypt</option>
                                        <option value="Jordan" {{ old('country') == 'Jordan' ? 'selected' : '' }}>Jordan</option>
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="distance_to_haram" class="form-label">Distance to Haram (KM)</label>
                                    <input type="number" step="0.1" class="form-control @error('distance_to_haram') is-invalid @enderror" 
                                           id="distance_to_haram" name="distance_to_haram" value="{{ old('distance_to_haram') }}">
                                    @error('distance_to_haram')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="distance_to_airport" class="form-label">Distance to Airport (KM)</label>
                                    <input type="number" step="0.1" class="form-control @error('distance_to_airport') is-invalid @enderror" 
                                           id="distance_to_airport" name="distance_to_airport" value="{{ old('distance_to_airport') }}">
                                    @error('distance_to_airport')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="number" step="0.00000001" class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" name="latitude" value="{{ old('latitude') }}">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="number" step="0.00000001" class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" name="longitude" value="{{ old('longitude') }}">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="section-title">Contact Information</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website') }}" 
                                           placeholder="https://www.example.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Hotel Policies -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="section-title">Hotel Policies & Information</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in_time" class="form-label">Check-in Time</label>
                                    <input type="time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                           id="check_in_time" name="check_in_time" value="{{ old('check_in_time', '15:00') }}">
                                    @error('check_in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out_time" class="form-label">Check-out Time</label>
                                    <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                           id="check_out_time" name="check_out_time" value="{{ old('check_out_time', '11:00') }}">
                                    @error('check_out_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="amenities" class="form-label">Amenities</label>
                                    <select class="form-select @error('amenities') is-invalid @enderror" 
                                            id="amenities" name="amenities[]" multiple>
                                        <option value="wifi">Free WiFi</option>
                                        <option value="parking">Parking</option>
                                        <option value="pool">Swimming Pool</option>
                                        <option value="gym">Fitness Center</option>
                                        <option value="spa">Spa</option>
                                        <option value="restaurant">Restaurant</option>
                                        <option value="room_service">Room Service</option>
                                        <option value="laundry">Laundry Service</option>
                                        <option value="concierge">Concierge</option>
                                        <option value="business_center">Business Center</option>
                                        <option value="conference_room">Conference Room</option>
                                        <option value="airport_shuttle">Airport Shuttle</option>
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple amenities</small>
                                    @error('amenities')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Hotels
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Hotel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetForm() {
    if (confirm('Are you sure you want to reset all form data?')) {
        document.getElementById('hotelForm').reset();
    }
}

// Initialize multi-select for amenities
document.addEventListener('DOMContentLoaded', function() {
    // You can initialize a better multi-select plugin here like Select2 or Choices.js
    const amenitiesSelect = document.getElementById('amenities');
    
    // Add some basic styling for better UX
    amenitiesSelect.style.minHeight = '120px';
});
</script>
@endpush
