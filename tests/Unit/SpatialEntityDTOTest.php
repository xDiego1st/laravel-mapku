<?php

namespace Laragiser\Mapping\Tests\Unit;

use Laragiser\Mapping\Support\SpatialEntityDTO;
use PHPUnit\Framework\TestCase;

class SpatialEntityDTOTest extends TestCase
{
    public function test_spatial_entity_dto_instantiation_and_serialization(): void
    {
        $raw = [
            'id' => 'cam-101',
            'lat' => 0.5071,
            'lng' => 101.4478,
            'title' => 'Simpang SKA Mall',
            'subtitle' => 'Traffic Monitoring Live',
            'category' => 'TRAFFIC',
            'category_label' => 'Lalu Lintas',
            'status' => 'ONLINE',
            'status_label' => 'Aktif',
            'status_color' => 'emerald',
            'badges' => [['label' => 'HD', 'color' => 'sky']],
            'district' => 'Payung Sekaki',
        ];

        $dto = SpatialEntityDTO::fromArray($raw);

        $this->assertEquals('cam-101', $dto->id);
        $this->assertEquals(0.5071, $dto->lat);
        $this->assertEquals(101.4478, $dto->lng);
        $this->assertEquals('Simpang SKA Mall', $dto->title);
        $this->assertEquals('TRAFFIC', $dto->category);
        $this->assertEquals('ONLINE', $dto->status);
        $this->assertEquals('emerald', $dto->statusColor);

        $serialized = $dto->toArray();
        $this->assertIsArray($serialized);
        $this->assertEquals('cam-101', $serialized['id']);
        $this->assertEquals('Payung Sekaki', $serialized['district']);
    }

    public function test_spatial_entity_dto_defaults_for_sparse_data(): void
    {
        $dto = SpatialEntityDTO::fromArray([
            'lat' => 0.5,
            'lng' => 101.4,
        ]);

        $this->assertNotEmpty($dto->id);
        $this->assertEquals('Tanpa Judul', $dto->title);
        $this->assertEquals('GENERAL', $dto->category);
        $this->assertEquals('ACTIVE', $dto->status);
    }
}
