<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EngagementController extends Controller
{
    public function toggleWishlist(Request $request, string $category, string $product): RedirectResponse
    {
        $products = config("naturmarkt.categories.{$category}.products", []);
        abort_unless(collect($products)->contains('handle', $product), 404);
        $customerId = $request->session()->get('customer_user_id');
        $query = DB::table('wishlists')->where(compact('category', 'product'))->where('customer_user_id', $customerId);
        if ($query->exists()) $query->delete();
        else DB::table('wishlists')->insert(['customer_user_id' => $customerId, 'category' => $category, 'product' => $product, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Deine Wunschliste wurde aktualisiert.');
    }

    public function subscribeAvailability(Request $request, string $category, string $product): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);
        DB::table('availability_subscriptions')->updateOrInsert(
            ['email' => mb_strtolower($validated['email']), 'category' => $category, 'product' => $product],
            ['customer_user_id' => $request->session()->get('customer_user_id'), 'token' => hash('sha256', Str::uuid()), 'active' => true, 'notified_at' => null, 'created_at' => now(), 'updated_at' => now()]
        );
        return back()->with('availability_success', 'Wir informieren dich, sobald das Produkt wieder verfügbar ist.');
    }

    public function saveCartReminder(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:255'], 'cart' => ['required', 'string', 'max:50000'], 'consent' => ['accepted']]);
        $cart = json_decode($validated['cart'], true);
        abort_unless(is_array($cart) && count($cart), 422);
        DB::table('cart_reminders')->insert(['customer_user_id' => $request->session()->get('customer_user_id'), 'email' => mb_strtolower($validated['email']), 'cart' => json_encode($cart), 'token' => hash('sha256', Str::uuid()), 'remind_after' => now()->addDay(), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('reminder_success', 'Deine freiwillige Warenkorb-Erinnerung wurde aktiviert.');
    }

    public function unsubscribeReminder(string $token): RedirectResponse
    {
        DB::table('cart_reminders')->where('token', $token)->update(['unsubscribed_at' => now(), 'updated_at' => now()]);
        return redirect()->route('home')->with('success', 'Warenkorb-Erinnerungen wurden abbestellt.');
    }

    public static function sendDueReminders(): int
    {
        $reminders = DB::table('cart_reminders')->whereNull('sent_at')->whereNull('unsubscribed_at')->where('remind_after', '<=', now())->get();
        foreach ($reminders as $reminder) {
            $url = route('home').'?cart_reminder='.$reminder->token;
            Mail::raw("In deinem Naturmarkt-Warenkorb warten noch Produkte.\n\nWarenkorb öffnen: {$url}\n\nErinnerungen abbestellen: ".route('cart-reminders.unsubscribe', $reminder->token), fn ($message) => $message->to($reminder->email)->subject('Dein Naturmarkt-Warenkorb wartet'));
            DB::table('cart_reminders')->where('id', $reminder->id)->update(['sent_at' => now(), 'updated_at' => now()]);
        }
        return $reminders->count();
    }
}
