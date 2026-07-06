<?php

namespace Tests\Feature\Schema;

use Tests\TestCase;

class LegacyAdminScreenCoverageTest extends TestCase
{
    public function test_every_root_legacy_admin_screen_is_documented(): void
    {
        $legacyRoot = base_path('../MilkMan_web');
        $document = file_get_contents(base_path('docs/modules/admin-legacy-screens.md'));

        $this->assertIsString($document);

        $files = glob($legacyRoot.'/*.php') ?: [];

        $this->assertNotEmpty($files, 'Expected root legacy admin PHP screens to be available.');

        foreach ($files as $file) {
            $basename = basename($file);

            $this->assertStringContainsString(
                '`'.$basename.'`',
                $document,
                "Expected [{$basename}] to be mapped in docs/modules/admin-legacy-screens.md.",
            );
        }
    }
}
