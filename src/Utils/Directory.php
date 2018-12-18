<?php

namespace RtorrentCleaner\Utils;

use Symfony\Component\Finder\Finder;

class Directory
{
    protected $home;

    public function __construct($home)
    {
        $this->home = $home;
    }

    public function getEmptyDirectory()
    {
        $emptyDirectory = [];
        $finder = new Finder();
        $finder->in($this->home)->directories();

        foreach ($finder as $dir) {
            $isEmpty = true;
            $handle = opendir($dir->getPathname());

            while (false !== ($entry = readdir($handle))) {
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
            $trunc = Str::truncate($dir);
            $list[] = $trunc;
        }

        return $list;
    }
}
