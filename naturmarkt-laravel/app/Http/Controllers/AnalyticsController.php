<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => ['required', 'string', 'in:page_view,product_view,add_to_cart,checkout_started,order_completed'],
            'path' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
            'product' => ['nullable', 'string', 'max:160'],
        ]);

        DB::table('analytics_events')->where('created_at', '<', now()->subDays(90))->delete();

        DB::table('analytics_events')->insert([
            ...$validated,
            'visitor_hash' => hash('sha256', $request->session()->getId()),
            'created_at' => now(),
        ]);

        return response()->json(['stored' => true], 201);
    }

    public function dashboard(): View
    {
        $since = now()->subDays(30);
        $events = DB::table('analytics_events')->where('created_at', '>=', $since);
        $counts = (clone $events)->select('event', DB::raw('COUNT(*) as total'))->groupBy('event')->pluck('total', 'event');
        $uniqueVisitors = (clone $events)->distinct()->count('visitor_hash');
        $topProducts = (clone $events)->where('event', 'product_view')->select('category', 'product', DB::raw('COUNT(*) as views'))->groupBy('category', 'product')->orderByDesc('views')->limit(10)->get();
        $topPages = (clone $events)->where('event', 'page_view')->select('path', DB::raw('COUNT(*) as views'))->groupBy('path')->orderByDesc('views')->limit(10)->get();
        $daily = (clone $events)->selectRaw('DATE(created_at) as day, COUNT(*) as events, COUNT(DISTINCT visitor_hash) as visitors')->groupBy('day')->orderBy('day')->get();

        $views = (int) ($counts['page_view'] ?? 0);
        $productViews = (int) ($counts['product_view'] ?? 0);
        $cartAdds = (int) ($counts['add_to_cart'] ?? 0);
        $checkoutStarts = (int) ($counts['checkout_started'] ?? 0);
        $purchases = (int) ($counts['order_completed'] ?? 0);

        return view('admin-analytics', compact('uniqueVisitors', 'topProducts', 'topPages', 'daily') + [
            'stats' => compact('views', 'productViews', 'cartAdds', 'checkoutStarts', 'purchases'),
            'rates' => [
                'product_to_cart' => $productViews ? round($cartAdds / $productViews * 100, 1) : 0,
                'cart_to_checkout' => $cartAdds ? round($checkoutStarts / $cartAdds * 100, 1) : 0,
                'checkout_to_purchase' => $checkoutStarts ? round($purchases / $checkoutStarts * 100, 1) : 0,
            ],
        ]);
    }
}
