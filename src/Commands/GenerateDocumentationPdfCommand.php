<?php

namespace Laragiser\Mapping\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GenerateDocumentationPdfCommand extends Command
{
    protected $signature = 'mapping:generate-pdf {--output= : Path file PDF output kustom}';
    protected $description = 'Generate dokumentasi lengkap laragiser/laravel-mapping ke format PDF berkualitas tinggi menggunakan headless browser rendering';

    public function handle(): int
    {
        $this->info('Memulai kompilasi dokumentasi laragiser/laravel-mapping ke format PDF...');

        $outputPath = $this->option('output') ?: base_path('docs/modules/laravel-mapping-package/laravel-mapping-documentation.pdf');
        $outputDir = dirname($outputPath);

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Render HTML sumber dokumentasi ke file sementara
        $tempHtmlPath = sys_get_temp_dir() . '/laravel_mapping_doc_' . uniqid() . '.html';
        $htmlContent = $this->buildHtmlDocumentation();
        file_put_contents($tempHtmlPath, $htmlContent);

        // Cari executable Chrome di MacOS / Linux / Windows
        $chromeBinary = $this->findChromeBinary();

        if (!$chromeBinary) {
            $this->error('Google Chrome / Chromium tidak ditemukan di sistem. Harap pastikan Chrome terpasang.');
            return self::FAILURE;
        }

        $this->line("Menggunakan rendering engine: {$chromeBinary}");

        $cmd = [
            $chromeBinary,
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--print-to-pdf=' . $outputPath,
            '--print-to-pdf-no-header',
            'file://' . $tempHtmlPath,
        ];

        $process = new Process($cmd);
        $process->setTimeout(60);
        $process->run();

        @unlink($tempHtmlPath);

        if (!$process->isSuccessful() && !file_exists($outputPath)) {
            $this->error('Gagal men-generate PDF: ' . $process->getErrorOutput());
            return self::FAILURE;
        }

        // Duplikasi ke public asset agar dapat diunduh langsung dari web
        $publicDir = public_path('vendor/laravel-mapping');
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }
        @copy($outputPath, $publicDir . '/laravel-mapping-documentation.pdf');

        $sizeKb = round(filesize($outputPath) / 1024, 2);
        $this->info("Dokumentasi PDF berhasil digenerate ({$sizeKb} KB)!");
        $this->line("Lokasi berkas: <comment>{$outputPath}</comment>");

