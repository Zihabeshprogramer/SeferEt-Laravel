{{-- Custom Button Component - Flutter Design Match --}}
@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, outline-primary, etc.
    'size' => 'md', // sm, md, lg
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'fullWidth' => false,
    'rounded' => false,
    'elevation' => true
])

@php
    $classes = collect([
        'seferet-btn',
        'btn-' . $variant,
        'btn-' . $size,
        $fullWidth ? 'w-100' : '',
        $rounded ? 'rounded-pill' : '',
        $elevation ? 'btn-elevated' : '',
        $loading ? 'btn-loading' : '',
        $disabled ? 'disabled' : ''
    ])->filter()->implode(' ');
    
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }} 
    {{ $attributes->merge([
        'class' => $classes,
        'type' => $href ? null : $type,
        'href' => $href,
        'disabled' => $disabled ? true : null,
        'aria-disabled' => ($href && $disabled) ? 'true' : null
    ]) }}
>
    @if($loading)
        <span class="btn-spinner me-2">
            <i class="fas fa-spinner fa-spin"></i>
        </span>
    @elseif($icon && $iconPosition === 'left')
        <i class="{{ $icon }} me-2"></i>
    @endif
    
    <span class="btn-text">{{ $slot }}</span>
    
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ms-2"></i>
    @endif
</{{ $tag }}>

<style>
.seferet-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-primary);
    font-weight: 600;
    text-decoration: none;
    border: 2px solid transparent;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    user-select: none;
}

.seferet-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
}

/* Size variants */
.seferet-btn.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    min-height: 36px;
}

.seferet-btn.btn-md {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    min-height: 44px;
}

.seferet-btn.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
    min-height: 52px;
}

/* Color variants */
.seferet-btn.btn-primary {
    background: var(--primary-color);
    color: var(--text-on-primary);
    border-color: var(--primary-color);
    box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.2);
}

.seferet-btn.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.3);
}

.seferet-btn.btn-secondary {
    background: var(--secondary-color);
    color: var(--text-primary);
    border-color: var(--secondary-color);
    box-shadow: 0 2px 4px rgba(var(--secondary-rgb), 0.2);
}

.seferet-btn.btn-secondary:hover {
    background: #e19b0a;
    border-color: #e19b0a;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(var(--secondary-rgb), 0.3);
}

.seferet-btn.btn-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #0ea5e9 100%);
    color: white;
    border-color: var(--success-color);
}

.seferet-btn.btn-success:hover {
    background: linear-gradient(135deg, #0ea5e9 0%, var(--success-color) 100%);
    transform: translateY(-1px);
}

.seferet-btn.btn-danger {
    background: linear-gradient(135deg, var(--error-color) 0%, #dc2626 100%);
    color: white;
    border-color: var(--error-color);
}

.seferet-btn.btn-danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, var(--error-color) 100%);
    transform: translateY(-1px);
}

.seferet-btn.btn-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
    color: var(--text-color);
    border-color: var(--warning-color);
}

.seferet-btn.btn-warning:hover {
    background: linear-gradient(135deg, #d97706 0%, var(--warning-color) 100%);
    transform: translateY(-1px);
}

/* Outline variants */
.seferet-btn.btn-outline-primary {
    background: transparent;
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.seferet-btn.btn-outline-primary:hover {
    background: var(--primary-color);
    color: var(--text-on-primary);
    box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.2);
}

.seferet-btn.btn-outline-secondary {
    background: transparent;
    color: var(--secondary-color);
    border-color: var(--secondary-color);
}

.seferet-btn.btn-outline-secondary:hover {
    background: var(--secondary-color);
    color: var(--text-primary);
    box-shadow: 0 2px 4px rgba(var(--secondary-rgb), 0.2);
}

/* States */
.seferet-btn.btn-elevated {
    box-shadow: var(--shadow-md);
}

.seferet-btn.btn-elevated:hover {
    box-shadow: var(--shadow-lg);
}

.seferet-btn.disabled,
.seferet-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.seferet-btn.btn-loading {
    pointer-events: none;
}

.seferet-btn.btn-loading .btn-text {
    opacity: 0.7;
}

.btn-spinner {
    display: inline-flex;
    align-items: center;
}

/* Ripple effect */
.seferet-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.seferet-btn:active::before {
    width: 300px;
    height: 300px;
}

/* Responsive */
@media (max-width: 768px) {
    .seferet-btn.btn-lg {
        padding: 0.875rem 1.75rem;
        font-size: 1rem;
    }
    
    .seferet-btn.btn-md {
        padding: 0.625rem 1.25rem;
        font-size: 0.9rem;
    }
}
</style>
