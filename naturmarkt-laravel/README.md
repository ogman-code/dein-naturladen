# Naturmarkt – Laravel Landingpage

Eigenständiges Demo-Projekt, inspiriert von der Informationsarchitektur eines Naturwaren-Shops. Es ist keine 1:1-Kopie und verwendet keine Bilder, Logos oder geschützten Texte der Referenzseite.

## Installation

```bash
cd ~/Desktop/Projekte
unzip naturmarkt-laravel.zip
cd naturmarkt-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Danach im Browser öffnen: `http://127.0.0.1:8000`

## Enthalten

- Responsive Laravel-Blade-Startseite
- Produktkategorien
- Werte- und Über-uns-Bereich
- Newsletter-Formular mit Laravel-Validierung
- Mobile Navigation
- Eigenständiges CSS ohne Build-Schritt

## Wichtiger Hinweis

Vor einer echten Veröffentlichung müssen Impressum, Datenschutz, AGB, Kontaktangaben, Produktdaten, Zahlungsabwicklung und Newsletter-Speicherung ergänzt werden.
