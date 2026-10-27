# Laravel Mapku (`xdiego1st/laravel-mapku`)

Enterprise-grade Reusable GIS & Interactive Mapping Package for Laravel based on **MapLibre GL JS**, featuring Built-in Tile Proxy, Multi-Instance Isolation, and Dynamic Boundary Management.

---

## Fitur Utama

- **Framework-Native:** Dibuat khusus untuk Laravel 10, 11, 12, dan 13 dengan PHP 8.2+.
- **MapLibre GL JS Engine:** Performa tinggi dengan akselerasi GPU WebGL.
- **7 Lapisan Peta Dasar (Basemaps):** OpenStreetMap, Google Satellite HD, Google Hybrid, Esri World Imagery, Dark Mode (CartoDB), Topografi (OpenTopoMap), dan Drone Ortofoto (Esri Clarity).
- **Built-in Tile Proxy:** Mencegah masalah CORS dan peta blank pada Superapp Webview dengan server-side caching 7 hari.
- **Dynamic Boundary Overlay:** Batas wilayah administratif berbasis GeoJSON (WGS84). Bawaan default Kota Pekanbaru yang dapat diganti ke kabupaten/kota mana pun via konfigurasi tanpa menyentuh source code.
- **Multi-Instance Isolation:** Mendukung banyak instance peta dalam satu halaman tanpa tabrakan variabel global.
- **Blade & Livewire Ready:** Komponen Blade native `<x-mapping-map />` dan interaktif dashboard `<livewire:mapping-canvas />`.

---

## Instalasi

```bash
composer require xdiego1st/laravel-mapku
```

Publikasikan file konfigurasi dan asset:

```bash
php artisan mapping:publish
```

---

## Penggunaan Cepat (First Map)

```blade
<!DOCTYPE html>
<html>
<head>
    @mappingStyles
</head>
<body>
    <x-mapping-map
        id="peta-pertama"
        height="600px"
        :zoom="12"
        :boundary="config('mapping.boundary')"
    />

    @mappingScripts
</body>
</html>
```

---

## Mengganti Batas Wilayah

Buka `config/mapping.php`:

```php
'boundary' => [
    'enabled' => true,
    'name' => 'Kota Surabaya',
    'source' => 'resources/geojson/surabaya.geojson',
    'fit_bounds' => true,
],
```

---

## Lisensi

Didistribusikan di bawah lisensi MIT. Silakan lihat [LICENSE](LICENSE) untuk informasi lebih lanjut.
