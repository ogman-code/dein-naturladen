# Going live: dein-naturladen.com on shared hosting

## 0. What you need from the host
- PHP **8.2 or newer** with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`
- one MySQL/MariaDB database
- the option to point the domain's **document root** at a subfolder (almost every host has it)
- free SSL (Let's Encrypt)
- SSH access is very helpful but not required

Connect `dein-naturladen.com` (and `www`) to the hosting: either transfer the domain or set its DNS A record or nameservers to the host's values. It can take a few hours to take effect.

## 1. Build the upload package (on your PC)
Double-click `build-release.bat` in the project folder.
Result: `release\naturmarkt-release.zip` (~40 MB, without `.env`, logs or dev packages).

## 2. Upload
1. Create a folder in the host's file manager, e.g. `naturmarkt` (**outside** `public_html`).
2. Upload the ZIP there and extract it.
3. In the domain settings, set the **document root** to `naturmarkt/public`.
   *Only if that isn't possible:* extract into the web folder and rename `root.htaccess` to `.htaccess`.
4. Make `storage/` and `bootstrap/cache/` writable (permissions 775; usually already set).

## 3. Database
Create a database and user in the host panel and note the name, user and password. Then **one** of these:
- **With SSH:** `cd naturmarkt && php artisan migrate --force`
- **Without SSH:** in phpMyAdmin, import `database/schema.sql`.

## 4. Configure `.env`
Copy `.env.production.example` to `.env` and fill in:

| Field | Value |
|---|---|
| `APP_KEY` | run `php artisan key:generate --show` on your PC and paste the output (`base64:…`) |
| `APP_URL` | `https://dein-naturladen.com` (or with `www.`, whichever is the main address) |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | from step 3 (`DB_HOST` is often `localhost`; the host will tell you) |
| `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` | SMTP details of a mailbox such as `shop@dein-naturladen.com` (set it up with the host) |
| `SHOP_ADMIN_EMAIL` | where order notifications should go |
| `LEGAL_OWNER`, `LEGAL_STREET`, `LEGAL_CITY`, `LEGAL_EMAIL`, `LEGAL_PHONE` | **required**: these fill the Impressum |

Leave `APP_DEBUG=false`. Set `FORCE_HTTPS=true` only once SSL is active (step 5).

## 5. Turn on SSL
Enable Let's Encrypt for the domain in the host panel. Then check that `FORCE_HTTPS=true` and `SESSION_SECURE_COOKIE=true` are set in `.env`.

## 6. Create the admin account
On a fresh database there is no owner account, and nobody can register one through the website.
- **With SSH:** `php artisan naturmarkt:create-owner "Your Name" you@dein-naturladen.com`
  The password is shown **once**; save it to your password manager right away.
- **Without SSH:** on your PC, run
  `php -r "echo password_hash('YOUR-PASSWORD', PASSWORD_BCRYPT);"`
  then in phpMyAdmin, add a row to the `owner_users` table: `name`, `email`, `password` = that hash, `created_at`/`updated_at` = now.

Log in at `https://dein-naturladen.com/kunden/login` with these credentials. You'll land in the admin area.

## 7. Check before announcing it
- [ ] Home page, categories, product page, search load (with images)
- [ ] Impressum, Datenschutz, AGB, Widerruf, Versand have **real content and no `[ergänzen]` placeholders**
- [ ] Place a test order: confirmation email arrives, order shows up in the admin
- [ ] Register a customer: verification email arrives, the link works
- [ ] `https://dein-naturladen.com/robots.txt` and `/sitemap.xml` reachable
- [ ] Optional (SSH): `php artisan naturmarkt:import-product-facts` imports ingredient details from the old shop

## 8. Optional: cron for daily backups
In the host's cron settings, run every minute:
`cd /path/to/naturmarkt && php artisan schedule:run`

## Updates later
Run `build-release.bat` again, then upload and overwrite everything **except `.env` and `storage/`**. With SSH, also run `php artisan migrate --force` afterwards.
