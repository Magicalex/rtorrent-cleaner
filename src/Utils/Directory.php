<?php

namespace Rtorrent\Cleaner\Utils;

use Rtorrent\Cleaner\Rtorrent\ListingFile;
use Symfony\Component\Finder\Finder;

class Directory extends ListingFile
{
    public function getEmptyDirectory()
    {
        $emptyDirectory = [];
        $finder = new Finder();
        $finder->in($this->directories)->directories();

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
