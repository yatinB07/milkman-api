<?php

namespace Tests\Feature\Schema;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacySchemaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_schema_command_passes_when_all_legacy_destinations_exist(): void
    {
        $this->artisan('milkman:verify-schema')
            ->expectsOutputToContain('Verified 32 legacy table coverage decisions.')
            ->assertSuccessful();
    }

    public function test_verify_schema_command_fails_when_a_required_destination_is_missing(): void
    {
        Schema::drop('milk_data');

        $this->artisan('milkman:verify-schema')
            ->expectsOutputToContain('Missing Laravel destination table [milk_data] for legacy table [tbl_milk].')
            ->assertFailed();
    }

    public function test_inspect_legacy_schema_command_prints_the_coverage_map_without_using_legacy_rows(): void
    {
        $this->artisan('milkman:inspect-legacy-schema')
            ->expectsOutputToContain('Legacy rows are ignored; this command reports table coverage decisions only.')
            ->expectsOutputToContain('tbl_normal_order')
            ->expectsOutputToContain('orders')
            ->assertSuccessful();
    }
}
