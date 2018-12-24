<?php

namespace Rtorrent\Cleaner\Utils;

use Rtorrent\Cleaner\Rtorrent\Connect;
use Symfony\Component\Finder\Finder;

class Directory extends Connect
{
    public function getEmptyDirectory()
    {
        $emptyDirectory = [];
        $finder = new Finder();
        $finder->in($this->home)->directories();

        foreach ($finder as $dir) {
            $isEmpty = true;
            $handle = opendir($dir->getPathname());

            while (($entry = readdir($handle)) !== false) {
                if ($entry != '.' && $entry != '..') {
                    $isEmpty = false;
                    break;
                }
            }

            closedir($handle);

            if ($isEmpty === true) {
                $emptyDirectory[] = $dir->getPathname();
            }
        }

        return $emptyDirectory;
    }

    public function removeDirectory($emptyDirectory)
    {
        foreach ($emptyDirectory as $dir) {
            rmdir($dir);
            $list[] = Str::truncate($dir);
        }

        return $list;
    }
}
