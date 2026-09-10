@props([
    'variant' => 'default', // default, elevated, interactive, instagram, neon-cyan, neon-violet, neon-magenta, subtle, clean
    'hover' => 'lift', // lift, glow, none
    'glow' => 'none', // none, instagram, cyan, violet, blue, magenta, emerald
    'padding' => 'md', // none, sm, md, lg, xl
    'border' => true,
    'specular' => true,
    'header' => null,
    'footer' => null,
    'title' => null,
    'subtitle' => null,
    'icon' => null,
])

@php
    // Padding styles
    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
        'xl' => 'p-10',
        default => 'p-6',
    };

    // Base surface variants (Ergonomic Daylight Glass)
    $variantClasses = match($variant) {
        'elevated' => 'bg-white backdrop-blur-xl shadow-md border-slate-200/90 shadow-[inset_0_1px_0_rgba(255,255,255,1)]',
        'interactive' => 'bg-white hover:bg-slate-50/80 backdrop-blur-md cursor-pointer transition-all duration-200 border-slate-200/90 hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-500/5',
        'instagram', 'ig', 'primary' => 'bg-white backdrop-blur-md border-indigo-200 shadow-sm shadow-indigo-500/5',
        'neon-cyan' => 'bg-white backdrop-blur-md border-sky-200 shadow-sm shadow-sky-500/5',
        'neon-violet' => 'bg-white backdrop-blur-md border-violet-200 shadow-sm shadow-violet-500/5',
        'neon-magenta' => 'bg-white backdrop-blur-md border-indigo-200 shadow-sm shadow-indigo-500/5',
        'subtle' => 'bg-slate-50/80 backdrop-blur-sm border-slate-200/70',
        'clean' => 'bg-white border-transparent shadow-xs',
        default => 'bg-white backdrop-blur-md border-slate-200/90 shadow-2xs',
    };

    // Hover transformations
    $hoverClasses = match($hover) {
        'lift' => 'transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300',
        'glow' => 'transition-all duration-200 hover:shadow-md hover:shadow-indigo-500/10 hover:border-indigo-300',
        'none' => '',
        default => 'transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300',
    };

    // Ambient glow borders
    $glowClasses = match($glow) {
        'instagram', 'ig', 'pink', 'indigo' => 'shadow-sm ring-1 ring-indigo-500/20 shadow-indigo-500/5',
        'cyan', 'sky' => 'shadow-sm ring-1 ring-sky-500/20 shadow-sky-500/5',
        'violet' => 'shadow-sm ring-1 ring-violet-500/20 shadow-violet-500/5',
        'blue' => 'shadow-sm ring-1 ring-blue-500/20 shadow-blue-500/5',
        'magenta' => 'shadow-sm ring-1 ring-indigo-500/20 shadow-indigo-500/5',
        'emerald' => 'shadow-sm ring-1 ring-emerald-500/20 shadow-emerald-500/5',
        default => '',
    };

    $borderClass = $border ? 'border' : '';
@endphp

<div {{ $attributes->merge(['class' => "relative rounded-2xl {$borderClass} {$variantClasses} {$hoverClasses} {$glowClasses} overflow-hidden text-slate-900 group"]) }}>
    {{-- Specular Top Edge Light Reflection --}}
    @if($specular)
        <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none z-10"></div>
    @endif

    {{-- Optional Card Header --}}
    @if($title || $header || $icon)
        <div class="flex items-center justify-between border-b border-slate-200/80 px-6 py-4">
            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#fd1d1d] via-[#e1306c] to-[#833ab4] flex items-center justify-center text-white text-sm shadow-sm shadow-pink-500/20">
                        <i class="{{ $icon }}"></i>
                    </div>
                @endif
                <div>
                    @if($title)
                        <h3 class="font-display font-bold text-base text-slate-900 tracking-tight leading-none">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="text-xs text-slate-500 mt-1 font-medium">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @if($header)
                <div class="flex items-center gap-2">
                    {{ $header }}
                </div>
            @endif
        </div>
    @endif

    {{-- Card Main Body --}}
    <div class="{{ $paddingClasses }}">
        {{ $slot }}
    </div>

    {{-- Optional Card Footer --}}
    @if($footer)
        <div class="border-t border-slate-200/80 bg-slate-50/60 px-6 py-3.5 flex items-center justify-between text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endif
</div>
