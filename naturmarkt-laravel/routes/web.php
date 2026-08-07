<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OwnerAuthController;
use Illuminate\Support\Facades\Route;
Route::get('/', HomeController::class)->name('home');
Route::get('/suche', [HomeController::class, 'search'])->name('search');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']));
Route::get('/produkte/honigsorten', [HomeController::class, 'honey'])->name('categories.honey');
Route::get('/produkte/honigmix', [HomeController::class, 'honeyMix'])->name('categories.honey-mix');
Route::get('/produkte/bienenprodukte', [HomeController::class, 'beeProducts'])->name('categories.bee-products');
Route::get('/produkte/cremes-salben', [HomeController::class, 'creamsAndSalves'])->name('categories.creams-salves');
Route::get('/produkte/kerzen', [HomeController::class, 'candles'])->name('categories.candles');
Route::get('/produkte/nahrungsergaenzungsmittel', [HomeController::class, 'supplements'])->name('categories.supplements');
Route::get('/produkte/seife', [HomeController::class, 'soaps'])->name('categories.soaps');
Route::get('/produkte/sirup', [HomeController::class, 'syrups'])->name('categories.syrups');
Route::get('/produkte/verschiedenes', [HomeController::class, 'miscellaneous'])->name('categories.miscellaneous');
Route::get('/produkte/halbedelsteine-co', [HomeController::class, 'gemstones'])->name('categories.gemstones');
Route::get('/produkte/{category}/{product}', [HomeController::class, 'product'])->name('products.show');
Route::post('/produkte/{category}/{product}/bewertungen', [HomeController::class, 'storeReview'])
    ->middleware('throttle:5,1')
    ->name('products.reviews.store');
Route::get('/warenkorb', [HomeController::class, 'cart'])->name('cart');
Route::get('/kasse', [HomeController::class, 'checkoutPage'])->name('checkout.page');
Route::get('/kontakt', [HomeController::class, 'contact'])->name('contact');
Route::get('/versand', [HomeController::class, 'shipping'])->name('shipping');
Route::get('/widerruf', [HomeController::class, 'returns'])->name('returns');
Route::get('/impressum', [HomeController::class, 'imprint'])->name('imprint');
Route::get('/datenschutz', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/agb', [HomeController::class, 'terms'])->name('terms');
Route::get('/besitzer', [OwnerAuthController::class, 'show'])->name('owner.login');
Route::post('/besitzer/registrieren', [OwnerAuthController::class, 'register'])->middleware('throttle:5,1')->name('owner.register');
Route::post('/besitzer/anmelden', [OwnerAuthController::class, 'login'])->middleware('throttle:5,1')->name('owner.authenticate');
Route::post('/besitzer/abmelden', [OwnerAuthController::class, 'logout'])->name('owner.logout');
Route::middleware(['owner.auth', 'throttle:60,1'])->prefix('admin')->group(function () {
    Route::get('/bestellungen', [HomeController::class, 'adminOrders'])->name('admin.orders');
    Route::post('/bestellungen/{id}/status', [HomeController::class, 'updateOrderStatus'])->name('admin.orders.status');
    Route::get('/produkte', [HomeController::class, 'adminProducts'])->name('admin.products');
    Route::post('/produkte/{category}/{product}', [HomeController::class, 'updateProduct'])->name('admin.products.update');
});
Route::post('/newsletter', [HomeController::class, 'newsletter'])->name('newsletter');
Route::post('/checkout', [HomeController::class, 'checkout'])->name('checkout');
Route::get('/danke', [HomeController::class, 'thankYou'])->name('checkout.thank-you');
