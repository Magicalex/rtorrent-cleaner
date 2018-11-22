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
}
