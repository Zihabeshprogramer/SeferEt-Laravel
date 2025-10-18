{{-- Custom Modal Component - Flutter Design Match --}}
@props([
    'id' => null,
    'title' => '',
    'size' => 'md', // sm, md, lg, xl
    'centered' => true,
    'scrollable' => false,
    'backdrop' => true, // true, false, 'static'
    'keyboard' => true,
    'fade' => true,
    'show' => false,
    'footerAlign' => 'end', // start, center, end, between
])

@php
    $modalId = $id ?: 'modal-' . uniqid();
    
    $modalClasses = collect([
        'seferet-modal',
        'modal',
        $fade ? 'fade' : ''
    ])->filter()->implode(' ');
    
    $dialogClasses = collect([
        'modal-dialog',
        'modal-' . $size,
        $centered ? 'modal-dialog-centered' : '',
        $scrollable ? 'modal-dialog-scrollable' : ''
    ])->filter()->implode(' ');
@endphp

<div class="{{ $modalClasses }}" id="{{ $modalId }}" tabindex="-1" aria-hidden="true"
     @if($backdrop === 'static') data-bs-backdrop="static" @elseif(!$backdrop) data-bs-backdrop="false" @endif
     @if(!$keyboard) data-bs-keyboard="false" @endif>
    <div class="{{ $dialogClasses }}">
        <div class="modal-content seferet-modal-content">
            @if($title || isset($header))
                <div class="modal-header seferet-modal-header">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h5 class="modal-title" id="{{ $modalId }}Label">
                            {{ $title }}
                        </h5>
                        <button type="button" class="btn-close seferet-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            @endif

            <div class="modal-body seferet-modal-body">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="modal-footer seferet-modal-footer footer-align-{{ $footerAlign }}">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Modal base styles */
.seferet-modal {
    --bs-modal-zindex: 1055;
}

