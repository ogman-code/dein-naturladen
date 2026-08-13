<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;
Artisan::command('inspire', function () { $this->comment('Natur beginnt mit kleinen Entscheidungen.'); });

Artisan::command('naturmarkt:backup', function () {
    $directory = storage_path('app/backups');
    File::ensureDirectoryExists($directory);

    $payload = [
        'created_at' => now()->toIso8601String(),
        'orders' => DB::table('checkout_requests')->orderBy('id')->get(),
        'newsletter' => DB::table('newsletter_requests')->orderBy('id')->get(),
        'product_overrides' => DB::table('product_overrides')->orderBy('id')->get(),
    ];

    $file = $directory.'/naturmarkt-'.now()->format('Y-m-d-His').'.json';
    File::put($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $this->info("Backup erstellt: {$file}");
})->purpose('Sichert Naturmarkt-Bestellungen und Einstellungen als JSON');

Schedule::command('naturmarkt:backup')->dailyAt('02:00')->withoutOverlapping();

Artisan::command('naturmarkt:import-product-facts {--dry-run}', function () {
    $categories = config('naturmarkt.categories', []);
    $products = collect($categories)->flatMap(fn (array $category) => collect($category['products'])
        ->map(fn (array $product) => [
            'category_key' => $category['key'],
            'handle' => $product['handle'],
            'name' => $product['name'],
        ]));

    $extractFacts = function (string $html): ?string {
        $text = html_entity_decode(strip_tags(str_replace(
            ['</div>', '</p>', '<br>', '<br/>', '<br />'],
            ["\n", "\n", "\n", "\n", "\n"],
            $html
        )), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\R{2,}/u', "\n", $text);
        $text = trim($text);

        $sections = [];
        $patterns = [
            'Zutaten / Zusammensetzung' => '/(?:Zutaten|Inhaltsstoffe|Zusammensetzung|Bestandteile)\s*:\s*(.{1,1600}?)(?=(?:Dosierung|Verzehrempfehlung|Anwendung|Verwendung|Wirkung|Indikationen?|Hinweise?|Warnhinweise?|Assoziationen|Aufbewahrung)\s*:|$)/isu',
            'Dosierung / Anwendung' => '/(?:Dosierung|Verzehrempfehlung|Anwendung|Verwendung)\s*:\s*(.{1,900}?)(?=(?:Zutaten|Inhaltsstoffe|Zusammensetzung|Wirkung|Indikationen?|Hinweise?|Warnhinweise?|Assoziationen|Aufbewahrung)\s*:|$)/isu',
            'Hinweise' => '/(?:Warnhinweise?|Hinweise?|Aufbewahrung)\s*:\s*(.{1,900}?)(?=(?:Zutaten|Inhaltsstoffe|Zusammensetzung|Dosierung|Verzehrempfehlung|Anwendung|Verwendung|Wirkung|Indikationen?|Assoziationen)\s*:|$)/isu',
        ];

        foreach ($patterns as $heading => $pattern) {
            if (preg_match($pattern, $text, $match)) {
                $value = trim(preg_replace('/\s+/u', ' ', $match[1]), " \n\t-–");
                if ($value !== '') {
                    $sections[] = "{$heading}:\n{$value}";
                }
            }
        }

        if (preg_match('/Nahrungsergänzungsmittel ersetzen nicht.{1,500}?(?:Lebensweise|Ausgeglichenheit)\.?/isu', $text, $warning)) {
            $sections[] = "Pflichthinweis:\n".trim(preg_replace('/\s+/u', ' ', $warning[0]));
        }

        return $sections ? implode("\n\n", array_unique($sections)) : null;
    };

    $imported = 0;
    $missing = [];
    $failed = [];
    $previousReportPath = storage_path('app/product-facts-import-report.json');
    $previousReport = File::exists($previousReportPath)
        ? json_decode(File::get($previousReportPath), true)
        : [];
    $missing = $previousReport['missing'] ?? [];
    $knownMissing = collect($previousReport['missing'] ?? []);
    $alreadyImported = DB::table('product_overrides')
        ->whereNotNull('ingredients')
        ->where('ingredients', '<>', '')
        ->get(['category_key', 'product_handle'])
        ->map(fn ($row) => "{$row->category_key}/{$row->product_handle}")
        ->flip();

    $bar = $this->output->createProgressBar($products->count());
    $bar->start();

    foreach ($products as $product) {
        if ($alreadyImported->has("{$product['category_key']}/{$product['handle']}") || $knownMissing->contains($product['name'])) {
            $bar->advance();
            continue;
        }

        try {
            $response = Http::withUserAgent('Mozilla/5.0 Naturmarkt product data sync')
                ->timeout(20)
                ->retry(3, 1000, throw: false)
                ->get("https://www.dein-naturladen.de/products/{$product['handle']}");

            if (! $response->successful()) {
                $failed[] = $product['name'];
                $bar->advance();
                continue;
            }

            $description = '';
            if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/isu', $response->body(), $scripts)) {
                foreach ($scripts[1] as $script) {
                    $json = json_decode(html_entity_decode($script, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
                    if (is_array($json) && ($json['@type'] ?? null) === 'Product') {
                        $description = (string) ($json['description'] ?? '');
                        break;
                    }
                }
            }

            $facts = $extractFacts($description);
            if (! $facts) {
                $missing[] = $product['name'];
                $bar->advance();
                continue;
            }

            if (! $this->option('dry-run')) {
                DB::table('product_overrides')->updateOrInsert(
                    ['category_key' => $product['category_key'], 'product_handle' => $product['handle']],
                    ['ingredients' => $facts, 'updated_at' => now(), 'created_at' => now()],
                );
            }

            $imported++;
        } catch (\Throwable) {
            $failed[] = $product['name'];
        }

        $bar->advance();
        usleep(300000);
    }

    $bar->finish();
    $this->newLine(2);
    $this->info(($this->option('dry-run') ? 'Gefunden' : 'Importiert').": {$imported}");
    $this->warn('Ohne strukturierte Zutatenangaben: '.count($missing));
    $this->error('Abruf fehlgeschlagen: '.count($failed));

    File::put(storage_path('app/product-facts-import-report.json'), json_encode([
        'created_at' => now()->toIso8601String(),
        'imported' => $imported,
        'missing' => $missing,
        'failed' => $failed,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
})->purpose('Importiert sachliche Zutaten-, Dosierungs- und Pflichtangaben aus dem Quellshop');
