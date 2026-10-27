# Laravel Mapku (`xdiego1st/laravel-mapku`)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/xdiego1st/laravel-mapku.svg?style=flat-square)](https://packagist.org/packages/xdiego1st/laravel-mapku)
[![Total Downloads](https://img.shields.io/packagist/dt/xdiego1st/laravel-mapku.svg?style=flat-square)](https://packagist.org/packages/xdiego1st/laravel-mapku)
[![License](https://img.shields.io/packagist/l/xdiego1st/laravel-mapku.svg?style=flat-square)](https://packagist.org/packages/xdiego1st/laravel-mapku)

Enterprise-grade GIS & Real-time Live Tracking Interactive Mapping Package for **Laravel 10, 11, 12, dan 13** based on **MapLibre GL JS**, featuring Built-in Tile Proxy Caching (Anti-CORS), Multi-Instance Isolation, Dynamic GeoJSON Boundary Management, Real-time Fleet Tracking, and High-Performance Glassmorphism UI.

---

## 🚀 Fitur Utama

- **Framework-Native & Modern:** Mendukung PHP 8.2+ dan Laravel 10, 11, 12, hingga 13.
- **GPU-Accelerated WebGL Rendering:** MapLibre GL JS engine memberikan rendering 60 FPS yang sangat halus dan responsif.
- **7 Lapisan Basemap Premium:**
  1. Google Satellite HD
  2. Google Hybrid
  3. OpenStreetMap
  4. Esri World Imagery
  5. Dark Mode (CartoDB Dark Matter)
  6. Topografi (OpenTopoMap)
  7. Drone Orthophoto (Esri Clarity)
- **Built-in Server-Side Tile Proxy:** Solusi permanen mengatasi masalah CORS dan peta blank pada Superapp Webview (Android/iOS) dengan caching 7 hari di disk server.
- **Real-Time Fleet & Live Tracking (`marker-type="truck"`):**
  - Pin armada kendaraan custom DOM dengan **Pulsating Radar Beacon** animasi radar sinyal.
  - Dukungan rotasi arah kompas (*heading*) secara real-time.
  - Sinkronisasi instan via Laravel Echo / WebSockets (`updateEntityLocation()`).
  - Label kode armada presisi tengah bawah tanpa lag atau jitter saat zoom.
- **Interactive Spatial Inspector:** Telemetri koordinat (Latitude, Longitude), level zoom, dan Point-in-Polygon (PIP) deteksi kecamatan/kelurahan saat kursor bergerak.
- **Collapsible Glassmorphism Legend Panel:** Kartu legenda modern dengan hitungan otomatis per kategori dan pencegahan teks overflow.
- **Floating Telemetry Command Bar:** Status ringkasan jumlah armada/aset per status dengan badge interaktif.
- **Omni Search & Complex Filter Drawer:** Pencarian instan berdasarkan ID, nama titik, wilayah, status, dan kategori.
- **Theme Customizer 2.0:** Preset warna (Sky, Emerald, Amber, Rose, Violet, Cyan, Slate) dengan slider real-time untuk opacity, blur, saturasi, dan border.
- **Multi-Instance Isolation:** Mendukung banyak peta dalam satu halaman web tanpa tabrakan event listener atau state.

---

## 📦 Instalasi

Install package melalui Composer:

```bash
composer require xdiego1st/laravel-mapku
```

Publikasikan file konfigurasi dan aset statis (CSS, JS, Fonts):

```bash
php artisan vendor:publish --tag=mapping-config
php artisan vendor:publish --tag=mapping-assets --force
```

---

## 🛠️ Penggunaan Dasar

### 1. Memuat Asset di Blade Layout

Tambahkan direktif `@mappingStyles` di `<head>` dan `@mappingScripts` sebelum penutup `</body>`:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Peta Spasial</title>
    @mappingStyles
</head>
<body class="w-full h-full">
    {{ $slot }}

    @mappingScripts
</body>
</html>
```

### 2. Komponen Peta Standar (GIS / Titik Aset)

```blade
<x-mapping-map
    id="peta-aset-utama"
    height="100vh"
    width="100%"
    :center="['lat' => 0.507068, 'lng' => 101.447779]"
    :zoom="12"
    :markers="$markers"
    :categories="['CCTV', 'PDAM', 'PEMDA']"
    theme="sky"
    :enable-spatial-inspector="true"
    :enable-legend="true"
    :enable-telemetry="true"
    :enable-gis-controls="true"
/>
```

---

## 🚛 Real-Time Fleet & Live Tracking Armada

Untuk pelacakan armada kendaraan dinas, truk logistik, atau ambulans secara real-time:

### 1. Definisi di Blade / Livewire

Gunakan atribut `marker-type="truck"`:

```blade
<div wire:ignore class="w-full h-screen">
    <x-mapping-map
        id="armada-tracking-map"
        height="100vh"
        width="100vw"
        :center="['lat' => 0.533000, 'lng' => 101.448000]"
        :zoom="13"
        :markers="$markers"
        :categories="['DALAM_PERJALANAN', 'SAMPAI_LOKASI', 'PROSES_PENGERJAAN', 'DITUGASKAN', 'SIAGA']"
        marker-type="truck"
        :telemetry-metrics="[
            ['key' => 'DALAM_PERJALANAN', 'label' => 'Dalam Perjalanan', 'color' => 'blue'],
            ['key' => 'SAMPAI_LOKASI', 'label' => 'Sampai Lokasi', 'color' => 'amber'],
            ['key' => 'PROSES_PENGERJAAN', 'label' => 'Proses Pengerjaan', 'color' => 'emerald'],
            ['key' => 'DITUGASKAN', 'label' => 'Ditugaskan', 'color' => 'purple'],
            ['key' => 'SIAGA', 'label' => 'Siaga di Pool', 'color' => 'slate'],
        ]"
        brand-title="Live Tracking Armada"
        brand-badge="UPT"
        theme="sky"
        :enable-spatial-inspector="true"
        :enable-legend="true"
        :enable-telemetry="true"
        :enable-detail-drawer="true"
    />
</div>
```

### 2. Format Marker Tracking

Kirimkan array marker dengan struktur berikut:

```php
$markers[] = [
    'id' => 'armada_1',
    'title' => 'TRUK-01',
    'subtitle' => 'Budi Santoso (Supir)',
    'category' => 'DALAM_PERJALANAN',
    'status' => 'aktif',
    'status_color' => 'blue', // blue, amber, emerald, purple, slate
    'lat' => 0.512345,
    'lng' => 101.456789,
    'marker_type' => 'truck',
    'extra' => [
        'armada_code' => 'TRK-01',
        'heading' => 85, // Derajat rotasi arah kompas (0 - 360)
        'speed_kmh' => 45,
        'plate_number' => 'BM 1234 PAL',
        'color' => '#2563eb',
    ],
    'attributes' => [
        'Supir' => 'Budi Santoso',
        'Kontak' => '0812-3456-7890',
        'Kecepatan' => '45 km/jam',
        'Status' => 'Meluncur ke Lokasi Pemohon',
    ]
];
```

### 3. Pembaruan Real-Time via JavaScript API

Dapatkan instance peta dan jalankan method `updateEntityLocation()` saat menerima broadcast event (misal via Laravel Echo):

```javascript
window.Echo.channel('armada.1')
    .listen('.ArmadaLocationUpdated', (e) => {
        window.LaravelMapping.get('armada-tracking-map')?.updateEntityLocation(
            'armada_' + e.armada_id, // ID Marker
            parseFloat(e.lat),       // Latitude
            parseFloat(e.lng),       // Longitude
            parseFloat(e.speed_kmh), // Kecepatan (opsional)
            parseFloat(e.heading)    // Arah Rotasi Derajat (opsional)
        );
    });
```

---

## 🎨 Mengganti Batas Wilayah (GeoJSON Boundary)

Di file `config/mapping.php`:

```php
'boundary' => [
    'enabled' => true,
    'name' => 'Kota Pekanbaru',
    'source' => 'resources/geojson/pekanbaru.geojson',
    'fit_bounds' => true,
    'endpoints' => [
        'kota' => '/api/mapping/boundary/kota',
        'kecamatan' => '/api/mapping/boundary/kecamatan',
        'kelurahan' => '/api/mapping/boundary/kelurahan',
    ],
],
```

---

## 💡 Best Practices & Solusi Troubleshooting

### 1. Menghindari Layar Hitam / Blank Saat Polling di Livewire
Bungkus komponen `<x-mapping-map>` di dalam container yang memiliki atribut `wire:ignore`:
```blade
<div wire:ignore class="w-full h-full">
    <x-mapping-map ... />
</div>
```
Ini mencegah mekanisme *DOM Morphing* bawaan Livewire mereset canvas WebGL MapLibre saat terjadi re-render server state.

### 2. Posisi Marker Bebas & Smooth Tanpa Lag Saat Zooming
Pada versi `v1.0.5`, seluruh wrapper marker truk custom DOM diatur dengan:
- `position: absolute !important; top: 0 !important; left: 0 !important;`
- Menggunakan `will-change: transform;` tanpa `transition: transform` pada elemen pembungkus root.
Hal ini menjamin MapLibre GL JS dapat menggerakkan seluruh pin armada dengan performa 60 FPS WebGL canvas camera tanpa keterlambatan (lag/jitter) atau tumpukan kolom vertikal 48px.

### 3. Teks Legenda Terbungkus Rapi
Kontainer Legenda Map diatur dengan `width: 250px`, `box-sizing: border-box`, dan teks bantuan wrapping otomatis di dalam card glassmorphism agar tidak meluap ke luar card atau menabrak pill telemetri bawah.

---

## 📄 Lisensi

Paket ini dirilis di bawah lisensi [MIT License](LICENSE).
Dikembangkan oleh **Rasyid Abdul Halim, S.Tr.Kom** untuk ekosistem aplikasi geospasial modern di Indonesia.
