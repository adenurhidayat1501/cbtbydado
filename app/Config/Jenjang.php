<?php

namespace App\Config;

class Jenjang
{
    public const SD = 1;
    public const SMP = 2;
    public const SMA = 3;

    public static function getNames(): array
    {
        return [
            self::SD => 'SD / MI',
            self::SMP => 'SMP / MTs',
            self::SMA => 'SMA / SMK / MA',
        ];
    }

    public static function getName(int $jenjang): string
    {
        return self::getNames()[$jenjang] ?? 'SMP / MTs';
    }

    public static function getLevels(int $jenjang): array
    {
        return match ($jenjang) {
            self::SD => [1, 2, 3, 4, 5, 6],
            self::SMP => [7, 8, 9],
            self::SMA => [10, 11, 12],
            default => [7, 8, 9],
        };
    }

    public static function getDefaultPgOptions(int $jenjang): int
    {
        return match ($jenjang) {
            self::SD => 3, // Opsi A, B, C (atau 4)
            self::SMP => 4, // Opsi A, B, C, D
            self::SMA => 5, // Opsi A, B, C, D, E
            default => 4,
        };
    }

    public static function hasJurusan(int $jenjang): bool
    {
        return $jenjang === self::SMA;
    }

    public static function getOptionLetters(int $count): array
    {
        $all = ['A', 'B', 'C', 'D', 'E'];
        return array_slice($all, 0, max(1, min(5, $count)));
    }
}

