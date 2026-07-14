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
            'orangTuaProfile' => $request->user()->isOrangTua() ? $request->user()->orangTuaProfile : null,
        ]);
    }

    /**
     * Update data profil orang tua (nama, hubungan, telepon, alamat).
     */
    public function updateOrangTuaProfile(Request $request): RedirectResponse
    {
        $profile = $request->user()->orangTuaProfile;
        abort_if(! $profile, 404);

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:200'],
            'hubungan'     => ['required', 'in:ayah,ibu,wali'],
            'no_telepon'   => ['nullable', 'string', 'max:15'],
            'alamat'       => ['nullable', 'string'],
        ]);

        $profile->update($validated);

        return Redirect::route('profile.edit')->with('status', 'orang-tua-profile-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
