<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Bestellungen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body class="admin-body">
<header class="admin-header">
    <nav class="admin-nav container" aria-label="Admin-Navigation">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <div class="admin-nav-copy"><strong>Shop-Verwaltung</strong><span>Alles Wichtige auf einen Blick</span></div>
        <div class="admin-nav-links"><a class="active" href="{{ route('admin.orders') }}">Bestellungen</a><a href="{{ route('admin.products') }}">Produkte</a><a class="admin-shop-link" href="{{ route('home') }}">Shop ansehen ↗</a><form method="post" action="{{ route('owner.logout') }}">@csrf<button type="submit">Abmelden</button></form></div>
    </nav>
</header>
<main class="admin-page">
    <section class="container">
        <div class="admin-page-heading"><div><span class="admin-kicker">Übersicht</span><h1>Bestellungen verwalten</h1><p>Neue Bestellungen prüfen und den Bearbeitungsstatus mit einem Klick aktualisieren.</p></div></div>
        <div class="admin-stats">
            <article><span>Neu</span><strong>{{ $stats['new'] }}</strong><small>Noch nicht bearbeitet</small></article>
            <article><span>In Bearbeitung</span><strong>{{ $stats['processing'] }}</strong><small>Wird vorbereitet</small></article>
            <article><span>Erledigt</span><strong>{{ $stats['completed'] }}</strong><small>Abgeschlossen</small></article>
            <article><span>Bestellwert gesamt</span><strong>{{ number_format($stats['revenue'], 2, ',', '.') }} €</strong><small>Alle Bestellungen</small></article>
        </div>
        @if(session('success'))<p class="admin-success">{{ session('success') }}</p>@endif
        <div class="admin-section-heading"><div><h2>Letzte Bestellungen</h2><p>Die neuesten Bestellungen stehen ganz oben.</p></div><span>{{ count($orders) }} Einträge</span></div>
        <div class="admin-order-list">
            @forelse ($orders as $order)
                <article class="admin-order-card">
                    <div class="admin-order-main">
                        <div class="admin-order-title"><span class="admin-order-number">#{{ $order->id }}</span><span class="status-pill status-{{ Str::slug($order->status ?? 'Neu') }}">{{ $order->status ?? 'Neu' }}</span></div>
                        <h3>{{ $order->customer_name ?? 'Ohne Name' }}</h3>
                        <div class="admin-contact-grid">
                            <span><small>E-Mail</small>{{ $order->email }}</span>
                            <span><small>Telefon</small>{{ $order->phone }}</span>
                            <span><small>Lieferadresse</small>{{ $order->street }}, {{ $order->postal_code }} {{ $order->city }} · {{ $order->country_code ?? 'DE' }}</span>
                            <span><small>Bestellt am</small>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d.m.Y · H:i') }} Uhr</span>
                        </div>
                        @if($order->notes)<p class="admin-note"><strong>Kundenhinweis:</strong> {{ $order->notes }}</p>@endif
                    </div>
                    <div class="admin-order-side">
                        <small>Gesamtbetrag</small>
                        <span>Gewicht: {{ number_format(((int) ($order->weight_grams ?? 0)) / 1000, 2, ',', '.') }} kg</span>
                        <strong>{{ number_format((float) $order->total, 2, ',', '.') }} €</strong>
                        <span>Zahlung: {{ $order->payment_method === 'Ueberweisung' ? 'Überweisung' : $order->payment_method }}</span>
                        <form method="post" action="{{ route('admin.orders.status', $order->id) }}">
                            @csrf
                            <label for="status-{{ $order->id }}">Bestellstatus</label>
                            <select id="status-{{ $order->id }}" name="status">
                                @foreach (['Neu', 'In Bearbeitung', 'Erledigt'] as $status)
                                    <option value="{{ $status }}" @selected(($order->status ?? 'Neu') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <button type="submit">Status speichern</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="cart-empty-state"><strong>Noch keine Bestellungen.</strong><p>Sobald Kunden bestellen, erscheinen sie übersichtlich an dieser Stelle.</p></div>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>
