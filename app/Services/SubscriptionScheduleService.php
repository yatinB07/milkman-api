<?php

namespace App\Services;

use App\Exceptions\Customer\SubscriptionScheduleDateNotFoundException;
use Carbon\CarbonImmutable;

class SubscriptionScheduleService
{
    /** @param array<int, string> $selectedDates */
    public function skippedDates(string $totalDates, array $selectedDates): string
    {
        $dates = $this->datesFromString($totalDates);
        $this->assertSelectedDatesExist($dates, $selectedDates);

        return implode(',', array_values(array_diff($dates, $selectedDates)));
    }

    /** @param array<int, string> $selectedDates */
    public function extendedDates(string $totalDates, string $selectedDays, array $selectedDates): string
    {
        $dates = $this->datesFromString($totalDates);
        $this->assertSelectedDatesExist($dates, $selectedDates);

        $remainingDates = array_values(array_diff($dates, $selectedDates));
        $replacementDates = $this->replacementDates(end($dates), $selectedDays, count($selectedDates));

        return implode(',', array_merge($remainingDates, $replacementDates));
    }

    /** @return array<int, string> */
    private function datesFromString(string $dates): array
    {
        return collect(explode(',', $dates))
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<int, string> $currentDates */
    private function assertSelectedDatesExist(array $currentDates, array $selectedDates): void
    {
        if (count(array_intersect($selectedDates, $currentDates)) !== count($selectedDates)) {
            throw new SubscriptionScheduleDateNotFoundException;
        }
    }

    /** @return array<int, string> */
    private function replacementDates(string $lastDate, string $selectedDays, int $count): array
    {
        $date = CarbonImmutable::parse($lastDate);
        $weekdays = collect(explode(',', $selectedDays))
            ->filter(fn (string $day): bool => $day !== '')
            ->map(fn (string $day): int => ((int) $day) + 1)
            ->values()
            ->all();
        $dates = [];

        while (count($dates) < $count) {
            $date = $date->addDay();

            if (in_array((int) $date->format('N'), $weekdays, true)) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }
}
