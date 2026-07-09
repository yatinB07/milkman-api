<?php

namespace Tests\Feature\Admin;

use App\Models\Zone;
use App\Repositories\ZoneRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneSpatialBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_zone_can_be_found_for_lat_lng_point(): void
    {
        Zone::factory()->create([
            'title' => 'Ahmedabad Active',
            'coordinates' => 'POLYGON((23.0000 72.5000,23.1000 72.5000,23.1000 72.6000,23.0000 72.6000,23.0000 72.5000))',
            'is_active' => true,
        ]);

        $zone = app(ZoneRepository::class)->findActiveContainingPoint(23.0500, 72.5500);

        $this->assertNotNull($zone);
        $this->assertSame('Ahmedabad Active', $zone->title);
    }

    public function test_point_lookup_ignores_inactive_and_soft_deleted_zones(): void
    {
        Zone::factory()->create([
            'title' => 'Inactive Match',
            'coordinates' => 'POLYGON((23.0000 72.5000,23.1000 72.5000,23.1000 72.6000,23.0000 72.6000,23.0000 72.5000))',
            'is_active' => false,
        ]);

        $deletedZone = Zone::factory()->create([
            'title' => 'Deleted Match',
            'coordinates' => 'POLYGON((23.0000 72.5000,23.1000 72.5000,23.1000 72.6000,23.0000 72.6000,23.0000 72.5000))',
            'is_active' => true,
        ]);
        $deletedZone->delete();

        $zone = app(ZoneRepository::class)->findActiveContainingPoint(23.0500, 72.5500);

        $this->assertNull($zone);
    }
}
