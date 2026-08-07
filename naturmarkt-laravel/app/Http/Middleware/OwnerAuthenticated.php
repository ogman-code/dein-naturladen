<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OwnerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $ownerId = $request->session()->get('owner_user_id');
        $owner = $ownerId ? DB::table('owner_users')->find($ownerId) : null;

        if (! $owner) {
            $request->session()->forget('owner_user_id');
            return redirect()->route('owner.login')->with('error', 'Bitte melde dich zuerst an.');
        }

        view()->share('currentOwner', $owner);

        return $next($request);
    }
}
