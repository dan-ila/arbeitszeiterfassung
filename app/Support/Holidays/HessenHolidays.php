<?php

namespace App\Support\Holidays;

use Carbon\Carbon;

class HessenHolidays
{
    /**
     * @return array<string,string> Map of YYYY-MM-DD => holiday name
     */
    public static function holidaysForYear(int $year): array
    {
        $easterSunday = self::easterSunday($year);

        $holidays = [
            Carbon::create($year, 1, 1, 0, 0, 0, 'Europe/Berlin')->toDateString() => 'Neujahr',
            Carbon::create($year, 5, 1, 0, 0, 0, 'Europe/Berlin')->toDateString() => 'Tag der Arbeit',
            Carbon::create($year, 10, 3, 0, 0, 0, 'Europe/Berlin')->toDateString() => 'Tag der Deutschen Einheit',
            Carbon::create($year, 12, 25, 0, 0, 0, 'Europe/Berlin')->toDateString() => '1. Weihnachtsfeiertag',
            Carbon::create($year, 12, 26, 0, 0, 0, 'Europe/Berlin')->toDateString() => '2. Weihnachtsfeiertag',

            $easterSunday->copy()->subDays(2)->toDateString() => 'Karfreitag',
            $easterSunday->copy()->addDays(1)->toDateString() => 'Ostermontag',
            $easterSunday->copy()->addDays(39)->toDateString() => 'Christi Himmelfahrt',
            $easterSunday->copy()->addDays(50)->toDateString() => 'Pfingstmontag',
            $easterSunday->copy()->addDays(60)->toDateString() => 'Fronleichnam',
        ];

        ksort($holidays);
        return $holidays;
    }

    public static function holidayName(Carbon $date): ?string
    {
        $map = self::holidaysForYear((int) $date->year);
        return $map[$date->toDateString()] ?? null;
    }

    public static function isWeekend(Carbon $date): bool
    {
        // ISO-8601: 6 = Saturday, 7 = Sunday
        return in_array($date->dayOfWeekIso, [6, 7], true);
    }

    /**
     * Block creating "add time" requests on weekends and holidays.
     */
    public static function isBlockedForAdd(Carbon $date): bool
    {
        return self::isWeekend($date) || self::holidayName($date) !== null;
    }

    private static function easterSunday(int $year): Carbon
    {
        // Meeus/Jones/Butcher algorithm for Gregorian Easter
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day, 0, 0, 0, 'Europe/Berlin');
    }
}
