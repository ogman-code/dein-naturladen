<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Elektronische Widerrufsfunktion nach § 356a BGB:
 * "Vertrag widerrufen" -> Angaben (Name, Vertrag, E-Mail) -> "Widerruf bestätigen" -> Eingangsbestätigung per E-Mail.
 * Ein Grund für den Widerruf wird bewusst nicht abgefragt.
 */
class WithdrawalController extends Controller
{
    public function show(Request $request): View
    {
        $customer = ($id = $request->session()->get('customer_id')) ? DB::table('customers')->find($id) : null;

        return view('withdrawal', [
            'prefill' => [
                'name' => old('name', $customer->name ?? ''),
                'email' => old('email', $customer->email ?? ''),
                'contract_reference' => old('contract_reference', $request->query('bestellung') ? 'Bestellung #'.(int) $request->query('bestellung') : ''),
            ],
            'legal' => config('naturmarkt.legal'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'contract_reference' => ['required', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:255'],
        ], [], [
            'contract_reference' => 'Angaben zur Bestellung',
        ]);

        $receivedAt = now();
        $orderId = preg_match('/#?\s*(\d{1,10})/', $data['contract_reference'], $m) ? (int) $m[1] : null;
        $orderExists = $orderId && DB::table('checkout_requests')->where('id', $orderId)->exists();

        $id = DB::table('contract_withdrawals')->insertGetId([
            'name' => $data['name'],
            'contract_reference' => $data['contract_reference'],
            'email' => mb_strtolower($data['email']),
            'checkout_request_id' => $orderExists ? $orderId : null,
            'customer_id' => $request->session()->get('customer_id'),
            'received_at' => $receivedAt,
            'created_at' => $receivedAt,
            'updated_at' => $receivedAt,
        ]);

        $withdrawal = DB::table('contract_withdrawals')->find($id);
        $legal = config('naturmarkt.legal');

        try {
            Mail::send('emails.withdrawal-receipt', compact('withdrawal', 'legal'), fn ($message) => $message
                ->to($withdrawal->email, $withdrawal->name)
                ->subject('Eingangsbestätigung deines Widerrufs'));
            DB::table('contract_withdrawals')->where('id', $id)->update(['receipt_sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Widerruf: Eingangsbestätigung konnte nicht gesendet werden.', ['withdrawal_id' => $id, 'error' => $e->getMessage()]);
        }

        if ($adminEmail = config('naturmarkt.admin_email')) {
            try {
                Mail::send('emails.withdrawal-receipt', compact('withdrawal', 'legal') + ['forShop' => true], fn ($message) => $message
                    ->to($adminEmail)
                    ->replyTo($withdrawal->email, $withdrawal->name)
                    ->subject('Neuer Widerruf eingegangen: '.$withdrawal->contract_reference));
            } catch (\Throwable $e) {
                Log::error('Widerruf: Benachrichtigung an den Shop fehlgeschlagen.', ['withdrawal_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('withdrawal.done')->with('withdrawal_id', $id);
    }

    public function done(Request $request): View|RedirectResponse
    {
        $withdrawal = ($id = $request->session()->get('withdrawal_id')) ? DB::table('contract_withdrawals')->find($id) : null;
        if (! $withdrawal) {
            return redirect()->route('withdrawal.form');
        }

        return view('withdrawal', ['done' => $withdrawal, 'legal' => config('naturmarkt.legal')]);
    }
}
