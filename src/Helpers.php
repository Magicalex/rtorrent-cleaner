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

    public static function convertFileSize($octets, $round = 0)
    {
        if ($octets < 0) {
            return 'err';
        } elseif ($octets === 0) {
            return 0;
        }

        $unit = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];
        for ($i = 0; $octets >= 1024; $i++) {
            $octets /= 1024;
        }

        return round($octets, $round).$unit[$i];
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

    public static function find_diff($array_one, $array_two)
    {
        $output = [];
        $diff_array_one = array_column($array_one, 'full_path');
        $diff_array_two = array_column($array_two, 'full_path');
        $diff = array_diff($diff_array_one, $diff_array_two);

        foreach ($diff as $full_path) {
            foreach ($array_one as $value_one) {
                if ($full_path === $value_one['full_path']) {
                    $output[] = $value_one;
                    break;
                }
            }
        }

        return $output;
    }

    public static function errorMessage($message, $output)
    {
        $spaces = '';
        for ($i = 0; $i < strlen($message) + 4; $i++) {
            $spaces .= ' ';
        }

        return $output->writeln(['<error>'.$spaces.'</>', '<error>  '.$message.'  </>', '<error>'.$spaces.'</>']);
    }

    public static function title($title, $output)
    {
        $dash = '';
        $tmp = preg_replace('/<fg=[a-z]+>(.*)<\/>/', '$1', $title);
        for ($i = 0; $i < strlen($tmp); $i++) {
            $dash .= '─';
        }
        $top = '┌'.$dash.'┐';
        $bottom = '└'.$dash.'┘';

        return $output->writeln([$top, '│ '.$title.' │', $bottom, '']);
    }
}
