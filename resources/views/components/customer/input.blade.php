{{-- Custom Input Component - Flutter Design Match --}}
@props([
    'type' => 'text',
    'name' => '',
    'id' => '',
    'value' => '',
    'placeholder' => '',
    'label' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'size' => 'md', // sm, md, lg
    'variant' => 'outlined', // filled, outlined
    'floating' => false, // floating label
    'rows' => 3, // for textarea
    'options' => [], // for select
    'multiple' => false, // for select
    'min' => null,
    'max' => null,
    'step' => null,
    'maxlength' => null,
    'pattern' => null
])

@php
    $inputId = $id ?: $name ?: uniqid('input_');
    $hasError = !empty($error) || $errors->has($name);
    $errorMessage = $error ?: ($errors->has($name) ? $errors->first($name) : '');
    
    $wrapperClasses = collect([
        'seferet-input-wrapper',
        'input-size-' . $size,
        'input-variant-' . $variant,
        $floating ? 'input-floating' : '',
        $hasError ? 'has-error' : '',
        $disabled ? 'is-disabled' : '',
        $icon ? 'has-icon-' . $iconPosition : ''
    ])->filter()->implode(' ');
    
    $inputClasses = collect([
        'seferet-input',
        $hasError ? 'is-invalid' : ''
    ])->filter()->implode(' ');
    
    $inputAttributes = [
        'class' => $inputClasses,
        'id' => $inputId,
        'name' => $name,
        'placeholder' => $floating ? '' : $placeholder,
        'required' => $required,
        'disabled' => $disabled,
        'readonly' => $readonly,
        'maxlength' => $maxlength,
        'pattern' => $pattern
    ];
    
    if (in_array($type, ['number', 'range'])) {
        $inputAttributes['min'] = $min;
        $inputAttributes['max'] = $max;
        $inputAttributes['step'] = $step;
    }
@endphp

<div class="{{ $wrapperClasses }}">
    @if($label && !$floating)
        <label for="{{ $inputId }}" class="input-label {{ $required ? 'required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <div class="input-field">
        @if($icon && $iconPosition === 'left')
            <div class="input-icon input-icon-left">
                <i class="{{ $icon }}"></i>
            </div>
        @endif

        @if($type === 'textarea')
            <textarea 
                {{ $attributes->merge($inputAttributes) }}
                rows="{{ $rows }}"
            >{{ old($name, $value) }}</textarea>
        @elseif($type === 'select')
            <select 
                {{ $attributes->merge($inputAttributes) }}
                @if($multiple) multiple @endif
            >
                @foreach($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" 
                        @if(old($name, $value) == $optValue || (is_array(old($name, $value)) && in_array($optValue, old($name, $value)))) selected @endif
                    >
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @else
            <input 
                type="{{ $type }}"
                {{ $attributes->merge($inputAttributes) }}
                value="{{ old($name, $value) }}"
            >
        @endif

        @if($floating && $label)
            <label for="{{ $inputId }}" class="floating-label {{ $required ? 'required' : '' }}">
                {{ $label }}
            </label>
        @endif

        @if($icon && $iconPosition === 'right')
            <div class="input-icon input-icon-right">
                <i class="{{ $icon }}"></i>
            </div>
        @endif

        @if($type === 'password')
            <button type="button" class="input-toggle-password" onclick="togglePasswordVisibility('{{ $inputId }}')">
                <i class="fas fa-eye" id="toggle-icon-{{ $inputId }}"></i>
            </button>
        @endif
    </div>

    @if($help && !$hasError)
        <div class="input-help">
            <i class="fas fa-info-circle me-1"></i>
            {{ $help }}
        </div>
    @endif

    @if($hasError)
        <div class="input-error">
            <i class="fas fa-exclamation-triangle me-1"></i>
            {{ $errorMessage }}
        </div>
    @endif
</div>

<style>
.seferet-input-wrapper {
    position: relative;
    margin-bottom: var(--spacing-md);
}

.input-label {
    display: block;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: var(--spacing-xs);
    font-size: 0.9rem;
}

.input-label.required::after {
    content: '*';
    color: var(--error-color);
    margin-left: 4px;
}

.input-field {
    position: relative;
    display: flex;
    align-items: center;
}

.seferet-input {
    width: 100%;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius-md);
    background: var(--surface-color);
    color: var(--text-color);
    font-family: var(--font-primary);
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}

