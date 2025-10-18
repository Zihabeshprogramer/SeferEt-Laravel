@extends('layouts.b2b')

@section('title', 'Edit Hotel - ' . $hotel->name)

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
                        <li class="breadcrumb-item">
                            <a href="{{ route('b2b.hotel-provider.hotels.show', $hotel) }}">{{ $hotel->name }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Hotel</h4>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($hotel->status !== 'active')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-{{ $hotel->status === 'pending' ? 'warning' : ($hotel->status === 'suspended' ? 'danger' : 'info') }}" role="alert">
                <i class="fas fa-info-circle"></i>
                <strong>Hotel Status: {{ ucfirst($hotel->status) }}</strong>
                @if($hotel->status === 'pending')
                    - Your hotel is pending admin approval.
                @elseif($hotel->status === 'suspended')
                    - Your hotel has been suspended. Contact support for assistance.
                @elseif($hotel->status === 'rejected')
                    - Your hotel was rejected. Please review the feedback and make necessary changes.
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Hotel Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Hotel Information</h5>
                    <div class="d-flex gap-2">
                        <span class="badge badge-{{ $hotel->status === 'active' ? 'success' : ($hotel->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($hotel->status) }}
                        </span>
                        <span class="badge badge-{{ $hotel->is_active ? 'success' : 'secondary' }}">
                            {{ $hotel->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('b2b.hotel-provider.hotels.update', $hotel) }}" method="POST" enctype="multipart/form-data" id="hotelForm">
                        @csrf
                        @method('PUT')
                        
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
                                           id="name" name="name" value="{{ old('name', $hotel->name) }}" required>
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
                                            <option value="{{ $value }}" {{ old('type', $hotel->type) == $value ? 'selected' : '' }}>
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
                                            <option value="{{ $i }}" {{ old('star_rating', $hotel->star_rating) == $i ? 'selected' : '' }}>
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
                                              placeholder="Describe your hotel's features, amenities, and unique selling points...">{{ old('description', $hotel->description) }}</textarea>
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
                                              id="address" name="address" rows="2" required>{{ old('address', $hotel->address) }}</textarea>
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
                                           id="city" name="city" value="{{ old('city', $hotel->city) }}" required>
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
                                        <option value="Saudi Arabia" {{ old('country', $hotel->country) == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                                        <option value="Turkey" {{ old('country', $hotel->country) == 'Turkey' ? 'selected' : '' }}>Turkey</option>
                                        <option value="UAE" {{ old('country', $hotel->country) == 'UAE' ? 'selected' : '' }}>UAE</option>
                                        <option value="Egypt" {{ old('country', $hotel->country) == 'Egypt' ? 'selected' : '' }}>Egypt</option>
                                        <option value="Jordan" {{ old('country', $hotel->country) == 'Jordan' ? 'selected' : '' }}>Jordan</option>
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
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $hotel->postal_code) }}">
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
                                           id="distance_to_haram" name="distance_to_haram" value="{{ old('distance_to_haram', $hotel->distance_to_haram) }}">
                                    @error('distance_to_haram')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="distance_to_airport" class="form-label">Distance to Airport (KM)</label>
                                    <input type="number" step="0.1" class="form-control @error('distance_to_airport') is-invalid @enderror" 
                                           id="distance_to_airport" name="distance_to_airport" value="{{ old('distance_to_airport', $hotel->distance_to_airport) }}">
                                    @error('distance_to_airport')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="number" step="0.00000001" class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" name="latitude" value="{{ old('latitude', $hotel->latitude) }}">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="number" step="0.00000001" class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" name="longitude" value="{{ old('longitude', $hotel->longitude) }}">
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
                                           id="phone" name="phone" value="{{ old('phone', $hotel->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $hotel->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website', $hotel->website) }}" 
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
                                           id="check_in_time" name="check_in_time" value="{{ old('check_in_time', $hotel->check_in_time ? $hotel->check_in_time->format('H:i') : '15:00') }}">
                                    @error('check_in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out_time" class="form-label">Check-out Time</label>
                                    <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                           id="check_out_time" name="check_out_time" value="{{ old('check_out_time', $hotel->check_out_time ? $hotel->check_out_time->format('H:i') : '11:00') }}">
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
                                        @php
                                            $availableAmenities = [
                                                'wifi' => 'Free WiFi',
                                                'parking' => 'Parking',
                                                'pool' => 'Swimming Pool',
                                                'gym' => 'Fitness Center',
                                                'spa' => 'Spa',
                                                'restaurant' => 'Restaurant',
                                                'room_service' => 'Room Service',
                                                'laundry' => 'Laundry Service',
                                                'concierge' => 'Concierge',
                                                'business_center' => 'Business Center',
                                                'conference_room' => 'Conference Room',
                                                'airport_shuttle' => 'Airport Shuttle'
                                            ];
                                            $hotelAmenities = old('amenities', $hotel->amenities ?? []);
                                        @endphp
                                        
                                        @foreach($availableAmenities as $value => $label)
                                            <option value="{{ $value }}" {{ in_array($value, $hotelAmenities) ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple amenities</small>
                                    @error('amenities')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status Control -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="section-title">Status</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                               type="checkbox" value="1" id="is_active" name="is_active" 
                                               {{ old('is_active', $hotel->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Uncheck this to temporarily disable your hotel listing
                                        </small>
                                    </div>
                                    @error('is_active')
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
                                    <a href="{{ route('b2b.hotel-provider.hotels.show', $hotel) }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Hotel
                                    </a>
                                    <div>
                                        <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-list"></i> All Hotels
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Hotel
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
// Initialize multi-select for amenities
document.addEventListener('DOMContentLoaded', function() {
    // You can initialize a better multi-select plugin here like Select2 or Choices.js
    const amenitiesSelect = document.getElementById('amenities');
    
    // Add some basic styling for better UX
    amenitiesSelect.style.minHeight = '120px';
});
</script>
@endpush
