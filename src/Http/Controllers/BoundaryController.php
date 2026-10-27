<?php

namespace Laragiser\Mapping\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Laragiser\Mapping\Services\BoundaryService;

class BoundaryController extends Controller
{
    public function __construct(
        protected BoundaryService $boundaryService
    ) {}

    /**
     * Endpoint penyaji GeoJSON batas administratif wilayah dengan dukungan HTTP ETag 304.
     *
     * GET /api/mapping/boundary/{level?}
     */
    public function boundary(Request $request, ?string $level = 'kota'): Response|JsonResponse
    {
        $customSource = $request->query('source');
        $geoJson = $this->boundaryService->getBoundaryGeoJson($customSource, $level ?? 'kota');

        if (!$geoJson) {
            return response()->json([
                'error' => 'Batas wilayah GeoJSON tidak ditemukan atau tidak valid',
            ], 404, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $payload = json_encode($geoJson);
        $etag = $this->boundaryService->getEtag($geoJson);

        if ($request->header('If-None-Match') === $etag || $request->header('If-None-Match') === trim($etag, '"')) {
            return response('', 304, [
                'Access-Control-Allow-Origin' => '*',
                'ETag' => $etag,
            ]);
        }

        return response($payload, 200, [
            'Content-Type' => 'application/json',
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, If-None-Match',
        ]);
    }
}
