@extends('layouts.b2b')

@section('title', 'Edit Ad')
@section('page-title', 'Edit Ad: ' . $ad->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.ads.index') }}">My Ads</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.ads.show', $ad) }}">{{ $ad->title }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Main Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Ad Details
                    </h3>
                </div>
                <div class="card-body">
                    @if($ad->isRejected() && $ad->rejection_reason)
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Rejection Reason</h5>
                            <p class="mb-0">{{ $ad->rejection_reason }}</p>
                            <small class="text-muted">Please address the issues above before resubmitting.</small>
                        </div>
                    @endif

                    <form id="ad-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Product Selection -->
                        <div class="form-group">
                            <label for="product_id">Product to Advertise <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-control" required>
                                <option value="">-- Select a Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-type="{{ $product->type }}"
                                            {{ $ad->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select which product this ad will promote</small>
                        </div>

                        <!-- Ad Title -->
                        <div class="form-group">
                            <label for="title">Ad Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" 
                                   placeholder="e.g. Special Umrah Package - Limited Time!" 
                                   maxlength="100" value="{{ old('title', $ad->title) }}" required>
                            <small class="form-text text-muted">Keep it short and compelling (max 100 characters)</small>
                        </div>

                        <!-- Ad Description -->
                        <div class="form-group">
                            <label for="description">Description (Optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="3" 
                                      placeholder="Brief description of your offer..." 
                                      maxlength="250">{{ old('description', $ad->description) }}</textarea>
                            <small class="form-text text-muted">Optional additional details (max 250 characters)</small>
                        </div>

                        <!-- Banner Image Upload -->
                        <div class="form-group">
                            <label for="image">Banner Image</label>
                            
                            @if($ad->hasImage())
                                <div class="mb-2">
                                    <small class="text-muted">Current image:</small>
                                    <div class="position-relative d-inline-block" style="max-width: 300px;">
                                        <img src="{{ $ad->image_url }}" alt="Current" class="img-fluid border">
                                    </div>
                                </div>
                            @endif

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                                <label class="custom-file-label" for="image">Choose new file (optional)...</label>
                            </div>
                            <small class="form-text text-muted">
                                Leave empty to keep current image. JPEG/PNG, max 2MB. Recommended: 1200x600px (2:1 ratio)
                            </small>
                            <div id="image-preview-container" class="mt-3" style="display: none;">
                                <div class="position-relative" style="max-width: 600px; margin: 0 auto;">
                                    <img id="image-preview" src="" alt="Preview" class="img-fluid border">
                                    <div id="cta-overlay" class="position-absolute" style="cursor: move; z-index: 10;">
                                        <button type="button" class="btn btn-{{ $ad->cta_style ?? 'primary' }} btn-sm" style="pointer-events: none;">
                                            <span id="cta-preview-text">{{ $ad->cta_text ?? 'Book Now' }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle"></i> <strong>Drag the button</strong> to reposition it on your new image
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button Position Editor -->
                        @if($ad->hasImage())
                        <div class="form-group">
                            <label>Adjust Button Position</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="position-relative" id="position-editor" style="max-width: 600px; margin: 0 auto;">
                                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="img-fluid border">
                                        <div id="cta-editor-overlay" class="position-absolute" style="cursor: move; z-index: 10; top: {{ $ad->cta_position_y ?? 50 }}%; left: {{ $ad->cta_position_x ?? 50 }}%; transform: translate(-50%, -50%);">
                                            <button type="button" class="btn btn-{{ $ad->cta_style ?? 'primary' }} btn-sm" style="pointer-events: none;">
                                                <span id="cta-editor-text">{{ $ad->cta_text ?? 'Book Now' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fas fa-info-circle"></i> <strong>Drag the button</strong> to reposition it on your current image. The new position will be saved when you update the ad.
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- CTA Text -->
                        <div class="form-group">
                            <label for="cta_text">Call-to-Action Button Text</label>
                            <input type="text" name="cta_text" id="cta_text" class="form-control" 
                                   placeholder="e.g. Book Now, Learn More, Get Offer" 
                                   maxlength="30" value="{{ old('cta_text', $ad->cta_text ?? 'Book Now') }}">
                            <small class="form-text text-muted">Button text (max 30 characters)</small>
                        </div>

                        <!-- CTA Style -->
                        <div class="form-group">
                            <label for="cta_style">Button Style</label>
                            <select name="cta_style" id="cta_style" class="form-control">
                                <option value="primary" {{ ($ad->cta_style ?? 'primary') == 'primary' ? 'selected' : '' }}>Primary (Blue)</option>
                                <option value="success" {{ $ad->cta_style == 'success' ? 'selected' : '' }}>Success (Green)</option>
                                <option value="warning" {{ $ad->cta_style == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                                <option value="danger" {{ $ad->cta_style == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                <option value="info" {{ $ad->cta_style == 'info' ? 'selected' : '' }}>Info (Cyan)</option>
                                <option value="secondary" {{ $ad->cta_style == 'secondary' ? 'selected' : '' }}>Secondary (Gray)</option>
                            </select>
                        </div>

                        <!-- Hidden fields for CTA position -->
                        <input type="hidden" name="cta_position_x" id="cta_position_x" value="{{ $ad->cta_position_x ?? 50 }}">
                        <input type="hidden" name="cta_position_y" id="cta_position_y" value="{{ $ad->cta_position_y ?? 50 }}">

                        <!-- Schedule -->
                        <div class="form-group">
                            <label>Schedule (Optional)</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" name="start_at" id="start_at" class="form-control" 
                                           value="{{ old('start_at', $ad->start_at ? $ad->start_at->format('Y-m-d') : '') }}">
                                    <small class="form-text text-muted">Leave empty to start immediately</small>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="end_at" id="end_at" class="form-control" 
                                           value="{{ old('end_at', $ad->end_at ? $ad->end_at->format('Y-m-d') : '') }}">
                                    <small class="form-text text-muted">Leave empty for no end date</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <div>
                                @if($ad->isDraft())
                                <button type="button" id="save-draft-btn" class="btn btn-secondary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                @else
                                <button type="button" id="save-draft-btn" class="btn btn-secondary">
                                    <i class="fas fa-save"></i> Update Ad
                                </button>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('b2b.ads.show', $ad) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                @if($ad->isDraft() || $ad->isRejected())
                                <button type="button" id="submit-btn" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Save & Submit for Approval
                                </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-md-4">
            <!-- Current Ad Preview -->
            @if($ad->hasImage())
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-eye"></i> Current Ad Preview
                    </h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="img-fluid border">
                        <div class="position-absolute" style="top: {{ $ad->cta_position_y ?? 50 }}%; left: {{ $ad->cta_position_x ?? 50 }}%; transform: translate(-50%, -50%);">
                            <button type="button" class="btn btn-{{ $ad->cta_style ?? 'primary' }} btn-sm">
                                {{ $ad->cta_text ?? 'Book Now' }}
                            </button>
                        </div>
                    </div>
                    <p class="text-muted text-center mt-2 mb-0 small">This is how your ad currently looks</p>
                </div>
            </div>
            @endif

            <!-- Guidelines Card -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb"></i> Editing Tips
                    </h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-info-circle text-info"></i> What You Can Change</h6>
                    <ul class="small">
                        <li>Product selection</li>
                        <li>Ad title and description</li>
                        <li>Banner image (upload new)</li>
                        <li>CTA button text and style</li>
                        <li>CTA button position (if uploading new image)</li>
                        <li>Schedule (start/end dates)</li>
                    </ul>

                    @if($ad->isRejected())
                    <h6 class="mt-3"><i class="fas fa-exclamation-triangle text-danger"></i> Resubmission</h6>
                    <ul class="small">
                        <li>Address all rejection reasons above</li>
                        <li>Make necessary changes</li>
                        <li>Click "Save & Submit for Approval" when ready</li>
                    </ul>
                    @endif

                    <h6 class="mt-3"><i class="fas fa-check-circle text-success"></i> Image Requirements</h6>
                    <ul class="small">
                        <li>Format: JPEG or PNG</li>
                        <li>Size: Max 2MB</li>
                        <li>Dimensions: 1200x600px recommended</li>
                        <li>Aspect Ratio: 2:1 (horizontal)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        #cta-overlay {
            top: {{ $ad->cta_position_y ?? 50 }}%;
            left: {{ $ad->cta_position_x ?? 50 }}%;
            transform: translate(-50%, -50%);
        }
        
        .custom-file-label::after {
            content: "Browse";
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script>
        $(document).ready(function() {
            let imageUploaded = false;
            let ctaPositionX = {{ $ad->cta_position_x ?? 50 }}; // percentage
            let ctaPositionY = {{ $ad->cta_position_y ?? 50 }}; // percentage

            // Custom file input label
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
            });

            // Handle image upload and preview
            $('#image').on('change', function(e) {
                let file = e.target.files[0];
                
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        toastr.error('Image size must be less than 2MB');
                        $(this).val('');
                        return;
                    }

                    // Validate file type
                    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                        toastr.error('Only JPEG and PNG images are allowed');
                        $(this).val('');
                        return;
                    }

                    // Show preview
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image-preview').attr('src', e.target.result);
                        $('#image-preview-container').show();
                        imageUploaded = true;
                        // Reset CTA position to center for new image
                        ctaPositionX = 50;
                        ctaPositionY = 50;
                        $('#cta_position_x').val(50);
                        $('#cta_position_y').val(50);
                        $('#cta-overlay').css('transform', 'translate(-50%, -50%)');
                        $('#cta-overlay').attr('data-x', 0);
                        $('#cta-overlay').attr('data-y', 0);
                        initializeDraggable();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Initialize position editor for current image
            @if($ad->hasImage())
            initializePositionEditor();
            @endif

            // CTA text change
            $('#cta_text').on('input', function() {
                $('#cta-preview-text').text($(this).val() || 'Book Now');
                $('#cta-editor-text').text($(this).val() || 'Book Now');
            });

            // CTA style change
            $('#cta_style').on('change', function() {
                let style = $(this).val();
                // Update preview overlay button
                let btn = $('#cta-overlay button');
                btn.removeClass('btn-primary btn-success btn-warning btn-danger btn-info btn-secondary');
                btn.addClass('btn-' + style);
                // Update editor overlay button
                let editorBtn = $('#cta-editor-overlay button');
                editorBtn.removeClass('btn-primary btn-success btn-warning btn-danger btn-info btn-secondary');
                editorBtn.addClass('btn-' + style);
            });

            // Initialize position editor for current image
            function initializePositionEditor() {
                interact('#cta-editor-overlay').draggable({
                    inertia: false,
                    modifiers: [
                        interact.modifiers.restrict({
                            restriction: 'parent',
                            endOnly: true
                        })
                    ],
                    autoScroll: false,
                    listeners: {
                        move: function(event) {
                            const target = event.target;
                            const parent = target.parentElement;
                            const parentRect = parent.getBoundingClientRect();
                            
                            // Get current position from style
                            const currentLeft = parseFloat(target.style.left) || {{ $ad->cta_position_x ?? 50 }};
                            const currentTop = parseFloat(target.style.top) || {{ $ad->cta_position_y ?? 50 }};
                            
                            // Calculate new percentage position
                            const newX = Math.max(0, Math.min(100, currentLeft + (event.dx / parentRect.width * 100)));
                            const newY = Math.max(0, Math.min(100, currentTop + (event.dy / parentRect.height * 100)));
                            
                            // Update position
                            target.style.left = newX + '%';
                            target.style.top = newY + '%';
                            
                            // Update hidden fields
                            ctaPositionX = newX;
                            ctaPositionY = newY;
                            $('#cta_position_x').val(newX.toFixed(2));
                            $('#cta_position_y').val(newY.toFixed(2));
                        }
                    }
                });
            }

            // Initialize draggable CTA button (only if new image uploaded)
            function initializeDraggable() {
                interact('#cta-overlay').draggable({
                    inertia: false,
                    modifiers: [
                        interact.modifiers.restrictRect({
                            restriction: 'parent',
                            endOnly: true
                        })
                    ],
                    autoScroll: false,
                    listeners: {
                        move: dragMoveListener
                    }
                });
            }

            function dragMoveListener(event) {
                const target = event.target;
                const parent = target.parentElement;
                
                // Get parent dimensions
                const parentRect = parent.getBoundingClientRect();
                
                // Parse existing translate values or start at center
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                // Calculate percentage position
                ctaPositionX = Math.max(0, Math.min(100, ((x + parentRect.width / 2) / parentRect.width) * 100));
                ctaPositionY = Math.max(0, Math.min(100, ((y + parentRect.height / 2) / parentRect.height) * 100));

                // Update position
                target.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);

                // Update hidden fields
                $('#cta_position_x').val(ctaPositionX.toFixed(2));
                $('#cta_position_y').val(ctaPositionY.toFixed(2));
            }

            // Save changes (keep current status)
            $('#save-draft-btn').on('click', function() {
                updateAd('{{ $ad->status }}');
            });

            // Submit for Approval (change status to pending)
            $('#submit-btn').on('click', function() {
                updateAd('pending');
            });

            // Update ad function
            function updateAd(status) {
                let formData = new FormData($('#ad-form')[0]);
                formData.append('status', status);
                formData.append('_method', 'PUT');

                // Get product type
                let productType = $('#product_id option:selected').data('type');
                formData.append('product_type', productType);

                // Show loading
                let btn = status === 'pending' ? $('#submit-btn') : $('#save-draft-btn');
                let originalText = btn.html();
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route('b2b.ads.update', $ad) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Ad updated successfully');
                            setTimeout(function() {
                                window.location.href = '{{ route('b2b.ads.show', $ad) }}';
                            }, 1000);
                        } else {
                            toastr.error(response.message || 'Failed to update ad');
                            btn.prop('disabled', false);
                            btn.html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to update ad';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join('<br>');
                        }
                        toastr.error(message);
                        btn.prop('disabled', false);
                        btn.html(originalText);
                    }
                });
            }

            // Set minimum date for start_at and end_at
            let today = new Date().toISOString().split('T')[0];
            $('#start_at').attr('min', today);
            $('#end_at').attr('min', today);

            // Validate end date
            $('#start_at').on('change', function() {
                $('#end_at').attr('min', $(this).val() || today);
            });
        });
    </script>
@endsection