        return self::SUCCESS;
    }

    protected function findChromeBinary(): ?string
    {
        $candidates = [
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Chromium.app/Contents/MacOS/Chromium',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
        ];

        foreach ($candidates as $bin) {
            if (file_exists($bin) && is_executable($bin)) {
                return $bin;
            }
        }

        return null;
    }

    protected function buildHtmlDocumentation(): string
    {
        // Konten HTML lengkap dengan styling cetak profesional
        $date = date('d F Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laravel Mapping Package Documentation</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm 20mm 15mm;
            @bottom-right {
                content: counter(page);
                font-family: 'Inter', sans-serif;
                font-size: 9pt;
                color: #64748b;
            }
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            line-height: 1.6;
            font-size: 10.5pt;
        }
        .cover {
            page-break-after: always;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            padding: 40px;
            box-sizing: border-box;
            border-radius: 16px;
        }
        .cover h1 {
            font-size: 32pt;
            font-weight: 800;
            margin: 0 0 10px 0;
            letter-spacing: -0.03em;
        }
        .cover .subtitle {
            font-size: 16pt;
            font-weight: 400;
            color: #e0f2fe;
            margin-bottom: 40px;
            max-width: 650px;
        }
        .cover .meta {
            margin-top: 60px;
            font-size: 10.5pt;
            color: #bae6fd;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 20px;
            width: 80%;
        }
        h2 {
            font-size: 18pt;
            color: #0369a1;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 28pt;
            page-break-after: avoid;
        }
        h3 {
            font-size: 13pt;
            color: #0f172a;
            margin-top: 18pt;
            page-break-after: avoid;
        }
        p, li {
            text-align: justify;
        }
        pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 14px;
            border-radius: 8px;
            font-family: "JetBrains Mono", Consolas, Monaco, monospace;
            font-size: 9pt;
            line-height: 1.45;
            overflow-x: auto;
            border: 1px solid #334155;
            page-break-inside: avoid;
        }
        code {
            font-family: "JetBrains Mono", Consolas, Monaco, monospace;
            background-color: #f1f5f9;
            color: #0369a1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9.5pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            color: #0f172a;
            font-weight: 600;
        }
        .alert {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 12px 16px;
            margin: 14px 0;
            border-radius: 4px;
            font-size: 10pt;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <div class="cover">
        <div style="font-size: 48px; margin-bottom: 20px;">🗺️</div>
        <h1>LARAVEL MAPPING PACKAGE</h1>
        <div class="subtitle">Dokumentasi Arsitektur, Panduan Integrasi, dan Referensi API Lengkap</div>
        <div class="meta">
            <strong>Package:</strong> xdiego1st/laravel-mapku (v1.0.0)<br>
            <strong>Penyusun:</strong> Tim Pengembang & GIS Architect Kota Pekanbaru<br>
            <strong>Tanggal Kompilasi:</strong> {$date}<br>
            <strong>Lisensi:</strong> MIT Open Source
        </div>
    </div>

    <h2>1. Pengenalan & Ikhtisar Produk</h2>
    <p>
        <strong>xdiego1st/laravel-mapku</strong> adalah pustaka pemetaan geospasial kelas enterprise untuk ekosistem framework Laravel. Dibangun di atas <strong>MapLibre GL JS</strong> dengan akselerasi GPU WebGL, paket ini menyediakan kontrol penuh atas visualisasi peta titik, linestring, dan polygon tanpa mengunci aplikasi pada penyedia data, wilayah, atau vendor tile tertentu.
    </p>

    <div class="alert">
        <strong>Visi Modularitas:</strong> Paket ini sepenuhnya agnostik terhadap domain bisnis. Logika seperti streaming CCTV, perhitungan jaringan air PDAM, maupun kependudukan diisolasi pada aplikasi konsumen.
    </div>

    <h3>Fitur Utama:</h3>
    <ul>
        <li><strong>7 Lapisan Peta Dasar Bawaan:</strong> OpenStreetMap, Google Satellite HD, Google Hybrid, Esri World Imagery, Dark Mode (CartoDB), Topografi (OpenTopoMap), dan Drone Ortofoto (Esri Clarity).</li>
        <li><strong>Built-in Tile Proxy:</strong> Reverse proxy ubin peta server-side dengan caching untuk mengatasi pemblokiran CORS dan blank map pada Superapp Webview.</li>
        <li><strong>Dynamic Boundary Overlay:</strong> Batas wilayah berbasis GeoJSON (WGS84) dengan default Kota Pekanbaru yang dapat diganti ke kabupaten/kota mana pun via satu baris konfigurasi.</li>
        <li><strong>Multi-Instance Isolation:</strong> Mendukung banyak instance peta dalam satu halaman tanpa tabrakan variabel global.</li>
        <li><strong>Utilitas Geospasial:</strong> Konversi koordinat presisi tinggi UTM Zone 47N ke WGS84 desimal derajat.</li>
    </ul>

    <h2>2. Persyaratan Sistem & Instalasi</h2>
    <table>
        <tr>
            <th>Komponen</th>
            <th>Spesifikasi Minimum</th>
            <th>Rekomendasi</th>
        </tr>
        <tr>
            <td>PHP Version</td>
            <td>PHP 8.2+</td>
            <td>PHP 8.3 / 8.4 / 8.5</td>
        </tr>
        <tr>
            <td>Laravel Framework</td>
            <td>Laravel 10.x / 11.x / 12.x</td>
            <td>Laravel 13.x</td>
        </tr>
        <tr>
            <td>Ekstensi PHP</td>
            <td>json, mbstring, curl</td>
            <td>ext-pcntl, redis</td>
        </tr>
        <tr>
            <td>Database</td>
            <td>Opsional (Tanpa database)</td>
            <td>PostgreSQL + PostGIS / MySQL</td>
        </tr>
    </table>

    <h3>Langkah Instalasi Composer</h3>
    <pre>composer require xdiego1st/laravel-mapku</pre>

    <h3>Publikasi Konfigurasi & Asset</h3>
    <pre>php artisan vendor:publish --tag=mapping-config
php artisan vendor:publish --tag=mapping-assets
php artisan vendor:publish --tag=mapping-geojson</pre>

    <div class="page-break"></div>

    <h2>3. Konfigurasi Batas Wilayah (Boundary Overlay)</h2>
    <p>
        Secara default, paket menggunakan batas administratif Kota Pekanbaru yang tersimpan pada <code>resources/geojson/pekanbaru.geojson</code>. Untuk menggunakan batas wilayah kota/kabupaten lain:
    </p>
    <pre>// config/mapping.php
return [
    'default_map' => [
        'center' => [
            'lat' => -6.2088, // Contoh: Jakarta
            'lng' => 106.8456,
        ],
        'default_zoom' => 12,
    ],
    'boundary' => [
        'enabled' => true,
        'name' => 'DKI Jakarta',
        'source' => 'resources/geojson/jakarta.geojson',
        'fit_bounds' => true,
        'style' => [
            'fill_color' => '#0284c7',
            'fill_opacity' => 0.08,
            'stroke_color' => '#38bdf8',
            'stroke_width' => 2,
        ],
    ],
];</pre>

    <h2>4. Penggunaan Komponen Blade Native</h2>
    <p>
        Gunakan Blade Component <code>&lt;x-mapping-map /&gt;</code> pada template mana pun:
    </p>
    <pre>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    @mappingStyles
&lt;/head&gt;
&lt;body&gt;
    &lt;x-mapping-map
        id="peta-utama"
        height="600px"
        :zoom="12"
        :boundary="config('mapping.boundary')"
        :markers="\$markers"
    /&gt;

    @mappingScripts
&lt;/body&gt;
&lt;/html&gt;</pre>

    <h2>5. Built-in Tile Proxy & Keamanan</h2>
    <p>
        Tile proxy melayani request ubin pada route <code>/api/mapping/tile/{layer}/{z}/{x}/{y}</code>. Tile disimpan pada cache server selama 7 hari, secara signifikan menghemat kuota upstream, mempercepat loading, dan menjamin peta tidak blank saat dibuka di dalam aplikasi mobile webview.
    </p>

    <h2>6. Utilitas Geospasial: Konversi UTM</h2>
    <pre>use Laragiser\Mapping\Support\CoordinateConverter;

// Konversi UTM Zone 47N ke WGS84 Lat/Lng
[\$lng, \$lat] = CoordinateConverter::utmToLatLng(772450.0, 56120.0, 47, true);

// Konversi WGS84 Lat/Lng ke UTM
\$utm = CoordinateConverter::latLngToUtm(\$lat, \$lng);
// Returns: ['easting' =&gt; 772450.0, 'northing' =&gt; 56120.0, 'zone' =&gt; 47, 'hemisphere' =&gt; 'N']</pre>

    <h2>7. FAQ & Troubleshooting</h2>
    <p><strong>Q: Apakah paket ini membutuhkan PostGIS?</strong><br>
    A: Tidak. Paket ini berfokus pada rendering WebGL di sisi klien dan abstraksi data. Format standar yang digunakan adalah GeoJSON (WGS84).</p>

    <p><strong>Q: Bagaimana cara merender dua peta sekaligus dalam satu halaman?</strong><br>
    A: Cukup berikan prop <code>id</code> yang berbeda pada masing-masing komponen. Instance engine diisolasi secara otomatis.</p>
</body>
</html>
HTML;
    }
}
