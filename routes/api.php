<?php

use Illuminate\Support\Facades\Route;
use Laragiser\Mapping\Http\Controllers\BoundaryController;
use Laragiser\Mapping\Http\Controllers\TileProxyController;

Route::prefix('api/mapping')->group(function () {
    // Batas Wilayah GeoJSON Endpoint (mendukung level: kota, kecamatan, kelurahan)
    Route::get('boundary/{level?}', [BoundaryController::class, 'boundary'])
        ->where('level', 'kota|kecamatan|kelurahan')
        ->name('mapping.boundary');

    // Tile Proxy Endpoints (CORS & Superapp webview-proxy friendly)
    Route::get('tile/{layer}/{z}/{x}/{y}', [TileProxyController::class, 'tile'])
        ->where(['layer' => '[a-z_]+', 'z' => '[0-9]+', 'x' => '[0-9]+', 'y' => '[0-9]+'])
        ->name('mapping.tile');

    Route::options('tile/{layer}/{z}/{x}/{y}', [TileProxyController::class, 'preflight'])
        ->where(['layer' => '[a-z_]+', 'z' => '[0-9]+', 'x' => '[0-9]+', 'y' => '[0-9]+']);
});
