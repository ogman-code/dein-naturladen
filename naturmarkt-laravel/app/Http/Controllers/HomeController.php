<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    private ?array $catalogCache = null;
    private ?array $overrideCache = null;
    public function __invoke(): View
    {
        $categories = $this->homeCategories();
        $products = $this->featuredProducts();

        return view('home', compact('categories', 'products'));
    }

    public function honey(): View
    {
        return $this->category('honigsorten');
    }

    public function honeyMix(): View
    {
        return $this->category('honigmix');
    }

    public function beeProducts(): View
    {
        return $this->category('bienenprodukte');
    }

    public function creamsAndSalves(): View
    {
        return $this->category('cremes-salben');
    }

    public function candles(): View
    {
        return $this->category('kerzen');
    }

    public function supplements(): View
    {
        return $this->category('nahrungsergaenzungsmittel');
    }

    public function soaps(): View
    {
        return $this->category('seife');
    }

    public function syrups(): View
    {
        return $this->category('sirup');
    }

    public function miscellaneous(): View
    {
        return $this->category('verschiedenes');
    }

    public function gemstones(): View
    {
        return $this->category('halbedelsteine-co');
    }

    public function product(string $category, string $product): View
    {
        $categoryData = $this->findCategory($category);
        $productData = collect($categoryData['products'])
            ->firstWhere('handle', $product);

        abort_unless($productData, 404);

        $relatedProducts = collect($categoryData['products'])
            ->reject(fn (array $item) => $item['handle'] === $productData['handle'])
            ->take(4)
            ->values()
            ->all();

        $enrichedProduct = $this->enrichProduct($productData, $categoryData);
        $reviews = DB::table('product_reviews')->join('customers','customers.id','=','product_reviews.customer_id')->where(['category_key'=>$category,'product_handle'=>$product,'status'=>'approved'])->select('product_reviews.*','customers.name')->latest('product_reviews.created_at')->get();
        $customerId = session('customer_id');

        return view('product', [
            'category' => $categoryData,
            'product' => $enrichedProduct,
            'relatedProducts' => array_map(fn (array $item) => $this->enrichProduct($item, $categoryData), $relatedProducts),
            'productSchema' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $enrichedProduct['name'],
                'image' => [$enrichedProduct['image']],
                'description' => $enrichedProduct['description'],
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'EUR',
                    'price' => (float) str_replace(',', '.', str_replace('.', '', str_replace(' EUR', '', $enrichedProduct['price']))),
                    'availability' => $enrichedProduct['active'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url' => $enrichedProduct['url'],
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'reviews' => $reviews,
            'averageRating' => $reviews->count() ? round($reviews->avg('rating'),1) : null,
            'wishlisted' => $customerId ? DB::table('wishlists')->where(['customer_id'=>$customerId,'category_key'=>$category,'product_handle'=>$product])->exists() : false,
            'canReview' => $customerId ? DB::table('checkout_requests')->where('customer_id',$customerId)->where('cart','like','%"product_handle":"'.$product.'"%')->exists() : false,
        ]);
    }

    public function cart(): View
    {
        return view('cart', [
            'categories' => $this->homeCategories(),
        ]);
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q'));
        $products = collect($this->catalog())
            ->flatMap(function (array $category) {
                $category = $this->enrichCategory($category);
                return collect($category['products'])->map(fn (array $product) => $this->enrichProduct($product, $category));
            })
            ->when($query !== '', fn ($items) => $items->filter(
                fn (array $product) => Str::contains(Str::lower($product['search'].' '.$product['description']), Str::lower($query))
            ))
            ->values()
            ->all();

        return view('search', [
            'query' => $query,
            'products' => $products,
            'categories' => $this->homeCategories(),
        ]);
    }

    public function searchSuggestions(Request $request)
    {
        $query = Str::lower(trim((string) $request->query('q')));
        if (mb_strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $products = collect($this->catalog())->flatMap(function (array $category) {
            $category = $this->enrichCategory($category);
            return collect($category['products'])->map(fn (array $product) => $this->enrichProduct($product, $category));
        });

        $suggestions = $products->map(function (array $product) use ($query) {
            $name = Str::lower($product['name']);
            $contains = Str::contains($name.' '.$product['category'], $query);
            $distance = levenshtein($query, mb_substr($name, 0, max(mb_strlen($query), 1)));
            return ['product' => $product, 'score' => $contains ? 0 : $distance + 10];
        })->filter(fn (array $item) => $item['score'] === 0 || $item['score'] <= 13)
          ->sortBy('score')->take(6)->map(fn (array $item) => [
              'name' => $item['product']['name'],
              'category' => $item['product']['category'],
              'price' => $item['product']['price'],
              'image' => $item['product']['image'],
              'url' => $item['product']['url'],
          ])->values();

        return response()->json(['suggestions' => $suggestions], 200, ['Cache-Control' => 'public, max-age=60']);
    }

    public function sitemap()
    {
        $urls = collect([
            route('home'), route('cart'), route('contact'), route('shipping'),
            route('returns'), route('imprint'), route('privacy'), route('terms'),
        ]);

        foreach ($this->catalog() as $category) {
            $category = $this->enrichCategory($category);
            $urls->push($category['url']);
            foreach ($category['products'] as $product) {
                $urls->push(route('products.show', [$category['key'], $product['handle']]));
            }
        }

        $xml = view('sitemap', ['urls' => $urls->unique()->values()])->render();
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function checkoutPage(): View
    {
        $customer = ($id = session('customer_id')) ? DB::table('customers')->find($id) : null;
        $address = $customer ? DB::table('customer_addresses')->where('customer_id', $customer->id)->orderByDesc('is_default')->first() : null;
        return view('checkout', [
            'categories' => $this->homeCategories(),
            'shippingCountries' => config('naturmarkt.shipping.countries', []),
            'payments' => config('naturmarkt.payments'),
            'customer' => $customer,
            'savedAddress' => $address,
        ]);
    }

    public function thankYou(): View
    {
        return view('thank-you', [
            'categories' => $this->homeCategories(),
            'order' => session('completed_order'),
        ]);
    }

    public function contact(): View
    {
        return $this->infoPage('Kontakt', 'Fragen zu Produkten, Versand oder deiner Bestellung? Schreibe uns eine Nachricht, wir melden uns persönlich zurück.');
    }

    public function shipping(): View
    {
        return $this->infoPage('Versand', 'Wir verpacken deine Naturprodukte sorgfältig. Die Versandkosten werden im Warenkorb automatisch aus Gesamtgewicht und Lieferland berechnet. Ab 60 EUR Bestellwert ist der Versand kostenlos.');
    }

    public function returns(): View
    {
        return $this->legalPage('Widerruf', [
            ['title' => 'Widerrufsrecht', 'text' => 'Verbraucher haben grundsätzlich das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen. Die Frist beginnt, sobald du oder eine von dir benannte Person die Ware erhalten hat.'],
            ['title' => 'Widerruf erklären', 'text' => 'Sende eine eindeutige Erklärung per E-Mail oder Post an die im Impressum genannte Adresse. Nenne möglichst Bestellnummer, Ware, Bestelldatum, Namen und Anschrift.'],
            ['title' => 'Folgen des Widerrufs', 'text' => 'Nach einem wirksamen Widerruf erstatten wir die erhaltenen Zahlungen einschließlich der Kosten der günstigsten Standardlieferung. Die Rückzahlung kann bis zum Eingang der Ware oder bis zu deinem Versandnachweis zurückgehalten werden.'],
            ['title' => 'Rücksendung und Ausnahmen', 'text' => 'Die Ware ist spätestens binnen vierzehn Tagen nach dem Widerruf zurückzusenden. Bei entsiegelten Hygiene- oder Gesundheitswaren kann das Widerrufsrecht gesetzlich ausgeschlossen sein.'],
        ], 'Vor Verwendung durch eine Rechtsberatung prüfen lassen.');
    }

    public function imprint(): View
    {
        $legal = config('naturmarkt.legal');
        return $this->legalPage('Impressum', [
            ['title' => 'Angaben gemäß § 5 DDG', 'text' => "{$legal['business_name']}\nInhaber/in: {$legal['owner']}\n{$legal['street']}\n{$legal['city']}"],
            ['title' => 'Kontakt', 'text' => "E-Mail: {$legal['email']}\nTelefon: {$legal['phone']}"],
            ['title' => 'Verantwortlich für Inhalte', 'text' => "{$legal['owner']}\nAnschrift wie oben"],
            ['title' => 'Verbraucherstreitbeilegung', 'text' => 'Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen, sofern keine gesetzliche Verpflichtung besteht.'],
        ], 'Alle Angaben in eckigen Klammern müssen durch echte Unternehmensdaten ersetzt werden.');
    }

    public function privacy(): View
    {
        $legal = config('naturmarkt.legal');
        return $this->legalPage('Datenschutz', [
            ['title' => 'Verantwortliche Stelle', 'text' => "{$legal['business_name']}, {$legal['street']}, {$legal['city']}\nE-Mail: {$legal['email']}"],
            ['title' => 'Bestellungen und Kontakt', 'text' => 'Wir verarbeiten Kontakt-, Liefer- und Bestelldaten zur Vertragsanbahnung und Vertragsabwicklung gemäß Art. 6 Abs. 1 lit. b DSGVO. Gesetzliche Aufbewahrungspflichten bleiben unberührt.'],
            ['title' => 'Warenkorb und Einstellungen', 'text' => 'Warenkorb und Datenschutzentscheidung werden ausschließlich im lokalen Speicher deines Browsers gespeichert. Es findet kein Werbe- oder Analyse-Tracking statt.'],
            ['title' => 'Empfänger', 'text' => 'Daten werden nur an erforderliche Zahlungs-, Hosting-, E-Mail- und Versanddienstleister weitergegeben. Die konkret eingesetzten Anbieter müssen vor dem Verkaufsstart ergänzt werden.'],
            ['title' => 'Deine Rechte', 'text' => 'Du hast insbesondere Rechte auf Auskunft, Berichtigung, Löschung, Einschränkung, Datenübertragbarkeit und Widerspruch sowie ein Beschwerderecht bei einer Datenschutzaufsichtsbehörde.'],
        ], 'Die tatsächlich eingesetzten Dienstleister müssen vor dem Verkaufsstart konkret ergänzt werden.');
    }

    public function terms(): View
    {
        return $this->legalPage('Allgemeine Geschäftsbedingungen', [
            ['title' => 'Geltungsbereich', 'text' => 'Diese Bedingungen gelten für Bestellungen von Verbrauchern über diesen Onlineshop. Abweichende Vereinbarungen bedürfen der ausdrücklichen Bestätigung.'],
            ['title' => 'Vertragsschluss', 'text' => 'Die Produktdarstellung ist kein verbindliches Angebot. Mit „Zahlungspflichtig bestellen“ gibst du ein Angebot ab. Der Vertrag kommt mit unserer Auftragsbestätigung oder dem Versand zustande.'],
            ['title' => 'Preise und Versand', 'text' => 'Alle angezeigten Preise sind Endpreise. Versandkosten werden abhängig von Lieferland und Gesamtgewicht vor der Bestellung ausgewiesen.'],
            ['title' => 'Zahlung und Lieferung', 'text' => 'Es gelten die im Checkout angebotenen Zahlungsarten. Lieferzeit und Lieferbeschränkungen werden vor Abschluss der Bestellung angegeben.'],
            ['title' => 'Eigentum und Mängelrechte', 'text' => 'Die Ware bleibt bis zur vollständigen Zahlung unser Eigentum. Es gelten die gesetzlichen Mängelhaftungsrechte.'],
        ], 'Dieser Entwurf ersetzt keine individuelle Rechtsberatung und muss vor dem Verkaufsstart geprüft werden.');
    }

    public function adminOrders(): View
    {
        $orders = DB::table('checkout_requests')->latest()->limit(100)->get();

        return view('admin-orders', [
            'orders' => $orders,
            'categories' => $this->homeCategories(),
            'stats' => [
                'new' => DB::table('checkout_requests')->where('status', 'Neu')->count(),
                'processing' => DB::table('checkout_requests')->where('status', 'In Bearbeitung')->count(),
                'completed' => DB::table('checkout_requests')->where('status', 'Erledigt')->count(),
                'revenue' => (float) DB::table('checkout_requests')->sum('total'),
            ],
        ]);
    }

    public function updateOrderStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Neu,In Bearbeitung,Erledigt'],
        ]);

        DB::table('checkout_requests')
            ->where('id', $id)
            ->update(['status' => $validated['status'], 'updated_at' => now()]);

        return back()->with('success', 'Status wurde aktualisiert.');
    }

    public function adminProducts(): View
    {
        $products = collect($this->catalog())->flatMap(function (array $category) {
            $category = $this->enrichCategory($category);
            return collect($category['products'])->map(fn (array $product) => $this->enrichProduct($product, $category));
        })->values()->all();

        return view('admin-products', compact('products'));
    }

    public function updateProduct(Request $request, string $category, string $product): RedirectResponse
    {
        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'description' => ['nullable', 'string', 'max:10000'],
            'ingredients' => ['nullable', 'string', 'max:10000'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:20000'],
            'image_url' => ['nullable', 'url', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'active' => ['nullable', 'boolean'],
        ]);

        $this->productFromCatalog($category, $product);

        $imageUrl = $validated['image_url'] ?: null;
        if ($request->hasFile('image_file')) $imageUrl = Storage::url($request->file('image_file')->store('products', 'public'));
        DB::table('product_overrides')->updateOrInsert(
            ['category_key' => $category, 'product_handle' => $product],
            [
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'description' => $validated['description'] ?: null,
                'ingredients' => $validated['ingredients'] ?: null,
                'weight_grams' => $validated['weight_grams'],
                'image_url' => $imageUrl,
                'active' => $request->boolean('active'),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        return back()->with('success', 'Produkt wurde aktualisiert.');
    }
    public function storeProduct(Request $request): RedirectResponse
    {
        $d=$request->validate(['category_key'=>['required','string'],'name'=>['required','string','max:180'],'price'=>['required','numeric','min:0'],'stock'=>['required','integer','min:0'],'weight_grams'=>['required','integer','min:1'],'description'=>['nullable','string'],'ingredients'=>['nullable','string'],'allergens'=>['nullable','string'],'image_url'=>['nullable','url'],'featured'=>['nullable','boolean']]);
        abort_unless(isset(config('naturmarkt.categories')[$d['category_key']]),422);
        DB::table('custom_products')->insert([...$d,'handle'=>Str::slug($d['name']),'active'=>true,'featured'=>$request->boolean('featured'),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Produkt wurde angelegt.');
    }
    public function deleteProduct(string $category,string $product): RedirectResponse
    {
        DB::table('custom_products')->where(['category_key'=>$category,'handle'=>$product])->delete();
        return back()->with('success','Eigenes Produkt wurde gelöscht.');
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'company_website' => ['nullable', 'string', 'max:0'],
        ]);

        DB::table('newsletter_requests')->updateOrInsert(
            ['email' => $validated['email']],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Danke! Deine Anmeldung wurde vorgemerkt.');
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:80'],
            'street' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'in:DE,AT,NL,LU'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', 'string', 'in:PayPal,Kreditkarte,Ueberweisung'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.name' => ['required', 'string', 'max:255'],
            'cart.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'terms_accepted' => ['accepted'],
            'company_website' => ['nullable', 'string', 'max:0'],
        ]);

        $customer = ($customerId = session('customer_id')) ? DB::table('customers')->find($customerId) : null;
        if ($customer) {
            $validated['email'] = $customer->email;
        } else {
            $customerId = null;
        }

        abort_if($validated['payment_method'] === 'Kreditkarte' && ! config('naturmarkt.payments.stripe_enabled'), 422, 'Kreditkartenzahlung wird zum Verkaufsstart aktiviert.');

        $totals = $this->calculateOrderTotals(
            $validated['cart'],
            $validated['country_code'],
            $validated['coupon_code'] ?? null,
        );

        $orderId = DB::table('checkout_requests')->insertGetId([
            'customer_id' => $customerId,
            'customer_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'street' => $validated['street'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'country_code' => $validated['country_code'],
            'address' => trim($validated['street'] . ', ' . $validated['postal_code'] . ' ' . $validated['city'] . ', ' . $validated['country_code']),
            'notes' => $validated['notes'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'Offen',
            'cart' => json_encode($totals['cart'], JSON_UNESCAPED_UNICODE),
            'weight_grams' => $totals['weight_grams'],
            'subtotal' => $totals['subtotal'],
            'shipping' => $totals['shipping'],
            'discount' => $totals['discount'],
            'coupon_code' => $totals['coupon_code'],
            'total' => $totals['total'],
            'status' => 'Neu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($totals['cart'] as $item) {
            DB::table('product_overrides')
                ->where('category_key', $item['category_key'])
                ->where('product_handle', $item['product_handle'])
                ->whereNotNull('stock')
                ->decrement('stock', $item['quantity']);
        }
        if ($totals['coupon_id']) {
            DB::table('coupons')->where('id',$totals['coupon_id'])->increment('uses');
            if ($customerId) DB::table('coupon_customer')->insertOrIgnore(['coupon_id'=>$totals['coupon_id'],'customer_id'=>$customerId,'created_at'=>now(),'updated_at'=>now()]);
        }

        try {
            $summary = "Vielen Dank für deine Bestellung #{$orderId} bei Naturmarkt.\n\n"
                ."Gesamt: ".number_format($totals['total'], 2, ',', '.')." EUR\n"
                ."Zahlungsart: {$validated['payment_method']}\n\n"
                ."Wir melden uns mit den nächsten Schritten.";

            Mail::raw($summary, fn ($message) => $message
                ->to($validated['email'])
                ->subject("Naturmarkt – Bestellung #{$orderId}"));

            if ($adminEmail = env('SHOP_ADMIN_EMAIL')) {
                Mail::raw("Neue Bestellung #{$orderId} von {$validated['name']}.", fn ($message) => $message
                    ->to($adminEmail)
                    ->subject("Neue Naturmarkt-Bestellung #{$orderId}"));
            }
        } catch (\Throwable $exception) {
            Log::warning('Bestell-E-Mail konnte nicht versendet werden.', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
            ]);
        }

        session(['completed_order' => [
            'id' => $orderId,
            'total' => $totals['total'],
            'email' => $validated['email'],
            'payment_method' => $validated['payment_method'],
        ]]);

        return response()->json([
            'redirect' => route('checkout.thank-you'),
            'message' => "Danke! Deine Bestellung #{$orderId} wurde gespeichert.",
        ]);
    }

    private function calculateOrderTotals(array $requestedCart, string $countryCode, ?string $couponCode): array
    {
        $catalogProducts = collect($this->catalog())
            ->flatMap(function (array $category) {
                $category = $this->enrichCategory($category);
                return collect($category['products'])->map(fn (array $product) => $this->enrichProduct($product, $category));
            })
            ->keyBy('name');

        $cart = collect($requestedCart)->map(function (array $requested) use ($catalogProducts) {
            $product = $catalogProducts->get($requested['name']);
            abort_unless($product, 422, 'Ein Produkt im Warenkorb ist nicht mehr verfügbar.');

            $quantity = (int) $requested['quantity'];
            abort_unless($product['active'], 422, "{$product['name']} ist derzeit nicht verfügbar.");
            abort_if($product['stock'] !== null && $quantity > $product['stock'], 422, "Für {$product['name']} ist nicht genügend Bestand verfügbar.");
            $unitPrice = (float) str_replace(',', '.', str_replace('.', '', str_replace(' EUR', '', $product['price'])));

            return [
                'name' => $product['name'],
                'category_key' => $product['category_key'],
                'product_handle' => $product['handle'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_weight_grams' => $product['weight_grams'],
                'line_weight_grams' => $product['weight_grams'] * $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ];
        })->values();

        $subtotal = round((float) $cart->sum('line_total'), 2);
        $weightGrams = (int) $cart->sum('line_weight_grams');
        $shippingConfig = config("naturmarkt.shipping.countries.{$countryCode}");
        abort_unless($shippingConfig, 422, 'Das ausgewählte Lieferland wird nicht unterstützt.');

        $weightShipping = $this->shippingPriceForWeight($shippingConfig['rates'], $weightGrams);
        $shipping = $subtotal >= (float) config('naturmarkt.shipping.free_from', 60)
            ? 0.0
            : $weightShipping;

        $normalizedCoupon = strtoupper(trim((string) $couponCode));
        $couponRecord = $normalizedCoupon !== '' ? DB::table('coupons')->where('code',$normalizedCoupon)->where('active',true)->first() : null;
        $legacy = $normalizedCoupon !== '' ? config("naturmarkt.coupons.{$normalizedCoupon}") : null;
        $coupon = $couponRecord ?: ($legacy ? (object) array_merge($legacy,['id'=>null,'minimum_order'=>0,'expires_at'=>null,'max_uses'=>null,'uses'=>0]) : null);
        abort_if($normalizedCoupon !== '' && ! $coupon, 422, 'Der Gutscheincode ist ungültig.');

        abort_if($coupon && $coupon->expires_at && now()->isAfter($coupon->expires_at),422,'Der Gutschein ist abgelaufen.');
        abort_if($coupon && $coupon->max_uses && $coupon->uses >= $coupon->max_uses,422,'Der Gutschein ist aufgebraucht.');
        abort_if($coupon && $subtotal < (float)$coupon->minimum_order,422,'Der Mindestbestellwert wurde nicht erreicht.');
        $discount = $coupon ? ($coupon->type === 'percent' ? round($subtotal*((float)$coupon->value/100),2) : min($subtotal,(float)$coupon->value)) : 0.0;

        return [
            'cart' => $cart->all(),
            'subtotal' => $subtotal,
            'weight_grams' => $weightGrams,
            'shipping' => $shipping,
            'discount' => $discount,
            'coupon_code' => $coupon ? $normalizedCoupon : null,
            'coupon_id' => $coupon?->id,
            'total' => round($subtotal + $shipping - $discount, 2),
        ];
    }

    private function shippingPriceForWeight(array $rates, int $weightGrams): float
    {
        foreach ($rates as $maximumWeight => $price) {
            if ($weightGrams <= (int) $maximumWeight) {
                return (float) $price;
            }
        }

        abort(422, 'Das Gesamtgewicht ist zu hoch. Bitte teile die Bestellung auf oder kontaktiere uns.');
    }

    private function infoPage(string $title, string $text): View
    {
        return view('info-page', [
            'title' => $title,
            'text' => $text,
            'categories' => $this->homeCategories(),
        ]);
    }

    private function legalPage(string $title, array $sections, string $notice): View
    {
        return view('legal-page', compact('title', 'sections', 'notice'));
    }

    private function category(string $key): View
    {
        $category = $this->findCategory($key);
        $products = array_map(fn (array $product) => $this->enrichProduct($product, $category), $category['products']);

        return view('category', compact('category', 'products'));
    }

    private function findCategory(string $key): array
    {
        $categories = $this->catalog();

        abort_unless(isset($categories[$key]), 404);

        return $this->enrichCategory($categories[$key]);
    }

    private function catalog(): array
    {
        if ($this->catalogCache !== null) return $this->catalogCache;
        $categories=config('naturmarkt.categories', []);
        if(Schema::hasTable('custom_products')) foreach(DB::table('custom_products')->get() as $p) if(isset($categories[$p->category_key])) $categories[$p->category_key]['products'][]=['name'=>$p->name,'handle'=>$p->handle,'price'=>number_format($p->price,2,',','.').' EUR','badge'=>$categories[$p->category_key]['name'],'image'=>$p->image_url?:$categories[$p->category_key]['image'],'description'=>$p->description?:'Ausgewähltes Naturmarkt-Produkt.','ingredients'=>$p->ingredients,'weight_grams'=>$p->weight_grams];
        return $this->catalogCache = $categories;
    }

    private function homeCategories(): array
    {
        return collect($this->catalog())
            ->map(fn (array $category) => $this->enrichCategory($category))
            ->values()
            ->all();
    }

    private function featuredProducts(): array
    {
        $featured = [
            ['honigsorten', 'waldhonig'],
            ['cremes-salben', 'gesichtsgel-bienengift-gelee-royal'],
            ['sirup', 'honigsirup-mit-echinaceea-propolis'],
            ['seife', 'seife-kaffe-haselnuss'],
            ['kerzen', 'kerze-linde'],
        ];

        return collect($featured)
            ->map(function (array $pair): ?array {
                $category = $this->findCategory($pair[0]);
                $product = collect($category['products'])->firstWhere('handle', $pair[1]);

                return $product ? $this->enrichProduct($product, $category) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function enrichCategory(array $category): array
    {
        $category['url'] = route($category['route']);
        $category['sort_name'] = Str::lower($category['name']);

        return $category;
    }

    private function enrichProduct(array $product, array $category): array
    {
        if ($this->overrideCache === null) {
            $this->overrideCache = Schema::hasTable('product_overrides')
                ? DB::table('product_overrides')->get()->keyBy(fn ($row) => $row->category_key.'|'.$row->product_handle)->all()
                : [];
        }
        $override = $this->overrideCache[$category['key'].'|'.$product['handle']] ?? null;

        if ($override && $override->price !== null) {
            $product['price'] = number_format((float) $override->price, 2, ',', '.').' EUR';
        }

        if ($override) {
            $product['description'] = $override->description ?: $product['description'];
            $product['image'] = $override->image_url ?: $product['image'];
        }

        $product['stock'] = $override?->stock;
        $product['active'] = $override ? (bool) $override->active : true;
        $product['ingredients'] = $override?->ingredients
            ?: ($product['ingredients'] ?? 'Die vollständige Zutatenliste ist auf dem Produktetikett angegeben und wird vor dem Verkaufsstart zusätzlich hier eingetragen.');
        $product['description_incomplete'] = str_ends_with(trim((string) ($product['description'] ?? '')), '...');
        $product['category'] = $category['name'];
        $product['category_key'] = $category['key'];
        $product['weight_grams'] = (int) ($override?->weight_grams ?? $product['weight_grams']
            ?? config("naturmarkt.shipping.category_weights_grams.{$category['key']}")
            ?? config('naturmarkt.shipping.default_product_weight_grams', 500));
        $product['category_url'] = route($category['route']);
        $product['url'] = route('products.show', [$category['key'], $product['handle']]);
        $product['search'] = Str::lower($product['name'] . ' ' . $category['name'] . ' ' . $product['badge']);

        return $product;
    }

    private function productFromCatalog(string $categoryKey, string $handle): array
    {
        $category = $this->findCategory($categoryKey);
        $product = collect($category['products'])->firstWhere('handle', $handle);
        abort_unless($product, 404);

        return $product;
    }
}
