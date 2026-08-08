<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('customer_user_id')) {
            return redirect()->route('customer.account');
        }

        return view('customer-auth');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:customer_users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $customerId = DB::table('customer_users')->insertGetId([
            'name' => trim($validated['name']),
            'email' => mb_strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'last_login_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request->session()->regenerate();
        $request->session()->put('customer_user_id', $customerId);

        return redirect()->route('customer.account')->with('success', 'Dein Kundenkonto wurde erstellt.');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = DB::table('customer_users')->where('email', mb_strtolower($validated['email']))->first();

        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'E-Mail-Adresse oder Passwort ist nicht korrekt.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('customer_user_id', $customer->id);
        DB::table('customer_users')->where('id', $customer->id)->update(['last_login_at' => now()]);

        return redirect()->intended(route('customer.account'));
    }

    public function account(Request $request): View
    {
        $customer = DB::table('customer_users')->find($request->session()->get('customer_user_id'));
        abort_unless($customer, 404);
        $orders = DB::table('checkout_requests')
            ->where('customer_user_id', $customer->id)
            ->latest()
            ->get()
            ->map(function ($order) {
                $order->items = json_decode($order->cart ?: '[]', true) ?: [];

                return $order;
            });

        $wishlist = DB::table('wishlists')->where('customer_user_id', $customer->id)->latest()->get()->map(function ($item) {
            $category = config("naturmarkt.categories.{$item->category}");
            $product = collect($category['products'] ?? [])->firstWhere('handle', $item->product);
            return $product ? ['name' => $product['name'], 'category' => $item->category, 'product' => $item->product, 'url' => route('products.show', [$item->category, $item->product]), 'image' => $product['image'] ?? ($category['image'] ?? '')] : null;
        })->filter();

        return view('customer-account', compact('customer', 'orders', 'wishlist'));
    }

    public function update(Request $request): RedirectResponse
    {
        $customerId = $request->session()->get('customer_user_id');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:customer_users,email,'.$customerId],
            'phone' => ['nullable', 'string', 'max:80'],
            'street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'in:DE,AT,NL,LU'],
        ]);

        DB::table('customer_users')->where('id', $customerId)->update([
            ...$validated,
            'name' => trim($validated['name']),
            'email' => mb_strtolower($validated['email']),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Deine Daten wurden gespeichert.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('customer_user_id');
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Du wurdest abgemeldet.');
    }
}
