<?php

namespace App\Support;

class Code39Barcode
{
    private const PATTERNS = [
        '0' => 'nnnwwnwnn',
        '1' => 'wnnwnnnnw',
        '2' => 'nnwwnnnnw',
        '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw',
        '5' => 'wnnwwnnnn',
        '6' => 'nnwwwnnnn',
        '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn',
        '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw',
        'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn',
        'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn',
        'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw',
        'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn',
        'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww',
        'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn',
        'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn',
        'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww',
        'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn',
        'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw',
        'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn',
        'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn',
        'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw',
        '.' => 'wwnnnnwnn',
        ' ' => 'nwwnnnwnn',
        '$' => 'nwnwnwnnn',
        '/' => 'nwnwnnnwn',
        '+' => 'nwnnnwnwn',
        '%' => 'nnnwnwnwn',
        '*' => 'nwnnwnwnn',
    ];

    public static function svg(string $value, int $height = 56): string
    {
        $encoded = '*'.strtoupper($value).'*';
        $narrow = 2;
        $wide = 5;
        $gap = 2;
        $quiet = 8;
        $x = $quiet;
        $bars = [];

        foreach (str_split($encoded) as $char) {
            if (! isset(self::PATTERNS[$char])) {
                continue;
            }

            foreach (str_split(self::PATTERNS[$char]) as $index => $part) {
                $width = $part === 'w' ? $wide : $narrow;

                if ($index % 2 === 0) {
                    $bars[] = sprintf(
                        '<rect x="%d" y="0" width="%d" height="%d" fill="#111827"/>',
                        $x,
                        $width,
                        $height
                    );
                }

                $x += $width;
            }

            $x += $gap;
        }

        $width = $x + $quiet;

        return sprintf(
            '<svg class="barcode-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" role="img" aria-label="Codigo de barras %s">%s</svg>',
            $width,
            $height,
            e($value),
            implode('', $bars)
        );
    }
}
