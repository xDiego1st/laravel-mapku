<?php

namespace Laragiser\Mapping\Support;

/**
 * SpatialEntityDTO
 *
 * Generic Data Transfer Object untuk titik / marker pada peta.
 * Digunakan untuk standarisasi format data dari berbagai modul aplikasi
 * (CCTV, PDAM, Aset, Kependudukan, Fasilitas Publik, dll.)
 */
class SpatialEntityDTO
{
    public function __construct(
        public string|int $id,
        public float $lat,
        public float $lng,
        public string $title,
        public ?string $subtitle = null,
        public ?string $category = 'GENERAL',
        public ?string $categoryLabel = null,
        public ?string $status = 'ACTIVE',
        public ?string $statusLabel = 'Aktif',
        public ?string $statusColor = 'emerald', // emerald, amber, red, blue, purple, sky, slate
        public ?string $thumbnailUrl = null,
        public array $badges = [], // [['label' => '...', 'color' => '...']]
        public array $attributes = [], // ['Key' => 'Value', ...]
        public array $actions = [], // [['label' => '...', 'type' => 'link|navigation|event', 'payload' => '...']]
        public ?string $district = null,
        public ?string $subdistrict = null,
        public array $extra = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? uniqid('pt_'),
            lat: (float) ($data['lat'] ?? 0),
            lng: (float) ($data['lng'] ?? 0),
            title: $data['title'] ?? 'Tanpa Judul',
            subtitle: $data['subtitle'] ?? null,
            category: $data['category'] ?? 'GENERAL',
            categoryLabel: $data['category_label'] ?? ($data['category'] ?? 'General'),
            status: $data['status'] ?? 'ACTIVE',
            statusLabel: $data['status_label'] ?? ($data['status'] ?? 'Aktif'),
            statusColor: $data['status_color'] ?? 'emerald',
            thumbnailUrl: $data['thumbnail_url'] ?? null,
            badges: $data['badges'] ?? [],
            attributes: $data['attributes'] ?? [],
            actions: $data['actions'] ?? [],
            district: $data['district'] ?? null,
            subdistrict: $data['subdistrict'] ?? null,
            extra: $data['extra'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'category' => $this->category,
            'category_label' => $this->categoryLabel ?? $this->category,
            'status' => $this->status,
            'status_label' => $this->statusLabel ?? $this->status,
            'status_color' => $this->statusColor ?? 'emerald',
            'thumbnail_url' => $this->thumbnailUrl,
            'badges' => $this->badges,
            'attributes' => $this->attributes,
            'actions' => $this->actions,
            'district' => $this->district,
            'subdistrict' => $this->subdistrict,
            'extra' => $this->extra,
        ];
    }
}
