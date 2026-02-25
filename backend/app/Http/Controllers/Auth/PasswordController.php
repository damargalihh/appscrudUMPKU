<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $status = 'success';
        try {
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        } catch (\Throwable $e) {
            $status = 'failed';
        }
        \App\Helpers\LogActivityHelper::log('change_password', $request->user()->email, $status, $request->user());
        return back()->with('status', $status === 'success' ? 'password-updated' : 'password-update-failed');
    }
}
