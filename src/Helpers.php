<?php

namespace Rtorrent\Cleaner;

class Helpers
{
    public static function truncate($text, $maxChars = 100, $separator = '[...]')
    {
        if (($length = strlen($text)) > $maxChars) {
            return substr_replace($text, $separator, $maxChars / 2, $length - $maxChars);
        } else {
            return $text;
        }
    }

    public static function convertFileSize($octets, $round)
    {
        $unit = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];

        for ($i = 0; $octets >= 1024; $i++) {
            $octets = $octets / 1024;
        }

        return round($octets, 2).$unit[$i];
    }

    public static function humanTime($ms)
    {
        $humanTime = '';
        $sec = (int) $ms / 1000;
        $min = floor($sec / 60);
        $sec = floor($sec - $min * 60);
        $ms = floor($ms - ($min * 60000) - ($sec * 1000));

        if ($min > 0) {
            $humanTime = "{$min}min ";
        }

        if ($sec > 0) {
            $humanTime .= "{$sec}sec ";
        }

        $humanTime .= "{$ms}ms";

        return $humanTime;
    }

    public static function array_diff_recursive($arr1, $arr2)
    {
        $outputDiff = [];

        foreach ($arr1 as $key => $value) {
            if (array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $recursiveDiff = self::array_diff_recursive($value, $arr2[$key]);

                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                } else if (! in_array($value, $arr2)) {
                    $outputDiff[$key] = $value;
                }
            } else if (! in_array($value, $arr2)) {
                $outputDiff[$key] = $value;
            }
        }

        return $outputDiff;
    }

    public static function errorMessage($message, $output)
    {
        $spaces = '    ';
        for ($i=0; $i < strlen($message); $i++) {
            $spaces .= ' ';
        }

        return $output->writeln(['<error>'.$spaces.'</>', '<error>  '.$message.'  </>', '<error>'.$spaces.'</>']);
    }
}
