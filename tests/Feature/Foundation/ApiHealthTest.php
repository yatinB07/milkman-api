<?php

namespace Tests\Feature\Foundation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_endpoint_returns_versioned_json_contract(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'name' => 'MilkMan API',
                    'status' => 'ok',
                    'version' => 'v1',
                ],
            ]);
    }
}
