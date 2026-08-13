<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View { return view('customer-auth', ['mode' => 'login']); }
    public function showRegister(): View { return view('customer-auth', ['mode' => 'register']); }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
            'privacy_accepted' => ['accepted'],
            'company_website' => ['nullable', 'string', 'max:0'],
        ]);
        if (DB::table('owner_users')->where('email', mb_strtolower($data['email']))->exists()) {
            throw ValidationException::withMessages(['email' => 'Diese E-Mail-Adresse gehört zum Besitzerkonto. Bitte verwende den Login.']);
        }
        $id = DB::table('customers')->insertGetId([
            'name' => $data['name'], 'email' => mb_strtolower($data['email']),
            'password' => Hash::make($data['password']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $request->session()->regenerate();
        $request->session()->put('customer_id', $id);
        $this->sendVerification($id, $data['email'], $data['name']);
        return redirect()->route('customer.account')->with('success', 'Konto erstellt. Bitte bestätige deine E-Mail-Adresse.');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $email = mb_strtolower($data['email']);
        $owner = DB::table('owner_users')->where('email', $email)->first();
        if ($owner && Hash::check($data['password'], $owner->password)) {
            $request->session()->regenerate();
            $request->session()->forget('customer_id');
            $request->session()->put('owner_user_id', $owner->id);
            DB::table('owner_users')->where('id', $owner->id)->update(['last_login_at' => now()]);
            return redirect()->route('home')->with('success', 'Als Besitzer angemeldet. Der Adminbereich ist jetzt sichtbar.');
        }

        $customer = DB::table('customers')->where('email', $email)->first();
        if (! $customer || ! Hash::check($data['password'], $customer->password)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'E-Mail-Adresse oder Passwort ist nicht korrekt.']);
        }
        $request->session()->regenerate();
        $request->session()->forget('owner_user_id');
        $request->session()->put('customer_id', $customer->id);
        DB::table('customers')->where('id', $customer->id)->update(['last_login_at' => now()]);
        return redirect()->intended(route('customer.account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('customer_id');
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Du wurdest abgemeldet.');
    }

    public function verify(Request $request, int $customer): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        $record = DB::table('customers')->find($customer);
        abort_unless($record && hash_equals(sha1($record->email), (string) $request->query('hash')), 403);
        if (! $record->email_verified_at) {
            DB::table('customers')->where('id', $customer)->update(['email_verified_at' => now(), 'updated_at' => now()]);
            $this->mail($record->email, 'Willkommen bei Naturmarkt', 'emails.customer-welcome', ['customer' => $record]);
        }
        $request->session()->put('customer_id', $customer);
        return redirect()->route('customer.account')->with('success', 'E-Mail bestätigt – herzlich willkommen!');
    }

    public function resend(Request $request): RedirectResponse
    {
        $customer = $request->attributes->get('customer');
        if (! $customer->email_verified_at) $this->sendVerification($customer->id, $customer->email, $customer->name);
        return back()->with('success', 'Bestätigungslink wurde erneut gesendet.');
    }

    public function showForgot(): View { return view('customer-forgot'); }

    public function forgot(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = mb_strtolower($data['email']);
        if ($customer = DB::table('customers')->where('email', $email)->first()) {
            $token = Str::random(64);
            DB::table('customer_password_reset_tokens')->updateOrInsert(['email' => $email], ['token' => Hash::make($token), 'created_at' => now()]);
            $url = route('customer.password.reset', ['token' => $token, 'email' => $email]);
            $this->mail($email, 'Passwort zurücksetzen', 'emails.customer-reset', compact('customer', 'url'));
        }
        return back()->with('success', 'Wenn ein Konto existiert, wurde eine E-Mail versendet.');
    }

    public function showReset(Request $request, string $token): View { return view('customer-reset', ['token' => $token, 'email' => $request->query('email')]); }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'token' => ['required'], 'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()]]);
        $reset = DB::table('customer_password_reset_tokens')->where('email', mb_strtolower($data['email']))->first();
        if (! $reset || now()->diffInMinutes($reset->created_at) > 60 || ! Hash::check($data['token'], $reset->token)) return back()->withErrors(['email' => 'Der Link ist ungültig oder abgelaufen.']);
        DB::table('customers')->where('email', mb_strtolower($data['email']))->update(['password' => Hash::make($data['password']), 'updated_at' => now()]);
        DB::table('customer_password_reset_tokens')->where('email', mb_strtolower($data['email']))->delete();
        return redirect()->route('customer.login')->with('success', 'Passwort wurde geändert.');
    }

    private function sendVerification(int $id, string $email, string $name): void
    {
        $url = URL::temporarySignedRoute('customer.verify', now()->addMinutes(60), ['customer' => $id, 'hash' => sha1(mb_strtolower($email))]);
        $this->mail($email, 'E-Mail-Adresse bestätigen', 'emails.customer-verify', compact('name', 'url'));
    }

    private function mail(string $to, string $subject, string $view, array $data): void
    {
        try { Mail::send($view, $data, fn ($message) => $message->to($to)->subject($subject)); }
        catch (\Throwable $e) { Log::warning('Kunden-E-Mail fehlgeschlagen.', ['to' => $to, 'error' => $e->getMessage()]); }
    }
}
