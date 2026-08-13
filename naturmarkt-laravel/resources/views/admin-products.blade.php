<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Naturmarkt | Produktverwaltung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body class="admin-body">
<header class="admin-header">
    <nav class="admin-nav container" aria-label="Admin-Navigation">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <div class="admin-nav-copy"><strong>Shop-Verwaltung</strong><span>Produkte einfach bearbeiten</span></div>
        <div class="admin-nav-links"><a href="{{ route('admin.orders') }}">Bestellungen</a><a class="active" href="{{ route('admin.products') }}">Produkte</a><a class="admin-shop-link" href="{{ route('home') }}">Shop ansehen ↗</a><form method="post" action="{{ route('owner.logout') }}">@csrf<button type="submit">Abmelden</button></form></div>
    </nav>
</header>
<main class="admin-page">
    <section class="container">
        <div class="admin-page-heading"><div><span class="admin-kicker">Sortiment</span><h1>Produkte verwalten</h1><p>Preis, Lagerbestand und Sichtbarkeit direkt beim jeweiligen Produkt ändern.</p></div></div>
        @if(session('success'))<p class="admin-success">{{ session('success') }}</p>@endif
        <details class="admin-create"><summary><strong>+ Neues Produkt anlegen</strong></summary><form method="post" action="{{ route('admin.products.store') }}" class="admin-form">@csrf<select name="category_key">@foreach(config('naturmarkt.categories') as $key=>$cat)<option value="{{ $key }}">{{ $cat['name'] }}</option>@endforeach</select><input name="name" placeholder="Produktname" required><input type="number" step=".01" name="price" placeholder="Preis" required><input type="number" name="stock" placeholder="Bestand" required><input type="number" name="weight_grams" value="500" required><input type="url" name="image_url" placeholder="Bild-URL"><textarea name="description" placeholder="Beschreibung"></textarea><textarea name="ingredients" placeholder="Zutaten"></textarea><input name="allergens" placeholder="Allergene"><label><input type="checkbox" name="featured" value="1"> Bestseller</label><button>Produkt anlegen</button></form></details>
        <div class="admin-toolbar">
            <label for="admin-product-search"><span>Produkt suchen</span><input id="admin-product-search" type="search" placeholder="Name oder Kategorie eingeben …"></label>
            <div><strong>{{ count($products) }}</strong><span>Produkte insgesamt</span></div>
        </div>
        <div class="admin-product-grid">
            @foreach($products as $product)
                <article class="admin-product-card" data-admin-product="{{ Str::lower($product['name'].' '.$product['category']) }}">
                    <img src="{{ $product['image'] }}" alt="">
                    <div class="admin-product-copy"><span>{{ $product['category'] }}</span><h3>{{ $product['name'] }}</h3><small>{{ $product['active'] ? (($product['stock'] ?? 100).' Stück verfügbar') : 'Im Shop ausgeblendet' }}</small></div>
                    <form method="post" action="{{ route('admin.products.update', [$product['category_key'], $product['handle']]) }}">
                        @csrf
                        <label><span>Preis in €</span><input type="number" name="price" min="0" step="0.01" value="{{ str_replace(',', '.', str_replace('.', '', str_replace(' EUR', '', $product['price']))) }}" required></label>
                        <label><span>Lagerbestand</span><input type="number" name="stock" min="0" value="{{ $product['stock'] ?? 100 }}" required></label>
                        <label><span>Gewicht in Gramm</span><input type="number" name="weight_grams" min="1" max="20000" value="{{ $product['weight_grams'] }}" required></label>
                        <label class="admin-product-text"><span>Bild-URL</span><input type="url" name="image_url" value="{{ $product['image'] }}" placeholder="https://…"></label>
                        <label class="admin-switch"><input type="checkbox" name="active" value="1" @checked($product['active'])><span></span><strong>Im Shop sichtbar</strong></label>
                        <label class="admin-product-text"><span>Vollständige Beschreibung</span><textarea name="description" rows="4" placeholder="Vollständigen Produkttext eintragen">{{ $product['description'] }}</textarea></label>
                        <label class="admin-product-text"><span>Zutaten und Pflichtangaben</span><textarea name="ingredients" rows="4" placeholder="Zutaten laut Verpackung eintragen">{{ str_starts_with($product['ingredients'], 'Die vollständige Zutatenliste') ? '' : $product['ingredients'] }}</textarea></label>
                        <button type="submit">Änderungen speichern</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
