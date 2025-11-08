@extends('layouts.b2b')

@section('title', 'Create Ad')
@section('page-title', 'Create Promotional Ad')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.ads.index') }}">My Ads</a></li>
    <li class="breadcrumb-item active">Create Ad</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Main Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-ad mr-2"></i>
                        Ad Details
                    </h3>
                </div>
                <div class="card-body">
                    <form id="ad-form" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Product Selection -->
                        <div class="form-group">
                            <label for="product_id">Product to Advertise <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-control" required>
                                <option value="">-- Select a Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-type="{{ $product->type }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select which product this ad will promote</small>
                        </div>

                        <!-- Ad Title -->
                        <div class="form-group">
                            <label for="title">Ad Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Special Umrah Package - Limited Time!" maxlength="100" required>
                            <small class="form-text text-muted">Keep it short and compelling (max 100 characters)</small>
                        </div>

                        <!-- Ad Description -->
                        <div class="form-group">
                            <label for="description">Description (Optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Brief description of your offer..." maxlength="250"></textarea>
                            <small class="form-text text-muted">Optional additional details (max 250 characters)</small>
                        </div>

                        <!-- Banner Image Upload -->
                        <div class="form-group">
                            <label for="image">Banner Image <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/jpeg,image/png,image/jpg" required>
                                <label class="custom-file-label" for="image">Choose file...</label>
                            </div>
                            <small class="form-text text-muted">
                                <strong>Required:</strong> JPEG/PNG, max 2MB. Recommended size: 1200x600px (2:1 ratio)
                            </small>
                            <div id="image-preview-container" class="mt-3" style="display: none;">
                                <div class="position-relative" style="max-width: 600px; margin: 0 auto;">
                                    <img id="image-preview" src="" alt="Preview" class="img-fluid border">
                                    <div id="cta-overlay" class="position-absolute" style="cursor: move; z-index: 10;">
                                        <button type="button" class="btn btn-primary btn-sm" style="pointer-events: none;">
                                            <span id="cta-preview-text">Book Now</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle"></i> <strong>Drag the button</strong> to position it on your image
                                </div>
                            </div>
                        </div>

                        <!-- CTA Text -->
                        <div class="form-group">
                            <label for="cta_text">Call-to-Action Button Text</label>
                            <input type="text" name="cta_text" id="cta_text" class="form-control" placeholder="e.g. Book Now, Learn More, Get Offer" maxlength="30" value="Book Now">
                            <small class="form-text text-muted">Button text (max 30 characters)</small>
                        </div>

                        <!-- CTA Style -->
                        <div class="form-group">
                            <label for="cta_style">Button Style</label>
                            <select name="cta_style" id="cta_style" class="form-control">
                                <option value="primary">Primary (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="warning">Warning (Yellow)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="info">Info (Cyan)</option>
                                <option value="secondary">Secondary (Gray)</option>
                            </select>
                        </div>

                        <!-- Hidden fields for CTA position -->
                        <input type="hidden" name="cta_position_x" id="cta_position_x" value="50">
                        <input type="hidden" name="cta_position_y" id="cta_position_y" value="50">

                        <!-- Schedule -->
                        <div class="form-group">
                            <label>Schedule (Optional)</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" name="start_at" id="start_at" class="form-control" placeholder="Start Date">
                                    <small class="form-text text-muted">Leave empty to start immediately</small>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="end_at" id="end_at" class="form-control" placeholder="End Date">
                                    <small class="form-text text-muted">Leave empty for no end date</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <button type="button" id="save-draft-btn" class="btn btn-secondary">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <div>
                                <a href="{{ route('b2b.ads.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="button" id="submit-btn" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-md-4">
            <!-- Guidelines Card -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb"></i> Ad Guidelines
                    </h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-check-circle text-success"></i> Image Requirements</h6>
                    <ul class="small">
                        <li>Format: JPEG or PNG</li>
                        <li>Size: Max 2MB</li>
                        <li>Dimensions: 1200x600px recommended</li>
                        <li>Aspect Ratio: 2:1 (horizontal)</li>
                    </ul>

                    <h6 class="mt-3"><i class="fas fa-check-circle text-success"></i> Content Tips</h6>
                    <ul class="small">
                        <li>Use high-quality, eye-catching images</li>
                        <li>Keep titles short and compelling</li>
                        <li>Position CTA button in a visible area</li>
                        <li>Avoid text-heavy banners</li>
                    </ul>

                    <h6 class="mt-3"><i class="fas fa-info-circle text-info"></i> Approval Process</h6>
                    <ul class="small">
                        <li>Ads are reviewed by admin within 24-48 hours</li>
                        <li>You'll be notified of approval/rejection</li>
                        <li>Rejected ads can be edited and resubmitted</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        #cta-overlay {
            top: 50%;
            left: 50%;
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
            let ctaPositionX = 50; // percentage
            let ctaPositionY = 50; // percentage

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
                        initializeDraggable();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // CTA text change
            $('#cta_text').on('input', function() {
                $('#cta-preview-text').text($(this).val() || 'Book Now');
            });

            // CTA style change
            $('#cta_style').on('change', function() {
                let style = $(this).val();
                let btn = $('#cta-overlay button');
                btn.removeClass('btn-primary btn-success btn-warning btn-danger btn-info btn-secondary');
                btn.addClass('btn-' + style);
            });

            // Initialize draggable CTA button
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

            // Save as Draft
            $('#save-draft-btn').on('click', function() {
                submitAd('draft');
            });

            // Submit for Approval
            $('#submit-btn').on('click', function() {
                if (!imageUploaded) {
                    toastr.error('Please upload a banner image');
                    return;
                }
                submitAd('pending');
            });

            // Submit ad function
            function submitAd(status) {
                let formData = new FormData($('#ad-form')[0]);
                formData.append('status', status);

                // Get product type
                let productType = $('#product_id option:selected').data('type');
                formData.append('product_type', productType);

                // Show loading
                let btn = status === 'draft' ? $('#save-draft-btn') : $('#submit-btn');
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route('b2b.ads.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Ad saved successfully');
                            setTimeout(function() {
                                window.location.href = '{{ route('b2b.ads.index') }}';
                            }, 1000);
                        } else {
                            toastr.error(response.message || 'Failed to save ad');
                            btn.prop('disabled', false);
                            btn.html(status === 'draft' ? '<i class="fas fa-save"></i> Save as Draft' : '<i class="fas fa-paper-plane"></i> Submit for Approval');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to save ad';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join('<br>');
                        }
                        toastr.error(message);
                        btn.prop('disabled', false);
                        btn.html(status === 'draft' ? '<i class="fas fa-save"></i> Save as Draft' : '<i class="fas fa-paper-plane"></i> Submit for Approval');
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
