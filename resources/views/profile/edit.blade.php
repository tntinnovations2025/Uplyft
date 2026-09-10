@extends(auth()->user()->isGlobalAdmin() ? 'global-admin.layouts.app' : (auth()->user()->isPrincipal() ? 'principal.layouts.app' : 'layouts.app'))

@section('title', 'My Profile Settings')
@section('page-title', 'My Account Profile & Security')
@section('page-subtitle', 'Personal information, password change and account security')

@section('content')
<div style="max-width: 880px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    @if(auth()->check() && auth()->user()->isGlobalAdmin())
        <!-- Card: UPLYFT Master Platform Logo & Global Branding -->
        <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
            @include('profile.partials.update-platform-logo-form')
        </div>
    @endif

    <!-- Card 1: Profile Information -->
    <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        @include('profile.partials.update-profile-information-form')
    </div>

    <!-- Card 2: Account Security & Email -->
    <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        @include('profile.partials.update-account-security-form')
    </div>

    <!-- Card 3: Password Update -->
    <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        @include('profile.partials.update-password-form')
    </div>

</div>
@endsection
