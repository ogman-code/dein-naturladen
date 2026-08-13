<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = ($id = $request->session()->get('customer_id')) ? DB::table('customers')->find($id) : null;
        if (! $customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Bitte melde dich zuerst an.');
        }
        if ($customer->is_blocked) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Dieses Kundenkonto wurde vorübergehend gesperrt.');
        }
        view()->share('currentCustomer', $customer);
        $request->attributes->set('customer', $customer);
        return $next($request);
    }
}