.seferet-modal-content {
    background: var(--surface-color);
    border: none;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.seferet-modal-header {
    background: var(--surface-variant-color);
    border-bottom: 1px solid var(--border-color);
    padding: var(--spacing-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.seferet-modal-header .modal-title {
    color: var(--text-color);
    font-weight: 700;
    font-size: 1.25rem;
    margin: 0;
    line-height: 1.2;
}

.seferet-modal-body {
    padding: var(--spacing-lg);
    color: var(--text-color);
    line-height: 1.6;
    max-height: 70vh;
    overflow-y: auto;
}

.seferet-modal-footer {
    padding: var(--spacing-md) var(--spacing-lg);
    border-top: 1px solid var(--border-color);
    background: var(--surface-variant-color);
}

.seferet-modal-footer.footer-align-start {
    justify-content: flex-start;
}

.seferet-modal-footer.footer-align-center {
    justify-content: center;
}

.seferet-modal-footer.footer-align-end {
    justify-content: flex-end;
}

.seferet-modal-footer.footer-align-between {
    justify-content: space-between;
}

/* Close button */
.seferet-btn-close {
    background: none;
    border: none;
    color: var(--text-secondary-color);
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--border-radius-md);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
}

.seferet-btn-close:hover {
    background: rgba(var(--error-rgb), 0.1);
    color: var(--error-color);
    transform: scale(1.1);
}

/* Modal sizes */
.modal-sm .seferet-modal-content {
    max-width: 400px;
}

.modal-md .seferet-modal-content {
    max-width: 500px;
}

.modal-lg .seferet-modal-content {
    max-width: 800px;
}

.modal-xl .seferet-modal-content {
    max-width: 1200px;
}

/* Backdrop styling */
.seferet-modal .modal-backdrop {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

/* Animation keyframes */
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes modalSlideOut {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
}

/* Hide animation */
.seferet-modal.fade.show .seferet-modal-content {
    animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.seferet-modal.fade:not(.show) .seferet-modal-content {
    animation: modalSlideOut 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollable modal body */
.modal-dialog-scrollable .seferet-modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Custom scrollbar for modal body */
.seferet-modal-body::-webkit-scrollbar {
    width: 6px;
}

.seferet-modal-body::-webkit-scrollbar-track {
    background: var(--surface-variant-color);
    border-radius: 3px;
}

.seferet-modal-body::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.seferet-modal-body::-webkit-scrollbar-thumb:hover {
    background: var(--text-secondary-color);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .modal-lg .seferet-modal-content,
    .modal-xl .seferet-modal-content {
        max-width: 95%;
        margin: 0.5rem;
    }
    
    .seferet-modal-header,
    .seferet-modal-body,
    .seferet-modal-footer {
        padding: var(--spacing-md);
    }
    
    .seferet-modal-header .modal-title {
        font-size: 1.125rem;
    }
    
    .seferet-modal-body {
        max-height: 60vh;
    }
}

@media (max-width: 576px) {
    .seferet-modal-content {
        margin: 0.25rem;
        border-radius: var(--border-radius-md);
    }
    
    .seferet-modal-header,
    .seferet-modal-body,
    .seferet-modal-footer {
        padding: var(--spacing-sm);
    }
    
    .seferet-modal-header .modal-title {
        font-size: 1rem;
    }
}

/* Loading state */
.seferet-modal .modal-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-xl);
    color: var(--text-secondary-color);
}

.seferet-modal .modal-loading i {
    font-size: 2rem;
    margin-bottom: var(--spacing-sm);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Modal overlay enhancements */
.seferet-modal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

.seferet-modal .modal-dialog {
    margin: 0;
    width: 100%;
    max-width: var(--modal-max-width, 500px);
}

/* Focus management */
.seferet-modal-content:focus {
    outline: none;
}

.seferet-btn-close:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Utility classes for modal content */
.modal-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    margin: 0 auto var(--spacing-md);
    font-size: 1.5rem;
}

.modal-icon.success {
    background: rgba(var(--success-rgb), 0.1);
    color: var(--success-color);
}

.modal-icon.error {
    background: rgba(var(--error-rgb), 0.1);
    color: var(--error-color);
}

.modal-icon.warning {
    background: rgba(var(--warning-rgb), 0.1);
    color: var(--warning-color);
}

.modal-icon.info {
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary-color);
}

.modal-text-center {
    text-align: center;
}

.modal-text-center .modal-title {
    margin-bottom: var(--spacing-sm);
}
</style>

<script>
// Initialize modals when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all Bootstrap modals
    const modals = document.querySelectorAll('.seferet-modal');
    modals.forEach(modal => {
        // Add custom event listeners
        modal.addEventListener('show.bs.modal', function(e) {
            // Add any custom show logic here
            this.classList.add('seferet-modal-showing');
        });
        
        modal.addEventListener('shown.bs.modal', function(e) {
            // Focus management
            const firstInput = this.querySelector('input, select, textarea, button');
            if (firstInput && !firstInput.disabled) {
                firstInput.focus();
            }
            this.classList.remove('seferet-modal-showing');
        });
        
        modal.addEventListener('hide.bs.modal', function(e) {
            // Add any custom hide logic here
            this.classList.add('seferet-modal-hiding');
        });
        
        modal.addEventListener('hidden.bs.modal', function(e) {
            // Clean up
            this.classList.remove('seferet-modal-hiding');
        });
    });
});

// Utility function to show modal programmatically
function showSeferetModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// Utility function to hide modal programmatically
function hideSeferetModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
}

// Utility function to create confirmation modal
function showConfirmationModal(title, message, onConfirm, onCancel) {
    const modalId = 'confirmation-modal-' + Date.now();
    const modalHtml = `
        <div class="seferet-modal modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content seferet-modal-content">
                    <div class="modal-header seferet-modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close seferet-btn-close" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body seferet-modal-body modal-text-center">
                        <div class="modal-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer seferet-modal-footer footer-align-end">
                        <button type="button" class="seferet-btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="seferet-btn btn-danger ms-2" id="confirm-btn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = document.getElementById(modalId);
    const bsModal = new bootstrap.Modal(modal);
    
    // Handle confirm button
    const confirmBtn = modal.querySelector('#confirm-btn');
    confirmBtn.addEventListener('click', function() {
        if (onConfirm) onConfirm();
        bsModal.hide();
    });
    
    // Handle modal cleanup
    modal.addEventListener('hidden.bs.modal', function() {
        if (onCancel) onCancel();
        modal.remove();
    });
    
    bsModal.show();
}
</script>
