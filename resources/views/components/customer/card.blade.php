{{-- Custom Card Component - Flutter Design Match --}}
@props([
    'variant' => 'default', // default, elevated, outlined, filled
    'elevation' => 'md', // none, sm, md, lg, xl
    'padding' => 'md', // none, sm, md, lg
    'rounded' => 'md', // none, sm, md, lg, full
    'hover' => false,
    'clickable' => false,
    'href' => null,
    'header' => null,
    'footer' => null,
    'image' => null,
    'imageAlt' => '',
    'imagePosition' => 'top' // top, left, right, background
])

@php
    $classes = collect([
        'seferet-card',
        'card-variant-' . $variant,
        'card-elevation-' . $elevation,
        'card-padding-' . $padding,
        'card-rounded-' . $rounded,
        $hover ? 'card-hover' : '',
        $clickable ? 'card-clickable' : '',
        $image && $imagePosition === 'background' ? 'card-bg-image' : ''
    ])->filter()->implode(' ');
    
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} 
    {{ $attributes->merge([
        'class' => $classes,
        'href' => $href
    ]) }}
    @if($image && $imagePosition === 'background') 
        style="background-image: url('{{ $image }}');"
    @endif
>
    @if($header)
        <div class="card-header">
            {{ $header }}
        </div>
    @endif

    @if($image && $imagePosition === 'top')
        <div class="card-image">
            <img src="{{ $image }}" alt="{{ $imageAlt }}" class="img-fluid">
        </div>
    @endif

    <div class="card-body {{ $image && in_array($imagePosition, ['left', 'right']) ? 'card-body-flex card-body-' . $imagePosition : '' }}">
        @if($image && $imagePosition === 'left')
            <div class="card-image-side">
                <img src="{{ $image }}" alt="{{ $imageAlt }}" class="img-fluid">
            </div>
        @endif

        <div class="card-content">
            {{ $slot }}
        </div>

        @if($image && $imagePosition === 'right')
            <div class="card-image-side">
                <img src="{{ $image }}" alt="{{ $imageAlt }}" class="img-fluid">
            </div>
        @endif
    </div>

    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</{{ $tag }}>

<style>
.seferet-card {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Variants */
.seferet-card.card-variant-default {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
}

.seferet-card.card-variant-elevated {
    background: var(--surface-color);
    border: none;
}

.seferet-card.card-variant-outlined {
    background: transparent;
    border: 2px solid var(--primary-color);
}

.seferet-card.card-variant-filled {
    background: var(--surface-variant-color);
    border: none;
}

/* Elevation */
.seferet-card.card-elevation-none {
    box-shadow: none;
}

.seferet-card.card-elevation-sm {
    box-shadow: var(--shadow-sm);
}

.seferet-card.card-elevation-md {
    box-shadow: var(--shadow-md);
}

.seferet-card.card-elevation-lg {
    box-shadow: var(--shadow-lg);
}

.seferet-card.card-elevation-xl {
    box-shadow: var(--shadow-xl);
}

/* Padding */
.seferet-card.card-padding-none .card-body {
    padding: 0;
}

.seferet-card.card-padding-sm .card-body {
    padding: var(--spacing-sm);
}

.seferet-card.card-padding-md .card-body {
    padding: var(--spacing-md);
}

.seferet-card.card-padding-lg .card-body {
    padding: var(--spacing-lg);
}

/* Rounded corners */
.seferet-card.card-rounded-none {
    border-radius: 0;
}

.seferet-card.card-rounded-sm {
    border-radius: var(--border-radius-sm);
}

.seferet-card.card-rounded-md {
    border-radius: var(--border-radius-md);
}

.seferet-card.card-rounded-lg {
    border-radius: var(--border-radius-lg);
}

.seferet-card.card-rounded-full {
    border-radius: 50px;
}

/* Interactive states */
.seferet-card.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.seferet-card.card-clickable {
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.seferet-card.card-clickable:hover {
    color: inherit;
    text-decoration: none;
}

/* Card sections */
.card-header {
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
    background: var(--surface-variant-color);
    font-weight: 600;
    color: var(--text-color);
}

.card-body {
    flex: 1;
    padding: var(--spacing-md);
}

.card-body-flex {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
}

.card-body-left {
    flex-direction: row;
}

.card-body-right {
    flex-direction: row-reverse;
}

.card-content {
    flex: 1;
}

.card-footer {
    padding: var(--spacing-md);
    border-top: 1px solid var(--border-color);
    background: var(--surface-variant-color);
    margin-top: auto;
}

.card-image {
    position: relative;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: auto;
    display: block;
}

.card-image-side {
    flex: 0 0 auto;
    max-width: 120px;
}

.card-image-side img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: var(--border-radius-sm);
}

/* Background image variant */
.seferet-card.card-bg-image {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: var(--text-title-color);
    position: relative;
}

.seferet-card.card-bg-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
    z-index: 1;
}

.seferet-card.card-bg-image .card-body,
.seferet-card.card-bg-image .card-header,
.seferet-card.card-bg-image .card-footer {
    position: relative;
    z-index: 2;
}

/* Animation keyframes */
@keyframes cardSlideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.seferet-card {
    animation: cardSlideUp 0.3s ease-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body-flex {
        flex-direction: column;
    }
    
    .card-body-right {
        flex-direction: column;
    }
    
    .card-image-side {
        max-width: none;
        width: 100%;
    }
    
    .seferet-card.card-padding-md .card-body {
        padding: var(--spacing-sm);
    }
    
    .card-header,
    .card-footer {
        padding: var(--spacing-sm);
    }
}

/* Focus styles for accessibility */
.seferet-card.card-clickable:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}
</style>
