<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * Personal profile details (Name & Email) cannot be changed by the
     * account holder themselves. Only Principals, Global Admins, or
     * delegated staff with the `staff` edit permission may update them
     * (from Institute Administration).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isPrincipal() && ! $user->isGlobalAdmin() && ! $user->hasPermission('staff', 'edit')) {
            return Redirect::route('profile.edit')->with('error', '🔒 Your profile details (Name & Email) can only be updated by your Principal or Institute Administration.');
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the UPLYFT master platform logo (Global Admin only).
     */
    public function updatePlatformLogo(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isGlobalAdmin()) {
            abort(403, 'Only Global Admin can update the master platform logo.');
        }

        if ($request->boolean('remove_logo')) {
            $existing = \App\Models\PlatformSetting::get('platform_logo_path');
            if ($existing) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existing);
            }
            \App\Models\PlatformSetting::forget('platform_logo_path');
            return Redirect::route('profile.edit')->with('success', 'UPLYFT master platform logo removed. System default brand restored.');
        }

        if ($request->filled('cropped_logo') && str_starts_with($request->input('cropped_logo'), 'data:image')) {
            $dataUri = $request->input('cropped_logo');
            if (str_contains($dataUri, ',')) {
                [$meta, $encoded] = explode(',', $dataUri, 2);
                $imageData = base64_decode($encoded);
                if ($imageData !== false) {
                    $filename = 'platform-logo/uplyft_master_logo_' . time() . '.png';
                    $existing = \App\Models\PlatformSetting::get('platform_logo_path');
                    if ($existing) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existing);
                    }
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $imageData);
                    \App\Models\PlatformSetting::set('platform_logo_path', $filename);
                    return Redirect::route('profile.edit')->with('success', '✨ UPLYFT master platform logo updated successfully!');
                }
            }
        } elseif ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,jpg,png,svg,webp|max:2048',
            ]);
            $filename = $request->file('logo')->store('platform-logo', 'public');
            $existing = \App\Models\PlatformSetting::get('platform_logo_path');
            if ($existing) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existing);
            }
            \App\Models\PlatformSetting::set('platform_logo_path', $filename);
            return Redirect::route('profile.edit')->with('success', '✨ UPLYFT master platform logo updated successfully!');
        }

        return Redirect::route('profile.edit')->with('error', 'Please select or crop an image.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
