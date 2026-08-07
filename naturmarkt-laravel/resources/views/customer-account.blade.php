<!doctype html>
<html lang="de"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Mein Konto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head><body class="customer-account-body">
<header class="site-header"><nav class="nav container" aria-label="Hauptnavigation"><a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a><div class="nav-links"><a href="{{ route('home') }}">Shop</a><a href="{{ route('cart') }}">Warenkorb</a></div><form method="post" action="{{ route('customer.logout') }}">@csrf<button class="account-logout" type="submit">Abmelden</button></form></nav></header>
<main class="account-page container">
    <div class="account-heading"><div><span class="eyebrow">Mein Naturmarkt</span><h1>Hallo, {{ $customer->name }}.</h1><p>Hier findest du deine persönlichen Daten und Bestellungen.</p></div><a class="button primary" href="{{ route('home') }}#kategorien">Weiter einkaufen</a></div>
    @if(session('success'))<p class="admin-success">{{ session('success') }}</p>@endif @if($errors->any())<div class="auth-error">{{ $errors->first() }}</div>@endif
    <div class="account-layout">
        <section class="account-panel"><h2>Meine Daten</h2><form class="account-address-form" method="post" action="{{ route('customer.update') }}">@csrf @method('PUT')
            <label><span>Name</span><input name="name" value="{{ old('name', $customer->name) }}" required></label><label><span>E-Mail</span><input type="email" name="email" value="{{ old('email', $customer->email) }}" required></label>
            <label><span>Telefon</span><input name="phone" value="{{ old('phone', $customer->phone) }}"></label><label class="wide-field"><span>Straße und Hausnummer</span><input name="street" value="{{ old('street', $customer->street) }}"></label>
            <label><span>PLZ</span><input name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}"></label><label><span>Ort</span><input name="city" value="{{ old('city', $customer->city) }}"></label>
            <label><span>Land</span><select name="country_code">@foreach(config('naturmarkt.shipping.countries', []) as $code => $country)<option value="{{ $code }}" @selected(old('country_code', $customer->country_code) === $code)>{{ $country['name'] }}</option>@endforeach</select></label><button class="button primary" type="submit">Daten speichern</button>
        </form></section>
        <section class="account-panel account-orders"><h2>Meine Bestellungen</h2>
            @forelse($orders as $order)<article class="customer-order-card"><div><strong>Bestellung #{{ $order->id }}</strong><time>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d.m.Y') }}</time><span class="order-status">{{ $order->status }}</span></div><ul>@foreach($order->items as $item)<li><span>{{ $item['quantity'] ?? 1 }} × {{ $item['name'] ?? 'Produkt' }}</span><strong>{{ number_format((float) ($item['line_total'] ?? 0), 2, ',', '.') }} EUR</strong></li>@endforeach</ul><footer><span>{{ $order->payment_method }} · {{ $order->payment_status }}</span><strong>{{ number_format((float) $order->total, 2, ',', '.') }} EUR</strong></footer></article>
            @empty<div class="cart-empty-state"><strong>Noch keine Bestellungen.</strong><p>Bestellungen, die du angemeldet aufgibst, erscheinen hier.</p><a class="button primary" href="{{ route('home') }}#kategorien">Produkte entdecken</a></div>@endforelse
        </section>
    </div>
</main><script src="{{ asset('assets/js/site.js') }}"></script></body></html>
