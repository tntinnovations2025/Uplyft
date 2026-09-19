<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $instituteBranding->name ?? config('app.name', 'UPLYFT') }} — Portal Authentication</title>

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ file_exists(public_path('favicon-32x32.png')) ? filemtime(public_path('favicon-32x32.png')) : 2 }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ file_exists(public_path('favicon-16x16.png')) ? filemtime(public_path('favicon-16x16.png')) : 2 }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ file_exists(public_path('favicon.ico')) ? filemtime(public_path('favicon.ico')) : 2 }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/uplyft-logo.png') }}?v={{ file_exists(public_path('images/uplyft-logo.png')) ? filemtime(public_path('images/uplyft-logo.png')) : 2 }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border-light: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            min-height: 100vh;
            width: 100%;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-main);
        }

        body, body.auth-portal-body {
            display: flex !important;
            align-items: flex-start !important;
            justify-content: flex-end !important;
            position: relative !important;
            background-color: #0f172a !important;
            background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.12) 0%, rgba(15, 23, 42, 0.28) 100%), url('{{ ($instituteBranding->bg_url ?? asset("images/default_campus_bg.jpg")) . (str_contains($instituteBranding->bg_url ?? "", "?") ? "&" : "?") . "v=" . (file_exists(public_path("images/default_campus_bg.jpg")) ? filemtime(public_path("images/default_campus_bg.jpg")) : 1) }}') !important;
            background-position: center center !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            padding: 55px 75px 30px 20px !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }

        /* Top-Left Institute Logo & Name Badge */
        .top-left-brand-header {
            position: fixed;
            top: 22px;
            left: 26px;
            z-index: 50;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 9999px;
            padding: 5px 16px 5px 6px;
            box-shadow: 
                0 10px 25px -4px rgba(15, 23, 42, 0.10),
                0 2px 6px rgba(15, 23, 42, 0.04);
            user-select: none;
        }

        .top-left-logo-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            border: 1.5px solid #D48A2E;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            padding: 1px;
        }

        .top-left-logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .top-left-logo-fallback {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #D48A2E 0%, #D48A2E 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 900;
        }

        .top-left-brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.2px;
            white-space: nowrap;
        }

        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            margin-top: 0;
        }

        .glass-card {
            background: #ffffff;
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 
                0 18px 36px -8px rgba(15, 23, 42, 0.16),
                0 3px 10px rgba(15, 23, 42, 0.05),
                inset 0 1px 0 #ffffff;
            border-radius: 14px;
            padding: 14px 16px;
            width: 100%;
            position: relative;
            overflow: hidden;
            animation: cardFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2.5px;
            background: linear-gradient(90deg, #D48A2E 0%, #E8CEAA 50%, #6366f1 100%);
        }

        @keyframes cardFadeIn {
            0% {
                opacity: 0;
                transform: scale(0.97) translateY(6px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @media (max-width: 768px) {
            body {
                justify-content: center;
                align-items: center;
                padding: 70px 14px 20px 14px;
            }
            .top-left-brand-header {
                top: 14px;
                left: 14px;
            }
            .auth-container {
                max-width: 100%;
                margin-top: 0;
            }
        }

        /* Default input padding: spacious left offset ensures placeholder and cursor NEVER collide with icon */
        .input-wrapper input,
        .input-wrapper .custom-input,
        .custom-input {
            width: 100% !important;
            padding-left: 34px !important;
            padding-right: 30px !important;
            box-sizing: border-box !important;
        }

        .input-wrapper .input-icon-left,
        .input-wrapper .input-icon {
            position: absolute !important;
            left: 10px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            width: 14px !important;
            height: 14px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            pointer-events: none !important;
            z-index: 5 !important;
            transition: opacity 0.12s ease, visibility 0.12s ease !important;
        }

        /* Hide input leading icon dynamically when input has value or browser autofill */
        .input-wrapper.has-value .input-icon,
        .input-wrapper.has-value .input-icon-left,
        .input-wrapper.has-value .input-icon-left svg,
        .input-wrapper input:-webkit-autofill ~ .input-icon,
        .input-wrapper input:-webkit-autofill ~ .input-icon-left,
        .input-wrapper input:autofill ~ .input-icon,
        .input-wrapper input:autofill ~ .input-icon-left,
        .input-wrapper input:not(:placeholder-shown) ~ .input-icon,
        .input-wrapper input:not(:placeholder-shown) ~ .input-icon-left,
        .input-wrapper:has(input:-webkit-autofill) .input-icon,
        .input-wrapper:has(input:-webkit-autofill) .input-icon-left,
        .input-wrapper:has(input:not(:placeholder-shown)) .input-icon,
        .input-wrapper:has(input:not(:placeholder-shown)) .input-icon-left {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* When value exists, adjust padding so text starts from comfortable left edge */
        .input-wrapper.has-value input,
        .input-wrapper.has-value .custom-input,
        .input-wrapper input:-webkit-autofill,
        .input-wrapper input:not(:placeholder-shown) {
            padding-left: 10px !important;
        }

        /* Detect WebKit autofill animation */
        @keyframes onAutoFillStart { from { opacity: 0.99; } to { opacity: 1; } }
        .input-wrapper input:-webkit-autofill {
            animation-name: onAutoFillStart;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateInputState(input) {
                const wrapper = input.closest('.input-wrapper');
                if (!wrapper) return;
                const isAutofilled = input.matches && (input.matches(':-webkit-autofill') || input.matches(':autofill'));
                if ((input.value && input.value.trim() !== '') || isAutofilled) {
                    wrapper.classList.add('has-value');
                } else {
                    wrapper.classList.remove('has-value');
                }
            }

            const inputs = document.querySelectorAll('.input-wrapper input');
            
            inputs.forEach(function(input) {
                updateInputState(input);
                ['input', 'change', 'keyup', 'keydown', 'focus', 'blur', 'animationstart'].forEach(function(evt) {
                    input.addEventListener(evt, function() { updateInputState(this); });
                });
            });

            // Polling check for Chrome/Edge autofill that doesn't trigger standard DOM events on page load
            let checks = 0;
            const interval = setInterval(function() {
                inputs.forEach(updateInputState);
                checks++;
                if (checks > 20) clearInterval(interval);
            }, 150);
        });
    </script>
</head>
<body class="auth-portal-body">
    {{-- Universal Multi-Tenant Branded Loader --}}
    @include('partials.institute-loader')

    {{-- Top Left Institute Logo & Name Badge --}}
    <div class="top-left-brand-header">
        <div class="top-left-logo-badge">
            <img src="{{ !empty($instituteBranding->logo_url) ? $instituteBranding->logo_url : asset('images/uplyft-logo.png') }}" 
                 alt="{{ $instituteBranding->name ?? 'Uplyft' }} Logo"
                 onerror="this.onerror=null; this.src='{{ asset('images/uplyft-logo.png') }}';" />
        </div>
        <span class="top-left-brand-name">{{ $instituteBranding->name ?? config('app.name', 'UPLYFT') }}</span>
    </div>

    <div class="auth-container">
        <div class="glass-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
