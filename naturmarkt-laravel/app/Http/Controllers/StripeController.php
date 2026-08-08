<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeController extends Controller
{
    public function success(Request $request): RedirectResponse
    {
        abort_unless(config('naturmarkt.payments.stripe_enabled'), 404);
        $sessionId = $request->query('session_id');
        abort_unless(is_string($sessionId) && str_starts_with($sessionId, 'cs_'), 400);

        $stripeSession = (new StripeClient(config('naturmarkt.payments.stripe_secret')))
            ->checkout->sessions->retrieve($sessionId);

        abort_unless($stripeSession->payment_status === 'paid', 402, 'Die Zahlung ist noch nicht abgeschlossen.');
        $order = $this->fulfill($stripeSession);

        $request->session()->put('completed_order', [
            'id' => $order->id,
            'total' => (float) $order->total,
            'email' => $order->email,
            'payment_method' => 'Kreditkarte',
        ]);

        return redirect()->route('checkout.thank-you');
    }

    public function webhook(Request $request): JsonResponse
    {
        $secret = config('naturmarkt.payments.stripe_webhook_secret');
        abort_unless(filled($secret), 503);

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret,
            );
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            Log::warning('Ungültiger Stripe-Webhook.', ['error' => $exception->getMessage()]);

            return response()->json(['received' => false], 400);
        }

        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $this->fulfill($event->data->object);
        }

        if ($event->type === 'checkout.session.async_payment_failed') {
            DB::table('checkout_requests')
                ->where('stripe_session_id', $event->data->object->id)
                ->where('payment_status', '!=', 'Bezahlt')
                ->update(['payment_status' => 'Fehlgeschlagen', 'updated_at' => now()]);
        }

        return response()->json(['received' => true]);
    }

    private function fulfill(object $stripeSession): object
    {
        $orderId = (int) ($stripeSession->metadata->order_id ?? $stripeSession->client_reference_id ?? 0);
        $order = DB::table('checkout_requests')->where('id', $orderId)->where('stripe_session_id', $stripeSession->id)->first();
        abort_unless($order, 404);

        DB::transaction(function () use ($order): void {
            $updated = DB::table('checkout_requests')
                ->where('id', $order->id)
                ->where('payment_status', '!=', 'Bezahlt')
                ->update(['payment_status' => 'Bezahlt', 'updated_at' => now()]);

            if (! $updated) {
                return;
            }

            if ($order->coupon_id) DB::table('coupons')->where('id', $order->coupon_id)->increment('uses_count');

            foreach (json_decode($order->cart ?: '[]', true) as $item) {
                DB::table('product_overrides')
                    ->where('category_key', $item['category_key'])
                    ->where('product_handle', $item['product_handle'])
                    ->whereNotNull('stock')
                    ->decrement('stock', $item['quantity']);
            }
        });

        return DB::table('checkout_requests')->find($order->id);
    }
}
