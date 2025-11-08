@push('css')
<style>
/* Package Image Upload Styles */
.image-upload-container { margin-bottom: 2rem; }
.dropzone-wrapper { border: 2px dashed #dee2e6; border-radius: 0.5rem; background-color: #f8f9fa; transition: all 0.3s ease; cursor: pointer; min-height: 200px; display: flex; align-items: center; justify-content: center; }
.dropzone-wrapper:hover { border-color: #0d6efd; background-color: #e7f1ff; }
.dropzone-wrapper.drag-over { border-color: #0d6efd; background-color: #e7f1ff; border-style: solid; transform: scale(1.02); }
.dropzone-message { text-align: center; width: 100%; }
.dropzone-message .fa-cloud-upload-alt { color: #6c757d; margin-bottom: 1rem; }
.dropzone-wrapper:hover .fa-cloud-upload-alt { color: #0d6efd; }
.upload-progress { margin-top: 1rem; display: none; }
.uploaded-images-gallery { margin-top: 2rem; }
.image-item { transition: all 0.3s ease; }
.image-card { border-radius: 0.5rem; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; cursor: pointer; }
.image-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
.image-card img { transition: all 0.3s ease; }
.image-card:hover img { transform: scale(1.05); }
.image-controls { opacity: 0; transition: all 0.3s ease; }
.image-item:hover .image-controls { opacity: 1; }
.image-controls .btn { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); border: none; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
.image-controls .btn:hover { transform: scale(1.1); }
.drag-handle { opacity: 0; transition: all 0.3s ease; cursor: grab; }
.image-item:hover .drag-handle { opacity: 1; }
.drag-handle:active { cursor: grabbing; }
.image-info { backdrop-filter: blur(5px); }
.empty-images-state { border: 2px dashed #dee2e6; border-radius: 0.5rem; background-color: #f8f9fa; margin: 2rem 0; }
.empty-images-state .fa-images { color: #6c757d; }
.sortable-ghost { opacity: 0.4; transform: rotate(5deg); }
@media (max-width: 768px) { .image-card img { height: 150px !important; } .image-controls { opacity: 1; } .drag-handle { opacity: 1; } .image-controls .btn { width: 28px; height: 28px; font-size: 0.7rem; } }
@media (max-width: 576px) { .dropzone-wrapper { min-height: 150px; } .dropzone-message .fa-cloud-upload-alt { font-size: 2rem !important; } .image-card img { height: 120px !important; } }
.image-item { opacity: 1; transform: scale(1); }
.image-item.uploading { opacity: 0.6; transform: scale(0.95); }
.image-item.deleting { opacity: 0; transform: scale(0.8); }
.image-upload-container .alert { margin-bottom: 1rem; }
.image-upload-container .alert ul { margin-bottom: 0; padding-left: 1.5rem; }
.progress { height: 8px; border-radius: 4px; overflow: hidden; }
.progress-bar { transition: width 0.3s ease; }
.form-wizard-section .image-upload-container { background: rgba(248, 249, 250, 0.5); border-radius: 0.5rem; padding: 1.5rem; border: 1px solid #e9ecef; }
.badge { font-weight: 500; letter-spacing: 0.025em; }
.badge.bg-warning { color: #000 !important; }
.btn-outline-primary { border-width: 2px; font-weight: 500; }
.btn-outline-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3); }

/* Dropdown suggestions styling */
.destination-input-container, .departure-input-container { position: relative; }
.dropdown-suggestions { position: absolute; z-index: 1000; width: 100%; background: white; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 0.375rem 0.375rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-height: 200px; overflow-y: auto; display: none; }
.suggestion-item { padding: 0.5rem 0.75rem; cursor: pointer; transition: background-color 0.2s; }
.suggestion-item:hover { background-color: #f8f9fa; }
.destination-tag, .departure-city-tag { font-size: 0.9em; padding: 0.4em 0.6em; }
</style>
@endpush

<!-- Hidden field for package draft ID -->
@if(isset($draft) && $draft && isset($draft->id))
<input type="hidden" name="package_draft_id" value="{{ $draft->id }}" id="packageDraftIdField">
@endif

<!-- Step 1: Basic Package Information -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle text-primary me-2"></i>
            Basic Package Information
        </h5>
        <p class="text-muted mb-0 small">Provide essential details about your travel package</p>
    </div>
    
    <!-- Step Description -->
    <div class="step-description mx-4 mt-4">
        <h6><i class="fas fa-lightbulb me-1"></i> Getting Started</h6>
        <p class="mb-0 small">Fill in the basic information about your travel package. This information will be used to create the package listing and help customers understand what you're offering. All fields marked with <span class="text-danger">*</span> are required.</p>
    </div>
    
    <div class="card-body p-4">
        <!-- Essential Information Section -->
        <div class="form-wizard-section">
            <h6><i class="fas fa-edit me-2"></i> Essential Information</h6>
            <p class="small text-muted mb-3">Basic details that will appear in your package listing</p>
        
        <div class="row">
            <!-- Package Name -->
            <div class="col-md-8 mb-3">
                <label for="name" class="form-label fw-bold">
                    Package Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control form-control-lg" id="name" name="name" 
                       placeholder="e.g., Istanbul & Cappadocia Adventure - 7 Days" 
                       value="{{ old('name', $draft->name ?? '') }}" required>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Choose an engaging name that highlights key destinations</small>
            </div>

            <!-- Package Type -->
            <div class="col-md-4 mb-3">
                <label for="package_type" class="form-label fw-bold">
                    Package Type <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-lg select2-enhanced" id="package_type" name="package_type" 
                        data-placeholder="Choose package type..." required>
                    <option value=""></option>
                    @foreach($packageTypes ?? [] as $value => $label)
                        <option value="{{ $value }}" {{ old('package_type', $draft->package_type ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Select the category that best describes your package</small>
            </div>
        </div>

        <div class="row">
            <!-- Short Description -->
            <div class="col-12 mb-3">
                <label for="short_description" class="form-label fw-bold">
                    Short Description <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="short_description" name="short_description" rows="3" 
                          placeholder="Brief, compelling description that will appear in search results and package listings..." 
                          maxlength="300" required>{{ old('short_description', $draft->short_description ?? '') }}</textarea>
                <div class="d-flex justify-content-between">
                    <div class="invalid-feedback"></div>
                    <small class="form-text text-muted">
                        <span id="shortDescCounter">0</span>/300 characters
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
                <textarea class="form-control summernote-editor" id="detailed_description" name="detailed_description" 
                          placeholder="Write a comprehensive description including highlights, experiences, and what makes this package unique...">{{ old('detailed_description', $draft->detailed_description ?? '') }}</textarea>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Use the rich text editor to format your package description with images, links, and styling</small>
            </div>
        </div>

        <div class="row">
            <!-- Date Range Picker -->
            <div class="col-md-6 mb-3">
                <label for="date_range" class="form-label fw-bold">
                    <i class="fas fa-calendar-alt me-1"></i> Trip Dates <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="date_range" name="date_range" 
                       placeholder="Select start and end dates..." 
                       value="{{ old('date_range', $draft->date_range ?? '') }}" required>
                <!-- Hidden inputs for backend -->
                <input type="hidden" id="start_date" name="start_date" value="{{ old('start_date', $draft->start_date ?? '') }}">
                <input type="hidden" id="end_date" name="end_date" value="{{ old('end_date', $draft->end_date ?? '') }}">
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Select the start and end dates for this travel package</small>
            </div>
            
            <!-- Auto-calculated Duration -->
            <div class="col-md-3 mb-3">
                <label for="duration_days" class="form-label fw-bold">
                    Duration (Days)
                </label>
                <input type="number" class="form-control" id="duration_days" name="duration_days" 
                       min="1" max="365" readonly 
                       value="{{ old('duration_days', $draft->duration_days ?? '') }}">
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Auto-calculated from dates</small>
            </div>

            <!-- Auto-calculated Nights -->
            <div class="col-md-3 mb-3">
                <label for="duration_nights" class="form-label fw-bold">
                    Nights
                </label>
                <input type="number" class="form-control" id="duration_nights" name="duration_nights" 
                       min="0" max="364" readonly 
                       value="{{ old('duration_nights', $draft->duration_nights ?? '') }}">
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Auto-calculated from dates</small>
            </div>

            <!-- Max Participants -->
            <div class="col-md-3 mb-3">
                <label for="max_participants" class="form-label fw-bold">
                    Max Participants <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="max_participants" name="max_participants" 
                       min="1" max="100" placeholder="20" 
                       value="{{ old('max_participants', $draft->max_participants ?? '') }}" required>
                <div class="invalid-feedback"></div>
            </div>

            <!-- Min Participants -->
            <div class="col-md-3 mb-3">
                <label for="min_participants" class="form-label fw-bold">
                    Min Participants
                </label>
                <input type="number" class="form-control" id="min_participants" name="min_participants" 
                       min="1" placeholder="2" 
                       value="{{ old('min_participants', $draft->min_participants ?? '') }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="row">
            <!-- Departure Cities -->
            <div class="col-md-6 mb-3">
                <label for="departure_cities" class="form-label fw-bold">
                    <i class="fas fa-plane-departure me-1"></i> Departure Cities <span class="text-danger">*</span>
                </label>
                <div class="departure-input-container">
                    <input type="text" class="form-control" id="departureCitiesInput" 
                           placeholder="Type departure city name and press Enter..." autocomplete="off">
                    <div id="departureCitySuggestions" class="dropdown-suggestions"></div>
                </div>
                <div class="selected-departure-cities mt-2" id="selectedDepartureCities">
                    <!-- Selected departure cities will appear here as tags -->
                    @php
                        $draftDepartureCities = is_array($draft->departure_cities ?? null) ? $draft->departure_cities : [];
                        $oldDepartureCities = old('departure_cities', $draftDepartureCities);
                        $departureCities = is_array($oldDepartureCities) ? $oldDepartureCities : [];
                    @endphp
                    @if(count($departureCities) > 0)
                        @foreach($departureCities as $city)
                            <span class="badge bg-info me-1 mb-1 departure-city-tag">
                                {{ $city }}
                                <button type="button" class="btn-close btn-close-white btn-sm ms-1" 
                                        onclick="removeDepartureCity('{{ $city }}')"></button>
                                <input type="hidden" name="departure_cities[]" value="{{ $city }}">
                            </span>
                        @endforeach
                    @endif
                </div>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Type city names where the trip departs from and press Enter. <strong>At least one departure city is required.</strong></small>
            </div>

            <!-- Destinations -->
            <div class="col-md-6 mb-3">
                <label for="destinations" class="form-label fw-bold">
                    Primary Destinations <span class="text-danger">*</span>
                </label>
                <div class="destination-input-container">
                    <input type="text" class="form-control" id="destinationsInput" 
                           placeholder="Type destination name and press Enter..." autocomplete="off">
                    <div id="destinationSuggestions" class="dropdown-suggestions"></div>
                </div>
                <div class="selected-destinations mt-2" id="selectedDestinations">
                    <!-- Selected destinations will appear here as tags -->
                    @php
                        $draftDestinations = is_array($draft->destinations ?? null) ? $draft->destinations : [];
                        $oldDestinations = old('destinations', $draftDestinations);
                        $destinations = is_array($oldDestinations) ? $oldDestinations : [];
                    @endphp
                    @if(count($destinations) > 0)
                        @foreach($destinations as $destination)
                            <span class="badge bg-primary me-1 mb-1 destination-tag">
                                {{ $destination }}
                                <button type="button" class="btn-close btn-close-white btn-sm ms-1" 
                                        onclick="removeDestination('{{ $destination }}')"></button>
                                <input type="hidden" name="destinations[]" value="{{ $destination }}">
                            </span>
                        @endforeach
                    @endif
                </div>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Type destination names and press Enter to add them, or click on suggestions. <strong>At least one destination is required.</strong></small>
            </div>
        </div>

        <div class="row">
            <!-- Categories -->
            <div class="col-md-12 mb-3">
                <label for="categories" class="form-label fw-bold">
                    Categories
                </label>
                <div class="category-checkboxes">
                    @php
                    $categories = [
                        'historical' => 'Historical Sites',
                        'nature' => 'Nature & Wildlife',
                        'beaches' => 'Beaches & Coastal',
                        'mountains' => 'Mountains & Hiking',
                        'cities' => 'City Tours',
                        'food' => 'Culinary Experience',
                        'shopping' => 'Shopping',
                        'nightlife' => 'Nightlife',
                        'religious' => 'Religious Sites',
                        'festivals' => 'Festivals & Events'
                    ];
                    $draftCategories = is_array($draft->categories ?? null) ? $draft->categories : [];
                    $selectedCategories = old('categories', $draftCategories);
                    $selectedCategories = is_array($selectedCategories) ? $selectedCategories : [];
                    @endphp
                    
                    @foreach($categories as $value => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="cat_{{ $value }}" 
                               name="categories[]" value="{{ $value }}"
                               {{ in_array($value, $selectedCategories) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="cat_{{ $value }}">
                            {{ $label }}
                        </label>
                    </div>
                    @endforeach
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>
        </div> <!-- End Essential Information Section -->
        
        <!-- Additional Details Section -->
        <div class="form-wizard-section">
            <h6><i class="fas fa-cog me-2"></i> Additional Details</h6>
            <p class="small text-muted mb-3">Optional information to help customers understand your package better</p>

        <div class="row">
            <!-- Difficulty Level -->
            <div class="col-md-4 mb-3">
                <label for="difficulty_level" class="form-label fw-bold">
                    <i class="fas fa-mountain me-1"></i> Difficulty Level
                </label>
                <select class="form-select select2-enhanced" id="difficulty_level" name="difficulty_level"
                        data-placeholder="Select difficulty level...">
                    <option value=""></option>
                    <option value="easy" {{ old('difficulty_level', $draft->difficulty_level ?? '') == 'easy' ? 'selected' : '' }}>🟢 Easy - Suitable for all ages</option>
                    <option value="moderate" {{ old('difficulty_level', $draft->difficulty_level ?? '') == 'moderate' ? 'selected' : '' }}>🟡 Moderate - Some walking required</option>
                    <option value="challenging" {{ old('difficulty_level', $draft->difficulty_level ?? '') == 'challenging' ? 'selected' : '' }}>🟠 Challenging - Good fitness required</option>
                    <option value="expert" {{ old('difficulty_level', $draft->difficulty_level ?? '') == 'expert' ? 'selected' : '' }}>🔴 Expert - High fitness level needed</option>
                </select>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Help customers choose the right package for their fitness level</small>
            </div>

            <!-- Currency -->
            <div class="col-md-4 mb-3">
                <label for="currency" class="form-label fw-bold">
                    <i class="fas fa-coins me-1"></i> Pricing Currency <span class="text-danger">*</span>
                </label>
                <select class="form-select select2-enhanced" id="currency" name="currency" 
                        data-placeholder="Select currency..." required>
                    <option value="TRY" {{ old('currency', $draft->currency ?? 'TRY') == 'TRY' ? 'selected' : '' }}>🇹🇷 Turkish Lira (₺)</option>
                    <option value="USD" {{ old('currency', $draft->currency ?? '') == 'USD' ? 'selected' : '' }}>🇺🇸 US Dollar ($)</option>
                    <option value="EUR" {{ old('currency', $draft->currency ?? '') == 'EUR' ? 'selected' : '' }}>🇪🇺 Euro (€)</option>
                    <option value="GBP" {{ old('currency', $draft->currency ?? '') == 'GBP' ? 'selected' : '' }}>🇬🇧 British Pound (£)</option>
                </select>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Choose your base pricing currency</small>
            </div>

            <!-- Status -->
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label fw-bold">
                    <i class="fas fa-toggle-on me-1"></i> Initial Status
                </label>
                <select class="form-select select2-enhanced" id="status" name="status"
                        data-placeholder="Choose initial status...">
                    <option value="draft" {{ old('status', $draft->status ?? 'draft') == 'draft' ? 'selected' : '' }}>📝 Draft - Keep as draft for now</option>
                    <option value="active" {{ old('status', $draft->status ?? '') == 'active' ? 'selected' : '' }}>🟢 Active - Publish immediately</option>
                </select>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted">Drafts are private, active packages are visible to customers</small>
            </div>
        </div>

        <!-- Special Features -->
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label fw-bold">Special Features & Inclusions</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includes_meals" name="includes_meals" value="1" 
                                   {{ old('includes_meals', $draft->includes_meals ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="includes_meals">
                                <i class="fas fa-utensils text-primary me-1"></i> Includes Meals
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includes_accommodation" name="includes_accommodation" value="1" 
                                   {{ old('includes_accommodation', $draft->includes_accommodation ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="includes_accommodation">
                                <i class="fas fa-bed text-primary me-1"></i> Includes Accommodation
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includes_transport" name="includes_transport" value="1" 
                                   {{ old('includes_transport', $draft->includes_transport ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="includes_transport">
                                <i class="fas fa-bus text-primary me-1"></i> Includes Transportation
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includes_guide" name="includes_guide" value="1" 
                                   {{ old('includes_guide', $draft->includes_guide ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="includes_guide">
                                <i class="fas fa-user-tie text-primary me-1"></i> Includes Professional Guide
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includes_flights" name="includes_flights" value="1" 
                                   {{ old('includes_flights', $draft->includes_flights ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="includes_flights">
                                <i class="fas fa-plane text-primary me-1"></i> Includes Flights
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includes_activities" name="includes_activities" value="1" 
                                   {{ old('includes_activities', $draft->includes_activities ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="includes_activities">
                                <i class="fas fa-map-marked-alt text-primary me-1"></i> Includes Activities
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="free_cancellation" name="free_cancellation" value="1" 
                                   {{ old('free_cancellation', $draft->free_cancellation ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="free_cancellation">
                                <i class="fas fa-undo text-success me-1"></i> Free Cancellation
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="instant_confirmation" name="instant_confirmation" value="1" 
                                   {{ old('instant_confirmation', $draft->instant_confirmation ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="instant_confirmation">
                                <i class="fas fa-bolt text-warning me-1"></i> Instant Confirmation
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- End Additional Details Section -->
        
        <!-- Package Images Section -->
        <div class="form-wizard-section">
            <h6><i class="fas fa-images me-2"></i> Package Images</h6>
            <p class="small text-muted mb-3">Upload attractive images of your package destinations and activities. At least one image is required.</p>
            
            <!-- Image Upload Area -->
            <div class="image-upload-container mb-4">
                <div class="dropzone-wrapper" id="imageDropzone">
                    <div class="dropzone-message text-center p-4">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Drag & Drop Images Here</h5>
                        <p class="text-muted mb-3">or click to browse files</p>
                        <button type="button" class="btn btn-outline-primary" id="browseImages">
                            <i class="fas fa-folder-open me-1"></i> Browse Files
                        </button>
                        <input type="file" id="imageFileInput" name="images[]" multiple accept="image/*" style="display: none;">
                        <small class="form-text text-muted d-block mt-2">
                            Supported formats: JPEG, PNG, WebP • Maximum size: 5MB per image • Recommended: 1200x800px or higher
                        </small>
                    </div>
                </div>
                
                <!-- Upload Progress -->
                <div class="upload-progress" id="uploadProgress" style="display: none;">
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted" id="uploadStatus">Uploading images...</small>
                </div>
            </div>
            
            <!-- Uploaded Images Gallery -->
            <div class="uploaded-images-gallery" id="uploadedImagesGallery">
                <div class="row" id="imagesContainer">
                    <!-- Existing images from draft will be loaded here -->
                    @php
                        // Safely get images from draft data
                        $draftImages = [];
                        if (isset($draft) && $draft) {
                            if (isset($draft->images) && is_array($draft->images)) {
                                $draftImages = $draft->images;
                            } elseif (isset($draft->draft_data) && is_array($draft->draft_data) && isset($draft->draft_data['images'])) {
                                $draftImages = $draft->draft_data['images'];
                            }
                        }
                    @endphp
                    
                    @if(count($draftImages) > 0)
                        @foreach($draftImages as $image)
                        @php
                            // Convert to array if it's an object
                            $imageArray = is_array($image) ? $image : (array) $image;
                            
                            // Skip empty image arrays
                            if (empty($imageArray) || !isset($imageArray['id'])) {
                                continue;
                            }
                            
                            $imageId = $imageArray['id'];
                            $imageSizes = $imageArray['sizes'] ?? [];
                            $mediumImage = is_array($imageSizes) ? ($imageSizes['medium'] ?? '') : '';
                            $altText = $imageArray['alt_text'] ?? $imageArray['original_name'] ?? 'Package image';
                            $originalName = $imageArray['original_name'] ?? 'Unknown';
                            $isMain = $imageArray['is_main'] ?? false;
                        @endphp
                        
                        @if($mediumImage)
                        <div class="col-md-3 col-sm-4 col-6 mb-3 image-item" data-image-id="{{ $imageId }}">
                            <div class="image-card position-relative">
                                <img src="{{ asset('storage/' . $mediumImage) }}" 
                                     alt="{{ $altText }}" 
                                     class="img-fluid rounded shadow-sm" style="height: 200px; object-fit: cover; width: 100%;">
                                
                                <!-- Image Controls -->
                                <div class="image-controls position-absolute" style="top: 8px; right: 8px;">
                                    @if($isMain)
                                    <span class="badge bg-warning text-dark mb-1" style="font-size: 0.7em;">
                                        <i class="fas fa-star"></i> Main
                                    </span>
                                    @else
                                    <button type="button" class="btn btn-sm btn-warning set-main-btn mb-1" 
                                            data-image-id="{{ $imageId }}" title="Set as main image">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger delete-image-btn" 
                                            data-image-id="{{ $imageId }}" title="Delete image">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Drag Handle -->
                                <div class="drag-handle position-absolute" style="top: 8px; left: 8px;">
                                    <i class="fas fa-grip-horizontal text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></i>
                                </div>
                                
                                <!-- Image Info -->
                                <div class="image-info position-absolute w-100 p-2" style="bottom: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: white; border-radius: 0 0 0.375rem 0.375rem;">
                                    <small class="d-block text-truncate">{{ $originalName }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    @endif
                </div>
                
                <!-- Hidden inputs for existing images to preserve them in form submissions -->
                <div id="hiddenImagesInputs">
                    @if(count($draftImages) > 0)
                        @foreach($draftImages as $index => $image)
                        @php
                            $imageArray = is_array($image) ? $image : (array) $image;
                            if (empty($imageArray) || !isset($imageArray['id'])) continue;
                        @endphp
                        <input type="hidden" name="existing_images[{{ $index }}][id]" value="{{ $imageArray['id'] }}">
                        <input type="hidden" name="existing_images[{{ $index }}][filename]" value="{{ $imageArray['filename'] ?? '' }}">
                        <input type="hidden" name="existing_images[{{ $index }}][original_name]" value="{{ $imageArray['original_name'] ?? '' }}">
                        <input type="hidden" name="existing_images[{{ $index }}][is_main]" value="{{ $imageArray['is_main'] ?? false ? '1' : '0' }}">
                        <input type="hidden" name="existing_images[{{ $index }}][alt_text]" value="{{ $imageArray['alt_text'] ?? '' }}">
                        <input type="hidden" name="existing_images[{{ $index }}][uploaded_at]" value="{{ $imageArray['uploaded_at'] ?? '' }}">
                        @if(isset($imageArray['sizes']) && is_array($imageArray['sizes']))
                            @foreach($imageArray['sizes'] as $sizeKey => $sizePath)
                            <input type="hidden" name="existing_images[{{ $index }}][sizes][{{ $sizeKey }}]" value="{{ $sizePath }}">
                            @endforeach
                        @endif
                        @endforeach
                    @endif
                </div>
                
                <!-- Empty State -->
                <div class="empty-images-state text-center py-4" id="emptyImagesState" 
                     style="{{ count($draftImages) > 0 ? 'display: none;' : '' }}">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No images uploaded yet</h6>
                    <p class="text-muted small">Upload some attractive images to showcase your package</p>
                </div>
            </div>
            
            <!-- Image Management Instructions -->
            <div class="alert alert-info border-0" role="alert">
                <h6 class="alert-heading"><i class="fas fa-lightbulb me-1"></i> Image Tips</h6>
                <ul class="mb-0 small">
                    <li><strong>Main Image:</strong> The first image or starred image will be used as the main package image in listings</li>
                    <li><strong>Drag & Drop:</strong> You can reorder images by dragging them to different positions</li>
                    <li><strong>Quality:</strong> Use high-quality images (1200x800px or higher) for best results</li>
                    <li><strong>Content:</strong> Include images of destinations, activities, accommodations, and local experiences</li>
                </ul>
            </div>
        </div> <!-- End Package Images Section -->
        
        <!-- Provider Sources Section -->
        <div class="form-wizard-section">
            <h6><i class="fas fa-network-wired me-2"></i> Provider Sources</h6>
            <p class="small text-muted mb-3">Choose how you'll source hotels, transport, and flights for this package</p>
            
            <div class="row">
                <!-- Hotel Source -->
                <div class="col-md-4 mb-3">
                    <label for="hotel_source" class="form-label fw-bold">
                        <i class="fas fa-bed me-1"></i> Hotel Source <span class="text-danger">*</span>
                    </label>
                    <select class="form-select select2-enhanced" id="hotel_source" name="hotel_source" 
                            data-placeholder="Choose hotel source..." required>
                        <option value=""></option>
                        <option value="platform" {{ old('hotel_source', $draft->hotel_source ?? '') == 'platform' ? 'selected' : '' }}>🏢 Platform Hotels</option>
                        <option value="external" {{ old('hotel_source', $draft->hotel_source ?? '') == 'external' ? 'selected' : '' }}>🌐 External Hotels</option>
                        <option value="mixed" {{ old('hotel_source', $draft->hotel_source ?? '') == 'mixed' ? 'selected' : '' }}>🔀 Mixed Sources</option>
                    </select>
                    <div class="invalid-feedback"></div>
                    <small class="form-text text-muted">Select where you'll find hotel accommodations</small>
                </div>
                
                <!-- Transport Source -->
                <div class="col-md-4 mb-3">
                    <label for="transport_source" class="form-label fw-bold">
                        <i class="fas fa-bus me-1"></i> Transport Source <span class="text-danger">*</span>
                    </label>
                    <select class="form-select select2-enhanced" id="transport_source" name="transport_source" 
                            data-placeholder="Choose transport source..." required>
                        <option value=""></option>
                        <option value="platform" {{ old('transport_source', $draft->transport_source ?? '') == 'platform' ? 'selected' : '' }}>🚌 Platform Transport</option>
                        <option value="external" {{ old('transport_source', $draft->transport_source ?? '') == 'external' ? 'selected' : '' }}>🌐 External Transport</option>
                        <option value="mixed" {{ old('transport_source', $draft->transport_source ?? '') == 'mixed' ? 'selected' : '' }}>🔀 Mixed Sources</option>
                    </select>
                    <div class="invalid-feedback"></div>
                    <small class="form-text text-muted">Select where you'll source transport services</small>
                </div>
                
                <!-- Flight Source -->
                <div class="col-md-4 mb-3">
                    <label for="flight_source" class="form-label fw-bold">
                        <i class="fas fa-plane me-1"></i> Flight Source <span class="text-danger">*</span>
                    </label>
                    <select class="form-select select2-enhanced" id="flight_source" name="flight_source" 
                            data-placeholder="Choose flight source..." required>
                        <option value=""></option>
                        <option value="own" {{ old('flight_source', $draft->flight_source ?? '') == 'own' ? 'selected' : '' }}>✈️ My Own Flights</option>
                        <option value="platform" {{ old('flight_source', $draft->flight_source ?? '') == 'platform' ? 'selected' : '' }}>🏢 Platform Flights</option>
                        <option value="external" {{ old('flight_source', $draft->flight_source ?? '') == 'external' ? 'selected' : '' }}>🌐 External Flights</option>
                        <option value="mixed" {{ old('flight_source', $draft->flight_source ?? '') == 'mixed' ? 'selected' : '' }}>🔀 Mixed Sources</option>
                    </select>
                    <div class="invalid-feedback"></div>
                    <small class="form-text text-muted">Select where you'll source flight services</small>
                </div>
            </div>
        </div> <!-- End Provider Sources Section -->
        
        <!-- Help Text -->
        <div class="alert alert-info border-0" role="alert">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Next Steps</h6>
            <p class="mb-0">After completing this form, you'll continue to select providers, build your itinerary, set pricing, and review your package before publishing.</p>
        </div>
    </div>
</div>

<script>
// Global function for calculating duration from dates
function calculateDurationFromDates(startDate, endDate) {
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const timeDiff = end.getTime() - start.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 to include both start and end days
        const nightsDiff = Math.max(0, daysDiff - 1);
        
        const durationDaysInput = document.getElementById('duration_days');
        const durationNightsInput = document.getElementById('duration_nights');
        const itineraryDurationSpan = document.getElementById('itineraryDuration');
        
        if (durationDaysInput) durationDaysInput.value = daysDiff;
        if (durationNightsInput) durationNightsInput.value = nightsDiff;
        if (itineraryDurationSpan) itineraryDurationSpan.textContent = daysDiff + ' Days';
        
        // Update duration display across all steps
        updateDurationDisplay(daysDiff);
        
        return { days: daysDiff, nights: nightsDiff };
    }
    return { days: 0, nights: 0 };
}

// Function to update duration display across steps
function updateDurationDisplay(days) {
    // Update itinerary duration in step 2 if it exists
    const itineraryDuration = document.getElementById('itineraryDuration');
    if (itineraryDuration) {
        itineraryDuration.textContent = days + ' Days';
    }
    
    // Update any other duration displays
    const durationDisplays = document.querySelectorAll('.duration-display');
    durationDisplays.forEach(display => {
        display.textContent = days + ' Days';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Character counter for short description
    const shortDesc = document.getElementById('short_description');
    const counter = document.getElementById('shortDescCounter');
    
    function updateCounter() {
        if (!shortDesc || !counter) return;
        
        counter.textContent = shortDesc.value.length;
        if (shortDesc.value.length > 280) {
            counter.parentElement.classList.add('text-warning');
        } else {
            counter.parentElement.classList.remove('text-warning');
        }
    }
    
    if (shortDesc && counter) {
        shortDesc.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    }
    
    // Initialize Date Range Picker
    initializeDateRangePicker();
    
    // Destination autocomplete functionality
    setupDestinationAutocomplete();
    
    // Departure cities autocomplete functionality
    setupDepartureCitiesAutocomplete();
    
    // Initialize image upload functionality
    initializeImageUpload();
});

function setupDestinationAutocomplete() {
    const input = document.getElementById('destinationsInput');
    const suggestionsDiv = document.getElementById('destinationSuggestions');
    
    // Early return if elements don't exist
    if (!input || !suggestionsDiv) {

        return;
    }
    
    // Sample destinations - replace with actual API call
    const destinations = [
        'Istanbul', 'Cappadocia', 'Pamukkale', 'Antalya', 'Bodrum', 'Fethiye', 
        'Kas', 'Ephesus', 'Izmir', 'Ankara', 'Trabzon', 'Safranbolu', 
        'Konya', 'Mardin', 'Gaziantep', 'Mount Nemrut', 'Troy', 'Gallipoli'
    ];
    
    input.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        const matches = destinations.filter(dest => 
            dest.toLowerCase().includes(value.toLowerCase()) && 
            !isDestinationSelected(dest)
        );
        
        if (matches.length > 0) {
            suggestionsDiv.innerHTML = matches
                .slice(0, 5)
                .map(dest => `<div class="suggestion-item" onclick="addDestination('${dest}')">${dest}</div>`)
                .join('');
            suggestionsDiv.style.display = 'block';
        } else {
            suggestionsDiv.style.display = 'none';
        }
    });
    
    // Handle enter key to add destination from input
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = this.value.trim();
            if (value && !isDestinationSelected(value)) {
                addDestination(value);
                this.value = '';
            }
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.destination-input-container')) {
            suggestionsDiv.style.display = 'none';
        }
    });
}

function addDestination(destination) {
    if (isDestinationSelected(destination)) return;
    
    const container = document.getElementById('selectedDestinations');
    if (!container) {
        console.error('selectedDestinations container not found');
        return;
    }
    
    const tag = document.createElement('span');
    tag.className = 'badge bg-primary me-1 mb-1 destination-tag';
    tag.innerHTML = `
        ${destination}
        <button type="button" class="btn-close btn-close-white btn-sm ms-1" 
                onclick="removeDestination('${destination}')"></button>
        <input type="hidden" name="destinations[]" value="${destination}">
    `;
    
    container.appendChild(tag);
    
    const inputElement = document.getElementById('destinationsInput');
    const suggestionsElement = document.getElementById('destinationSuggestions');
    
    if (inputElement) inputElement.value = '';
    if (suggestionsElement) suggestionsElement.style.display = 'none';
}

function removeDestination(destination) {
    const tags = document.querySelectorAll('.destination-tag');
    tags.forEach(tag => {
        if (tag.textContent.trim().startsWith(destination)) {
            tag.remove();
        }
    });
}

function isDestinationSelected(destination) {
    const destinationInputs = document.querySelectorAll('input[name="destinations[]"]');
    if (!destinationInputs || destinationInputs.length === 0) {
        return false;
    }
    
    const selected = Array.from(destinationInputs).map(input => input.value || '');
    return selected.includes(destination);
}

// Departure Cities Autocomplete
function setupDepartureCitiesAutocomplete() {
    const input = document.getElementById('departureCitiesInput');
    const suggestionsDiv = document.getElementById('departureCitySuggestions');
    
    if (!input || !suggestionsDiv) {
        return;
    }
    
    // International departure cities - grouped by region
    const cities = [
        // Turkey
        'Istanbul', 'Ankara', 'Izmir', 'Antalya', 'Bursa', 'Adana', 
        'Gaziantep', 'Konya', 'Mersin', 'Kayseri', 'Eskişehir', 'Diyarbakır',
        'Samsun', 'Denizli', 'Şanlıurfa', 'Adapazarı', 'Malatya', 'Erzurum',
        'Van', 'Batman', 'Elâzığ', 'Tekirdağ', 'Kocaeli', 'Manisa',
        'Trabzon', 'Balıkesir', 'Kahramanmaraş', 'Aydın', 'Hatay',
        
        // Africa
        'Addis Ababa', 'Cairo', 'Johannesburg', 'Lagos', 'Nairobi', 'Casablanca',
        'Accra', 'Dar es Salaam', 'Khartoum', 'Kampala',
        
        // Middle East
        'Dubai', 'Abu Dhabi', 'Riyadh', 'Jeddah', 'Doha', 'Kuwait City',
        'Muscat', 'Manama', 'Amman', 'Beirut', 'Baghdad', 'Damascus',
        
        // Europe
        'London', 'Paris', 'Berlin', 'Rome', 'Madrid', 'Amsterdam',
        'Vienna', 'Brussels', 'Stockholm', 'Copenhagen', 'Oslo', 'Helsinki',
        'Athens', 'Lisbon', 'Prague', 'Budapest', 'Warsaw', 'Bucharest',
        
        // Asia
        'Jakarta', 'Kuala Lumpur', 'Singapore', 'Bangkok', 'Manila',
        'Dhaka', 'Karachi', 'Lahore', 'Islamabad', 'Delhi', 'Mumbai',
        'Kolkata', 'Chennai', 'Bangalore', 'Hyderabad',
        
        // North America
        'New York', 'Los Angeles', 'Chicago', 'Houston', 'Toronto',
        'Montreal', 'Vancouver', 'Washington DC', 'Boston', 'Miami',
        
        // Oceania
        'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Auckland', 'Wellington'
    ];
    
    input.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        const matches = cities.filter(city => 
            city.toLowerCase().includes(value.toLowerCase()) && 
            !isDepartureCitySelected(city)
        );
        
        if (matches.length > 0) {
            suggestionsDiv.innerHTML = matches
                .slice(0, 5)
                .map(city => `<div class="suggestion-item" onclick="addDepartureCity('${city}')">${city}</div>`)
                .join('');
            suggestionsDiv.style.display = 'block';
        } else {
            suggestionsDiv.style.display = 'none';
        }
    });
    
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = this.value.trim();
            if (value && !isDepartureCitySelected(value)) {
                addDepartureCity(value);
                this.value = '';
            }
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.departure-input-container')) {
            suggestionsDiv.style.display = 'none';
        }
    });
}

function addDepartureCity(city) {
    if (isDepartureCitySelected(city)) return;
    
    const container = document.getElementById('selectedDepartureCities');
    if (!container) {
        console.error('selectedDepartureCities container not found');
        return;
    }
    
    const tag = document.createElement('span');
    tag.className = 'badge bg-info me-1 mb-1 departure-city-tag';
    tag.innerHTML = `
        ${city}
        <button type="button" class="btn-close btn-close-white btn-sm ms-1" 
                onclick="removeDepartureCity('${city}')"></button>
        <input type="hidden" name="departure_cities[]" value="${city}">
    `;
    
    container.appendChild(tag);
    
    const inputElement = document.getElementById('departureCitiesInput');
    const suggestionsElement = document.getElementById('departureCitySuggestions');
    
    if (inputElement) inputElement.value = '';
    if (suggestionsElement) suggestionsElement.style.display = 'none';
}

function removeDepartureCity(city) {
    const tags = document.querySelectorAll('.departure-city-tag');
    tags.forEach(tag => {
        if (tag.textContent.trim().startsWith(city)) {
            tag.remove();
        }
    });
}

function isDepartureCitySelected(city) {
    const cityInputs = document.querySelectorAll('input[name="departure_cities[]"]');
    if (!cityInputs || cityInputs.length === 0) {
        return false;
    }
    
    const selected = Array.from(cityInputs).map(input => input.value || '');
    return selected.includes(city);
}

// Global function for initializing date range picker
function initializeDateRangePicker() {
    const dateRangeInput = document.getElementById('date_range');
    if (dateRangeInput) {
        // Initialize with Daterangepicker library
        $(dateRangeInput).daterangepicker({
            startDate: moment().add(1, 'day'), // Start from tomorrow
            endDate: moment().add(8, 'day'), // Default 7-day trip
            minDate: moment(), // Can't select past dates
            maxDate: moment().add(2, 'year'), // Up to 2 years in advance
            showDropdowns: true,
            showWeekNumbers: true,
            showISOWeekNumbers: false,
            alwaysShowCalendars: true,
            ranges: {
                'Weekend (2D/1N)': [moment().add(1, 'day'), moment().add(2, 'day')],
                '3 Days': [moment().add(1, 'day'), moment().add(3, 'day')],
                '1 Week': [moment().add(1, 'day'), moment().add(7, 'day')],
                '2 Weeks': [moment().add(1, 'day'), moment().add(14, 'day')],
                '1 Month': [moment().add(1, 'day'), moment().add(30, 'day')]
            },
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' to ',
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                weekLabel: 'W',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            opens: 'right',
            drops: 'down'
        }, function(start, end, label) {
            // Callback when date range is selected
            console.log('Date range selected:', start.format('YYYY-MM-DD'), 'to', end.format('YYYY-MM-DD'));
            
            // Update hidden fields
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            if (startDateInput) startDateInput.value = start.format('YYYY-MM-DD');
            if (endDateInput) endDateInput.value = end.format('YYYY-MM-DD');
            
            // Calculate and update duration
            calculateDurationFromDates(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            
            // Update display
            dateRangeInput.value = start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD');
        });
        
        // Set initial values if they exist
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            $(dateRangeInput).data('daterangepicker').setStartDate(startDate);
            $(dateRangeInput).data('daterangepicker').setEndDate(endDate);
            dateRangeInput.value = startDate + ' to ' + endDate;
            calculateDurationFromDates(startDate, endDate);
        }
    }
}

// Global function for initializing image upload functionality
function initializeImageUpload() {

    
    const dropzone = document.getElementById('imageDropzone');
    const fileInput = document.getElementById('imageFileInput');
    const browseButton = document.getElementById('browseImages');    
    if (!dropzone || !fileInput) {

        return;
    }
    
    // Browse button click handler
    if (browseButton) {
        browseButton.addEventListener('click', function(e) {

            fileInput.value = '';
            fileInput.click();
        });
    }
    
    // Dropzone click handler
    dropzone.addEventListener('click', function(e) {
        if (e.target === dropzone || e.target.closest('.dropzone-message')) {

            fileInput.value = '';
            fileInput.click();
        }
    });
    
    // File input change handler with debounce to prevent double uploads
    let fileInputTimeout = null;
    fileInput.addEventListener('change', (e) => {
        // Clear any existing timeout
        if (fileInputTimeout) {
            clearTimeout(fileInputTimeout);
        }
        
        // Debounce the file selection to prevent double uploads
        fileInputTimeout = setTimeout(() => {
            if (e.target.files && e.target.files.length > 0) {

                handleFileSelection(e.target.files);
                // Clear the input after handling to prevent re-triggering
                e.target.value = '';
            } else {

            }
        }, 100);
    });
    
    // Drag and drop handlers
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    
    dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (!dropzone.contains(e.relatedTarget)) {
            dropzone.classList.remove('drag-over');
        }
    });
    
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');

        handleFileSelection(e.dataTransfer.files);
    });
    
    // Setup event delegation for image controls
    setupImageControlEvents();
}

function handleFileSelection(files) {

    
    if (!files || files.length === 0) {

        return;
    }
    
    const validFiles = [];
    const errors = [];
    
    for (let file of files) {
        if (validateImageFile(file, errors)) {
            validFiles.push(file);
        }
    }
    
    if (errors.length > 0) {
        showImageUploadErrors(errors);
    }
    
    if (validFiles.length > 0) {

        uploadImages(validFiles);
    } else {

    }
}

function validateImageFile(file, errors) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        errors.push(`${file.name}: Invalid file type. Please use JPEG, PNG, or WebP.`);
        return false;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        errors.push(`${file.name}: File too large. Maximum size is 5MB.`);
        return false;
    }
    
    return true;
}

function showImageUploadErrors(errors) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Upload Errors:</strong>
            <ul class="mb-0 mt-2">
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const uploadContainer = document.querySelector('.image-upload-container');
    const alertContainer = document.createElement('div');
    alertContainer.innerHTML = alertHtml;
    uploadContainer.insertBefore(alertContainer.firstElementChild, uploadContainer.firstChild);
}

function uploadImages(files) {
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = uploadProgress?.querySelector('.progress-bar');
    const uploadStatus = document.getElementById('uploadStatus');
    
    let completedUploads = 0;
    const totalFiles = files.length;
    
    if (uploadProgress) {
        uploadProgress.style.display = 'block';
        if (uploadStatus) {
            uploadStatus.textContent = `Uploading ${totalFiles} image(s)...`;
        }
    }
    
    Array.from(files).forEach((file) => {
        uploadSingleImage(file)
            .then(response => {
                if (response.success) {
                    // Update draft ID if returned
                    if (response.draft_id) {
                        sessionStorage.setItem('package_draft_id', response.draft_id);
                        // Update hidden field if it exists - prioritize package_draft_id
                        let hiddenField = document.querySelector('[name="package_draft_id"]');
                        if (hiddenField) {
                            hiddenField.value = response.draft_id;
                        } else {
                            // Try draft_id field as fallback
                            hiddenField = document.querySelector('[name="draft_id"]');
                            if (hiddenField) {
                                hiddenField.value = response.draft_id;
                            } else {
                                // Create package_draft_id hidden field if neither exists
                                const newHiddenField = document.createElement('input');
                                newHiddenField.type = 'hidden';
                                newHiddenField.name = 'package_draft_id';
                                newHiddenField.value = response.draft_id;
                                document.body.appendChild(newHiddenField);
                            }
                        }

                    }
                    
                    addImageToGallery(response.image);
                    hideEmptyState();
                } else {
                    showImageUploadErrors([`${file.name}: ${response.error || 'Upload failed'}`]);
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showImageUploadErrors([`${file.name}: Upload failed`]);
            })
            .finally(() => {
                completedUploads++;
                
                const progress = Math.round((completedUploads / totalFiles) * 100);
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                }
                
                if (uploadStatus) {
                    uploadStatus.textContent = `Uploaded ${completedUploads} of ${totalFiles} images...`;
                }
                
                if (completedUploads === totalFiles) {
                    setTimeout(() => {
                        if (uploadProgress) {
                            uploadProgress.style.display = 'none';
                        }
                        if (progressBar) {
                            progressBar.style.width = '0%';
                            progressBar.setAttribute('aria-valuenow', '0');
                        }
                    }, 1500);
                }
            });
    });
}

function uploadSingleImage(file) {
    return new Promise((resolve, reject) => {

        
        const formData = new FormData();
        formData.append('image', file);
        const draftId = getDraftId();
        formData.append('package_draft_id', draftId);
        

        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('{{ route("b2b.travel-agent.packages.images.upload-draft") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {

            return response.json();
        })
        .then(data => {

            resolve(data);
        })
        .catch(error => {
            console.error('Upload fetch error:', error);
            reject(error);
        });
    });
}

function addImageToGallery(image) {
    const imagesContainer = document.getElementById('imagesContainer');
    const imageHtml = createImageHTML(image);
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = imageHtml;
    const imageElement = tempDiv.firstElementChild;
    
    imagesContainer.appendChild(imageElement);
    
    // Add hidden input for this image to preserve it in form submissions
    addHiddenImageInput(image);
    
    setTimeout(() => {
        imageElement.style.transition = 'all 0.3s ease';
        imageElement.style.opacity = '1';
        imageElement.style.transform = 'scale(1)';
    }, 50);
}

function createImageHTML(image) {
    const isMainBadge = image.is_main ? 
        `<span class="badge bg-warning text-dark mb-1" style="font-size: 0.7em;"><i class="fas fa-star"></i> Main</span>` :
        `<button type="button" class="btn btn-sm btn-warning set-main-btn mb-1" data-image-id="${image.id}" title="Set as main image"><i class="fas fa-star"></i></button>`;
    
    return `
        <div class="col-md-3 col-sm-4 col-6 mb-3 image-item" data-image-id="${image.id}">
            <div class="image-card position-relative">
                <img src="/storage/${image.sizes.medium}" 
                     alt="${image.alt_text || image.original_name}" 
                     class="img-fluid rounded shadow-sm" style="height: 200px; object-fit: cover; width: 100%;">
                
                <div class="image-controls position-absolute" style="top: 8px; right: 8px;">
                    ${isMainBadge}
                    <button type="button" class="btn btn-sm btn-danger delete-image-btn" 
                            data-image-id="${image.id}" title="Delete image">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="image-info position-absolute w-100 p-2" style="bottom: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: white; border-radius: 0 0 0.375rem 0.375rem;">
                    <small class="d-block text-truncate">${image.original_name}</small>
                </div>
            </div>
        </div>
    `;
}

function setupImageControlEvents() {
    const imagesContainer = document.getElementById('imagesContainer');
    if (!imagesContainer) return;
    
    imagesContainer.addEventListener('click', (e) => {
        if (e.target.closest('.delete-image-btn')) {
            const button = e.target.closest('.delete-image-btn');
            const imageId = button.getAttribute('data-image-id');
            deleteImage(imageId);
        }
        
        if (e.target.closest('.set-main-btn')) {
            const button = e.target.closest('.set-main-btn');
            const imageId = button.getAttribute('data-image-id');
            setMainImage(imageId);
        }
    });
}

function deleteImage(imageId) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch(`{{ route('b2b.travel-agent.packages.images.delete-draft', '') }}/${imageId}?package_draft_id=${getDraftId()}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            removeImageFromGallery(imageId);
        } else {
            showImageUploadErrors([data.error || 'Failed to delete image']);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showImageUploadErrors(['Failed to delete image']);
    });
}

function setMainImage(imageId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch('{{ route("b2b.travel-agent.packages.images.set-main-draft") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            image_id: imageId,
            package_draft_id: getDraftId()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMainImageDisplay(imageId);
        } else {
            showImageUploadErrors([data.error || 'Failed to set main image']);
        }
    })
    .catch(error => {
        console.error('Set main image error:', error);
        showImageUploadErrors(['Failed to set main image']);
    });
}

function removeImageFromGallery(imageId) {
    const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
    if (imageElement) {
        imageElement.style.transition = 'all 0.3s ease';
        imageElement.style.opacity = '0';
        imageElement.style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            imageElement.remove();
            // Remove corresponding hidden inputs
            removeHiddenImageInput(imageId);
            checkEmptyState();
        }, 300);
    }
}

function updateMainImageDisplay(mainImageId) {
    document.querySelectorAll('.image-item').forEach(item => {
        const imageId = item.getAttribute('data-image-id');
        const controls = item.querySelector('.image-controls');
        const isMain = imageId === mainImageId;
        
        if (isMain) {
            controls.innerHTML = `
                <span class="badge bg-warning text-dark mb-1" style="font-size: 0.7em;">
                    <i class="fas fa-star"></i> Main
                </span>
                <button type="button" class="btn btn-sm btn-danger delete-image-btn" 
                        data-image-id="${imageId}" title="Delete image">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        } else {
            controls.innerHTML = `
                <button type="button" class="btn btn-sm btn-warning set-main-btn mb-1" 
                        data-image-id="${imageId}" title="Set as main image">
                    <i class="fas fa-star"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger delete-image-btn" 
                        data-image-id="${imageId}" title="Delete image">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        }
        
        // Update hidden input to reflect main image status
        updateHiddenImageInput(imageId, 'is_main', isMain ? '1' : '0');
    });
}

function hideEmptyState() {
    const emptyState = document.getElementById('emptyImagesState');
    if (emptyState) emptyState.style.display = 'none';
}

function checkEmptyState() {
    const imagesContainer = document.getElementById('imagesContainer');
    const emptyState = document.getElementById('emptyImagesState');
    
    if (imagesContainer && emptyState) {
        const hasImages = imagesContainer.children.length > 0;
        emptyState.style.display = hasImages ? 'none' : 'block';
    }
}

function getDraftId() {

    
    // Try multiple sources for draft ID - prioritize package_draft_id
    const packageDraftIdField = document.querySelector('[name="package_draft_id"]')?.value;
    const draftIdField = document.querySelector('[name="draft_id"]')?.value;
    const sessionStorageValue = window.sessionStorage?.getItem('package_draft_id');
    const laravelSession = '{{ session("package_draft_id") ?? "" }}';
    const draftFromUrl = new URLSearchParams(window.location.search).get('package_draft_id') || new URLSearchParams(window.location.search).get('draft_id');
    
    // Check if we have a draft object with ID
    @if(isset($draft) && $draft && isset($draft->id))
    const draftFromBlade = '{{ $draft->id }}';
    @else
    const draftFromBlade = '';
    @endif
    
    const draftId = packageDraftIdField || draftFromBlade || draftIdField || sessionStorageValue || laravelSession || draftFromUrl;
    
    console.log('getDraftId debug:', {
        packageDraftIdField,
        draftIdField,
        draftFromBlade,
        sessionStorageValue,
        laravelSession, 
        draftFromUrl,
        finalDraftId: draftId
    });
    
    return draftId || '';
}

// Helper functions for managing hidden image inputs
function addHiddenImageInput(image) {
    const hiddenContainer = document.getElementById('hiddenImagesInputs');
    if (!hiddenContainer) return;
    
    // Get current count for unique naming
    const existingInputs = hiddenContainer.querySelectorAll('input[name^="existing_images["]');
    const index = Math.floor(existingInputs.length / 6); // 6 inputs per image
    
    const hiddenInputsHtml = `
        <input type="hidden" name="existing_images[${index}][id]" value="${image.id}">
        <input type="hidden" name="existing_images[${index}][filename]" value="${image.filename || ''}">
        <input type="hidden" name="existing_images[${index}][original_name]" value="${image.original_name || ''}">
        <input type="hidden" name="existing_images[${index}][is_main]" value="${image.is_main ? '1' : '0'}">
        <input type="hidden" name="existing_images[${index}][alt_text]" value="${image.alt_text || ''}">
        <input type="hidden" name="existing_images[${index}][uploaded_at]" value="${image.uploaded_at || ''}">
        ${image.sizes ? Object.entries(image.sizes).map(([sizeKey, sizePath]) => 
            `<input type="hidden" name="existing_images[${index}][sizes][${sizeKey}]" value="${sizePath}">`
        ).join('') : ''}
    `;
    
    // Create a container div for this image's hidden inputs
    const imageHiddenDiv = document.createElement('div');
    imageHiddenDiv.className = 'hidden-image-inputs';
    imageHiddenDiv.setAttribute('data-image-id', image.id);
    imageHiddenDiv.innerHTML = hiddenInputsHtml;
    
    hiddenContainer.appendChild(imageHiddenDiv);
    

}

function removeHiddenImageInput(imageId) {
    const hiddenContainer = document.getElementById('hiddenImagesInputs');
    if (!hiddenContainer) return;
    
    const imageHiddenDiv = hiddenContainer.querySelector(`[data-image-id="${imageId}"]`);
    if (imageHiddenDiv) {
        imageHiddenDiv.remove();

    }
}

function updateHiddenImageInput(imageId, property, value) {
    const hiddenContainer = document.getElementById('hiddenImagesInputs');
    if (!hiddenContainer) return;
    
    const imageHiddenDiv = hiddenContainer.querySelector(`[data-image-id="${imageId}"]`);
    if (imageHiddenDiv) {
        const propertyInput = imageHiddenDiv.querySelector(`input[name*="[${property}]"]`);
        if (propertyInput) {
            propertyInput.value = value;

        }
    }
}
</script>
