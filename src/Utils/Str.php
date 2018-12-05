<?php

namespace RtorrentCleaner\Utils;

class Str
{
    public static function truncate(string $text, int $maxChars = 100, string $separator = '[...]')
    {
        if (($length = strlen($text)) > $maxChars) {
            return substr_replace($text, $separator, $maxChars / 2, $length - $maxChars);
        } else {
            return $text;
        }
    }

    public static function convertFileSize(int $octets, int $round)
    {
        $unit = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];

        for ($i = 0; $octets >= 1024; $i++) {
            $octets = $octets / 1024;
        }

        return round($octets, 2).' '.$unit[$i];
    }

    public static function getPattern($exclude)
    {
        // exclude file with pattern
        $pattern = explode('|', $exclude);
        $notName = [];

        foreach ($pattern as $value) {
            if (empty($value) === false) {
                $notName[] = $value;
            }
        }

        return $notName;
    }
}
