<?php

namespace Tests\Feature\Foundation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IdentitySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_identity_tables_exist_with_secure_password_columns(): void
    {
        foreach (['admins', 'customers', 'stores', 'riders'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected table [{$table}] to exist.");
            $this->assertTrue(Schema::hasColumn($table, 'password'), "Expected [{$table}.password].");
            $this->assertFalse(Schema::hasColumn($table, 'legacy_password'), 'Legacy test passwords must not be migrated.');
        }
    }

    public function test_foundation_package_tables_exist(): void
    {
        foreach (['personal_access_tokens', 'roles', 'permissions', 'model_has_roles'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected table [{$table}] to exist.");
        }
    }
}
