@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
    'suffix' => null,
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'size' => 'md', // sm, md, lg
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'input_' . uniqid());

    // Input height & padding sizes
    $sizeClasses = match($size) {
        'sm' => 'py-2 text-xs',
        'lg' => 'py-3.5 text-base',
        default => 'py-2.5 text-sm',
    };

    // Padding depending on presence of prefix or suffix icons
    $leftPadding = $icon ? 'pl-11' : 'pl-4';
    $rightPadding = $suffix ? 'pr-11' : 'pr-4';

    // State classes (Bright Daylight Mode)
    $stateClasses = $error
        ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 text-rose-900 placeholder-rose-400 bg-rose-50/50'
        : 'border-slate-300 focus:border-pink-500 focus:ring-pink-500/20 text-slate-900 placeholder-slate-400 bg-white hover:border-slate-400 shadow-sm';
@endphp

<div class="w-full flex flex-col gap-1.5 text-left">
    {{-- Label Section --}}
    @if($label)
        <label for="{{ $inputId }}" class="flex items-center justify-between text-xs font-bold text-slate-700 uppercase tracking-wider select-none">
            <span class="flex items-center gap-1">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 text-sm leading-none">*</span>
                @endif
            </span>
            @if($hint && !$error)
                <span class="text-[11px] font-normal text-slate-400 lowercase">{{ $hint }}</span>
            @endif
        </label>
    @endif

    {{-- Input Wrapper --}}
    <div class="relative rounded-xl overflow-hidden group">
        {{-- Left Icon Prefix --}}
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-pink-500 transition-colors">
                <i class="{{ $icon }} text-sm"></i>
            </div>
        @endif

        {{-- Main HTML Input --}}
        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => "w-full rounded-xl {$leftPadding} {$rightPadding} {$sizeClasses} {$stateClasses} outline-none transition-all duration-200 font-medium focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed"
            ]) }}
        />

        {{-- Right Suffix Slot / Icon --}}
        @if($suffix)
            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                {{ $suffix }}
            </div>
        @endif
    </div>

    {{-- Validation Error Feedback --}}
    @if($error)
        <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-0.5 animate-fadeIn">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
