<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OwnerAuthController extends Controller
{
    public function account(): View
    {
        return view('owner-account');
    }

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('owner_user_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('owner-auth', [
            'ownerExists' => DB::table('owner_users')->exists(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        abort_if(DB::table('owner_users')->exists(), 403, 'Das Besitzerkonto wurde bereits eingerichtet.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:owner_users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ]);

        $ownerId = DB::table('owner_users')->insertGetId([
            'name' => $validated['name'],
            'email' => mb_strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'last_login_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request->session()->regenerate();
        $request->session()->put('owner_user_id', $ownerId);

        return redirect()->route('admin.dashboard')->with('success', 'Dein Besitzerkonto wurde eingerichtet.');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $owner = DB::table('owner_users')->where('email', mb_strtolower($validated['email']))->first();

        if (! $owner || ! Hash::check($validated['password'], $owner->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'E-Mail-Adresse oder Passwort ist nicht korrekt.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('owner_user_id', $owner->id);
        DB::table('owner_users')->where('id', $owner->id)->update(['last_login_at' => now()]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('owner.login')->with('success', 'Du wurdest sicher abgemeldet.');
    }
}
