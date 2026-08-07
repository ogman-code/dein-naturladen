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
        $customerId = $request->session()->get('customer_user_id');
        $customer = $customerId ? DB::table('customer_users')->find($customerId) : null;

        if (! $customer) {
            $request->session()->forget('customer_user_id');

            return redirect()->route('customer.login')->with('error', 'Bitte melde dich zuerst an.');
        }

        view()->share('currentCustomer', $customer);

        return $next($request);
    }
}
