{{-- ========================================================================= --}}
{{-- UNIVERSAL DYNAMIC MULTI-TENANT BRAND HEADER (TOP-LEFT SIDEBAR & APP BAR)  --}}
{{-- Dynamically renders active Institute's logo_url, or fallback UPLYFT brand --}}
{{-- ========================================================================= --}}
@php
    $branding = $instituteBranding ?? (object)[
        'is_tenant'        => false,
        'name'             => config('app.name', 'UPLYFT'),
        'logo_url'         => null,
        'icon_url'         => null,
        'has_custom_logo'  => false,
        'has_custom_icon'  => false,
        'initial'          => 'U',
    ];

    // Determine contextual role subtitle
    $roleSubtitle = 'ACADEMIC LMS';
    $isGlobalAdmin = false;
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isGlobalAdmin()) {
            $isGlobalAdmin = true;
            $roleSubtitle = 'GLOBAL GOVERNANCE';
        } elseif ($user->isPrincipal()) {
            $roleSubtitle = 'EXECUTIVE PRINCIPAL SUITE';
        } elseif ($user->isTeacher()) {
            $roleSubtitle = $user->staff_role ? strtoupper($user->staff_role . ' WORKSPACE') : 'STAFF PORTAL';
        } elseif ($user->isStudent()) {
            $roleSubtitle = 'STUDENT WORKSPACE';
        }
    }
@endphp

<div class="brand-header-container p-3.5 border-b border-[#2A2C30] flex items-center gap-3 relative bg-[#17191C]">
    @if(!empty($branding->logo_url))
        {{-- Custom Institute Logo (Larger Display) --}}
        <div class="brand-logo-img-wrapper flex-shrink-0 w-11 h-11 rounded-xl bg-[#2A2C30] border border-[#3E4249] p-1 flex items-center justify-center overflow-hidden transition-transform hover:scale-105">
            <img src="{{ $branding->logo_url }}" 
                 alt="{{ $branding->name }} Logo" 
                 class="w-full h-full object-contain rounded-lg"
                 onerror="this.style.display='none'; document.getElementById('brand-fallback-badge-{{ $branding->initial }}').style.display='flex';" />
        </div>
        
        {{-- Brand Text & Contextual Institute Display --}}
        <div class="min-w-0 flex-1 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                <span class="font-extrabold text-[#F9F8F5] tracking-tight text-[15px] font-display">
                    UPLYFT
                </span>
                @if($branding->is_tenant && $branding->name !== 'UPLYFT')
                    <span class="text-[#8A5A10] font-bold text-xs">—</span>
                    <span class="font-bold text-[#F0B45D] text-[13px] font-display truncate max-w-[130px]" title="{{ $branding->name }}">
                        {{ $branding->name }}
                    </span>
                @endif
            </div>
            {{-- <div class="text-[9.5px] font-extrabold text-[#8A877E] tracking-wider uppercase mt-0.5">
                {{ $roleSubtitle }}
            </div> --}}
        </div>

        {{-- Hidden fallback in case the image fails to load --}}
        <div id="brand-fallback-badge-{{ $branding->initial }}" style="display:none;" class="items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-[#D48A2E] text-[#1A1200] flex items-center justify-center font-extrabold text-lg border border-[#F0B45D] overflow-hidden flex-shrink-0">
                <span class="font-display">{{ $branding->initial }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                    <span class="font-extrabold text-[#F9F8F5] tracking-tight text-[15px] font-display">UPLYFT</span>
                    @if($branding->is_tenant && $branding->name !== 'UPLYFT')
                        <span class="text-[#8A5A10] font-bold text-xs">—</span>
                        <span class="font-bold text-[#F0B45D] text-[13px] font-display truncate max-w-[130px]">{{ $branding->name }}</span>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- Default UPLYFT / Institute Initial Styling --}}
        <div class="w-11 h-11 rounded-xl bg-[#D48A2E] text-[#1A1200] flex items-center justify-center font-extrabold text-lg border border-[#F0B45D] relative overflow-hidden flex-shrink-0">
            <span class="relative z-10 font-display">{{ $branding->initial ?? 'U' }}</span>
        </div>
        <div class="min-w-0 flex-1 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                <span class="font-extrabold text-[#F9F8F5] tracking-tight text-[15px] font-display">
                    UPLYFT
                </span>
                @if($branding->is_tenant && $branding->name !== 'UPLYFT')
                    <span class="text-[#8A5A10] font-bold text-xs">—</span>
                    <span class="font-bold text-[#F0B45D] text-[13px] font-display truncate max-w-[130px]" title="{{ $branding->name }}">
                        {{ $branding->name }}
                    </span>
                @endif
            </div>
            {{-- <div class="text-[9.5px] font-extrabold text-[#8A877E] tracking-wider uppercase mt-0.5">
                {{ $roleSubtitle }}
            </div> --}}
        </div>
    @endif
</div>
