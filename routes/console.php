<?php

use App\Support\LegacySchemaCoverage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('milkman:inspect-legacy-schema', function (): int {
    $this->info('Legacy rows are ignored; this command reports table coverage decisions only.');
    $this->table(
        ['Legacy table', 'Laravel destination', 'Status', 'Notes'],
        array_map(
            static fn (array $decision): array => [
                $decision['legacy'],
                $decision['destination'],
                $decision['status'],
                $decision['notes'],
            ],
            LegacySchemaCoverage::decisions(),
        ),
    );

    return self::SUCCESS;
})->purpose('Print the MilkMan legacy-to-Laravel schema coverage map');

Artisan::command('milkman:verify-schema', function (): int {
    $missing = [];

    foreach (LegacySchemaCoverage::decisions() as $decision) {
        if (! Schema::hasTable($decision['destination'])) {
            $missing[] = $decision;
        }
    }

    foreach ($missing as $decision) {
        $this->error("Missing Laravel destination table [{$decision['destination']}] for legacy table [{$decision['legacy']}].");
    }

    if ($missing !== []) {
        return self::FAILURE;
    }

    $this->info('Verified '.count(LegacySchemaCoverage::decisions()).' legacy table coverage decisions.');

    return self::SUCCESS;
})->purpose('Verify every required legacy table has a Laravel destination table');
