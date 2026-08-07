<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
    <title>Naturmarkt | Kundenkonto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body class="customer-auth-body">
<header class="account-simple-header container"><a href="{{ route('home') }}"><img src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a><a href="{{ route('home') }}">Zurück zum Shop</a></header>
<main class="customer-auth-shell container">
    <section class="customer-auth-intro"><span class="eyebrow">Mein Naturmarkt</span><h1>Ein Konto für deine Bestellungen.</h1><p>Speichere deine Lieferadresse, fülle die Kasse schneller aus und behalte deine Bestellungen im Blick.</p><ul><li>Bestellverlauf einsehen</li><li>Adresse sicher speichern</li><li>Schneller bestellen</li></ul></section>
    <div class="customer-auth-book{{ old('auth_mode') === 'register' ? ' is-registering' : '' }}" data-auth-book>
        <span class="page-turn-hand" aria-hidden="true">☝</span>
        <div class="customer-auth-pages">
            <section class="customer-auth-card customer-auth-page customer-auth-login" aria-hidden="false">
                <span class="eyebrow">Willkommen zurück</span><h2>Anmelden</h2>
                @if(session('error'))<p class="auth-error">{{ session('error') }}</p>@endif
                @if($errors->any() && old('auth_mode') !== 'register')<p class="auth-error">{{ $errors->first() }}</p>@endif
                <form method="post" action="{{ route('customer.authenticate') }}">@csrf
                    <label><span>E-Mail-Adresse</span><input type="email" name="email" value="{{ old('auth_mode') !== 'register' ? old('email') : '' }}" autocomplete="email" required></label>
                    <label><span>Passwort</span><input type="password" name="password" autocomplete="current-password" required></label>
                    <button type="submit">Anmelden</button>
                </form>
                <div class="auth-choice"><span>oder</span></div>
                <button class="auth-switch" type="button" data-show-register><span>Konto erstellen</span> <span aria-hidden="true">→</span></button>
            </section>
            <section class="customer-auth-card customer-auth-page customer-auth-register" aria-hidden="true">
                <button class="auth-back" type="button" data-show-login><span aria-hidden="true">←</span> <span>Zurück zur Anmeldung</span></button>
                <span class="eyebrow">Neu dabei</span><h2>Konto erstellen</h2>
                @if($errors->any() && old('auth_mode') === 'register')<p class="auth-error">{{ $errors->first() }}</p>@endif
                <form method="post" action="{{ route('customer.register') }}">@csrf
                    <input type="hidden" name="auth_mode" value="register">
                    <label><span>Name</span><input type="text" name="name" value="{{ old('auth_mode') === 'register' ? old('name') : '' }}" autocomplete="name" required></label>
                    <label><span>E-Mail-Adresse</span><input type="email" name="email" value="{{ old('auth_mode') === 'register' ? old('email') : '' }}" autocomplete="email" required></label>
                    <label><span>Passwort</span><input type="password" name="password" minlength="10" autocomplete="new-password" required></label>
                    <label><span>Passwort wiederholen</span><input type="password" name="password_confirmation" minlength="10" autocomplete="new-password" required></label>
                    <small>Mindestens 10 Zeichen.</small><button type="submit">Kostenlos registrieren</button>
                </form>
            </section>
        </div>
    </div>
</main>
<script>
(() => {
    const book = document.querySelector('[data-auth-book]');
    if (!book) return;
    const loginPage = book.querySelector('.customer-auth-login');
    const registerPage = book.querySelector('.customer-auth-register');
    const setPage = (register) => {
        book.classList.toggle('is-registering', register);
        loginPage.setAttribute('aria-hidden', String(register));
        registerPage.setAttribute('aria-hidden', String(!register));
        window.setTimeout(() => {
            const target = register ? registerPage : loginPage;
            target.querySelector('input:not([type="hidden"])')?.focus();
        }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 720);
    };
    book.querySelector('[data-show-register]').addEventListener('click', () => setPage(true));
    book.querySelector('[data-show-login]').addEventListener('click', () => setPage(false));
    setPage(book.classList.contains('is-registering'));
})();
</script>
</body></html>