.seferet-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.seferet-input::placeholder {
    color: var(--text-secondary-color);
}

/* Size variants */
.input-size-sm .seferet-input {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    min-height: 36px;
}

.input-size-md .seferet-input {
    padding: 0.75rem 1rem;
    font-size: 1rem;
    min-height: 44px;
}

.input-size-lg .seferet-input {
    padding: 1rem 1.25rem;
    font-size: 1.125rem;
    min-height: 52px;
}

/* Variant styles */
.input-variant-filled .seferet-input {
    background: var(--surface-variant-color);
    border: 2px solid transparent;
    border-bottom: 2px solid var(--border-color);
    border-radius: var(--border-radius-md) var(--border-radius-md) 0 0;
}

.input-variant-filled .seferet-input:focus {
    border-bottom-color: var(--primary-color);
    background: var(--surface-color);
}

.input-variant-outlined .seferet-input {
    background: var(--surface-color);
    border: 2px solid var(--border-color);
}

/* Icon styles */
.input-icon {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary-color);
    z-index: 2;
    pointer-events: none;
}

.input-size-sm .input-icon {
    width: 36px;
    height: 36px;
}

.input-size-md .input-icon {
    width: 44px;
    height: 44px;
}

.input-size-lg .input-icon {
    width: 52px;
    height: 52px;
}

.input-icon-left {
    left: 0;
}

.input-icon-right {
    right: 0;
}

.has-icon-left .seferet-input {
    padding-left: 2.5rem;
}

.has-icon-right .seferet-input {
    padding-right: 2.5rem;
}

/* Password toggle */
.input-toggle-password {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    color: var(--text-secondary-color);
    cursor: pointer;
    padding: 0.25rem;
    z-index: 3;
    transition: color 0.2s ease;
}

.input-toggle-password:hover {
    color: var(--primary-color);
}

/* Floating label */
.input-floating .floating-label {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: var(--surface-color);
    color: var(--text-secondary-color);
    font-size: 1rem;
    font-weight: 400;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
    padding: 0 0.25rem;
}

.input-floating .floating-label.required::after {
    content: '*';
    color: var(--error-color);
    margin-left: 4px;
}

.input-floating .seferet-input:focus + .floating-label,
.input-floating .seferet-input:not(:placeholder-shown) + .floating-label,
.input-floating .seferet-input[value]:not([value=""]) + .floating-label {
    top: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--primary-color);
}

.input-floating.has-icon-left .floating-label {
    left: 2.5rem;
}

/* Error and help states */
.has-error .seferet-input {
    border-color: var(--error-color);
}

.has-error .seferet-input:focus {
    border-color: var(--error-color);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.input-error {
    display: flex;
    align-items: center;
    color: var(--error-color);
    font-size: 0.875rem;
    margin-top: var(--spacing-xs);
}

.input-help {
    display: flex;
    align-items: center;
    color: var(--text-secondary-color);
    font-size: 0.875rem;
    margin-top: var(--spacing-xs);
}

/* Disabled state */
.is-disabled .seferet-input {
    background: var(--disabled-bg-color);
    color: var(--disabled-text-color);
    cursor: not-allowed;
    opacity: 0.6;
}

.is-disabled .input-label,
.is-disabled .floating-label {
    color: var(--disabled-text-color);
}

/* Select specific styles */
select.seferet-input {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

/* Textarea specific styles */
textarea.seferet-input {
    resize: vertical;
    min-height: auto;
}

/* Animation for validation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.has-error .input-field {
    animation: shake 0.5s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .input-size-lg .seferet-input {
        padding: 0.875rem 1rem;
        font-size: 1rem;
    }
    
    .input-size-md .seferet-input {
        padding: 0.625rem 0.875rem;
        font-size: 0.9rem;
    }
}
</style>

<script>
// Password visibility toggle function
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('toggle-icon-' + inputId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Initialize floating labels on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for pre-filled inputs and update floating labels
    document.querySelectorAll('.input-floating .seferet-input').forEach(input => {
        if (input.value) {
            input.classList.add('has-value');
        }
        
        input.addEventListener('input', function() {
            if (this.value) {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
    });
});
</script>
