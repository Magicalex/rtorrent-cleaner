<?php

namespace Rtcleaner;

class Helpers
{
    public static function truncate($text, $maxChars = 100, $separator = '...')
    {
        if (($length = strlen($text)) > $maxChars) {
            return substr_replace($text, $separator, $maxChars / 2, $length - $maxChars);
        }

        return $text;
    }

    public static function convertFileSize($octets, $round = 0)
    {
        if ($octets === 0) {
            return 0;
        }

        $unit = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];
        for ($i = 0; $octets >= 1024; $i++) {
            $octets /= 1024;
        }

        return round($octets, $round).' '.$unit[$i];
    }

    public static function humanTime($ms)
    {
        $humanTime = '';
        $sec = (int) $ms / 1000;
        $min = floor($sec / 60);
        $sec = floor($sec - $min * 60);
        $ms = floor($ms - ($min * 60000) - ($sec * 1000));

        if ($min > 0) {
            $humanTime = $min.'min ';
        }

        if ($sec > 0) {
            $humanTime .= $sec.'sec ';
        }

        $humanTime .= $ms.'ms';

        return $humanTime;
    }

    public static function title($title, $output)
    {
        $dash = '';
        $characters = iconv_strlen(preg_replace('#<fg=[a-z]+>(.*)<\/>#', '$1', $title)) + 2;
        for ($i = 0; $i < $characters; $i++) {
            $dash .= '─';
        }

        $top = '┌'.$dash.'┐';
        $bottom = '└'.$dash.'┘';

        return $output->writeln([$top, '│ '.$title.' │', $bottom]);
    }

    public static function getParentFolder($array)
    {
        sort($array);
        $directories = [];

        foreach ($array as $value) {
            $test_path = preg_replace('#\/([^\/]+)$#', '', $value);
            if (!in_array($test_path, $directories)) {
                $directories[] = $value;
            }
        }

        return array_unique($directories);
    }

    public static function scgiArgument($string)
    {
        $string = explode(':', $string);

        if (!isset($string[1])) {
            $string[1] = -1;
        }

        return [
            'hostname' => $string[0],
            'port'     => $string[1]
        ];
    }
}
