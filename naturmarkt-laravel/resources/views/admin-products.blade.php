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
        @php
            $productGroups = collect($products)->groupBy('category_key');
            $productCategories = collect(config('naturmarkt.categories'))->map(fn ($category, $key) => [
                'name' => $category['name'],
                'count' => $productGroups->get($key, collect())->count(),
            ]);
        @endphp
        <div class="admin-catalog-layout">
        <aside class="admin-category-panel" aria-label="Produktkategorien">
            <h2>Kategorien</h2>
            <p>Kategorie auswählen und Produkte bearbeiten.</p>
            <div class="admin-category-list">
                <button type="button" class="admin-category-button" data-admin-category="" data-category-name="Alle Produkte" aria-pressed="true" aria-controls="admin-catalog-results"><span>Alle Produkte</span><strong>{{ count($products) }}</strong></button>
                @foreach($productCategories as $key => $category)
                    <button type="button" class="admin-category-button" data-admin-category="{{ $key }}" data-category-name="{{ $category['name'] }}" aria-pressed="false" aria-controls="admin-catalog-results"><span>{{ $category['name'] }}</span><strong>{{ $category['count'] }}</strong></button>
                @endforeach
            </div>
        </aside>
        <div class="admin-catalog-results" id="admin-catalog-results">
        <div class="admin-catalog-heading"><div><span class="admin-kicker">Ihre Produktauswahl</span><h2 id="admin-category-title">Alle Produkte</h2></div><p id="admin-product-count" role="status" aria-live="polite">{{ count($products) }} Produkte</p></div>
        <div class="admin-toolbar">
            <label for="admin-product-search"><span>Produkt suchen</span><input id="admin-product-search" type="search" placeholder="Name oder Kategorie eingeben …"></label>
            <button type="button" class="admin-search-reset" id="admin-search-reset" hidden>Suche zurücksetzen</button>
        </div>
        <div class="admin-product-filters" aria-label="Produkte filtern">
            <button type="button" class="active" data-admin-product-filter="all">Alle</button>
            <button type="button" data-admin-product-filter="out-of-stock">Ausverkauft</button>
            <button type="button" data-admin-product-filter="hidden">Versteckt</button>
            <button type="button" data-admin-product-filter="incomplete">Unvollständig</button>
            <button type="button" data-admin-product-filter="low-stock">Niedriger Bestand</button>
        </div>
        <form id="bulk-product-form" class="admin-bulk-actions" method="post" action="{{ route('admin.products.bulk-update') }}">
            @csrf
            <div><strong><span data-admin-selected-count>0</span> ausgewählt</strong><button type="button" data-admin-select-visible>Sichtbare auswählen</button><button type="button" data-admin-clear-selection>Auswahl aufheben</button></div>
            <label><span>Mehrfachaktion</span><select name="bulk_action" required><option value="">Aktion wählen …</option><option value="show">Im Shop einblenden</option><option value="hide">Im Shop ausblenden</option><option value="set_stock">Lagerbestand setzen</option></select></label>
            <label class="admin-bulk-stock" hidden><span>Neuer Bestand</span><input type="number" name="bulk_stock" min="0" max="999999" placeholder="0"></label>
            <button type="submit" disabled data-admin-bulk-submit>Anwenden</button>
        </form>
        <div class="admin-catalog-empty" id="admin-catalog-empty" hidden><h3>Keine Produkte gefunden</h3><p>Versuchen Sie einen anderen Produktnamen oder wählen Sie eine andere Kategorie.</p></div>
        @foreach($productCategories as $categoryKey => $category)
        <section class="admin-product-group" data-admin-group="{{ $categoryKey }}" aria-label="{{ $category['name'] }}">
        <h3 class="admin-group-title">{{ $category['name'] }} <span>{{ $category['count'] }} Produkte</span></h3>
        <div class="admin-product-grid">
            @foreach($productGroups->get($categoryKey, collect())->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $product)
                        @php($displayStock = $product['stock'] ?? 100)
                        @php($isIncomplete = $product['description_incomplete'] || str_starts_with($product['ingredients'], 'Die vollständige Zutatenliste'))
                        <article class="admin-product-card" data-admin-category-key="{{ $product['category_key'] }}" data-admin-product="{{ Str::lower($product['name'].' '.$product['category']) }}" data-stock="{{ $displayStock }}" data-active="{{ $product['active'] ? '1' : '0' }}" data-incomplete="{{ $isIncomplete ? '1' : '0' }}">
                    <div class="admin-product-row">
                        <label class="admin-product-select" title="Produkt auswählen"><input type="checkbox" name="products[]" value="{{ $product['category_key'].'|'.$product['handle'] }}" form="bulk-product-form" data-admin-product-select><span></span></label>
                        <img src="{{ $product['image'] }}" alt="">
                        <div class="admin-product-copy"><span>{{ $product['category'] }}</span><h3>{{ $product['name'] }}</h3><div class="admin-product-badges"><small class="{{ $displayStock === 0 ? 'danger' : ($displayStock <= 10 ? 'warning' : '') }}">{{ $displayStock === 0 ? 'Ausverkauft' : $displayStock.' Stück verfügbar' }}</small>@if(!$product['active'])<small class="muted">Versteckt</small>@endif @if($isIncomplete)<small class="warning">Angaben fehlen</small>@endif</div></div>
                        <button type="button" class="admin-product-expand" aria-expanded="false"><span>Bearbeiten</span><b>⌄</b></button>
                    </div>
                    <div class="admin-product-editor" hidden>
                    <form method="post" enctype="multipart/form-data" action="{{ route('admin.products.update', [$product['category_key'], $product['handle']]) }}">
                        @csrf
                        <label><span>Preis in €</span><input type="number" name="price" min="0" step="0.01" value="{{ str_replace(',', '.', str_replace('.', '', str_replace(' EUR', '', $product['price']))) }}" required></label>
                        <label><span>Lagerbestand</span><input type="number" name="stock" min="0" value="{{ $product['stock'] ?? 100 }}" required></label>
                        <label><span>Gewicht in Gramm</span><input type="number" name="weight_grams" min="1" max="20000" value="{{ $product['weight_grams'] }}" required></label>
                        <label class="admin-product-text"><span>Bild-URL</span><input type="url" name="image_url" value="{{ $product['image'] }}" placeholder="https://…"></label>
                        <label class="admin-product-text"><span>Oder Bild hochladen (JPG, PNG, WebP)</span><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
                        <label class="admin-switch"><input type="checkbox" name="active" value="1" @checked($product['active'])><span></span><strong>Im Shop sichtbar</strong></label>
                        <label class="admin-product-text"><span>Vollständige Beschreibung</span><textarea name="description" rows="4" placeholder="Vollständigen Produkttext eintragen">{{ $product['description'] }}</textarea></label>
                        <label class="admin-product-text"><span>Zutaten und Pflichtangaben</span><textarea name="ingredients" rows="4" placeholder="Zutaten laut Verpackung eintragen">{{ str_starts_with($product['ingredients'], 'Die vollständige Zutatenliste') ? '' : $product['ingredients'] }}</textarea></label>
                        <button type="submit">Änderungen speichern</button>
                    </form>
                    </div>
                </article>
            @endforeach
        </div>
        </section>
        @endforeach
        </div>
        </div>
    </section>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
