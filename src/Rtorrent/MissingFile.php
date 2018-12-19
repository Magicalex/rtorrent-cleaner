<?php

namespace RtorrentCleaner\Rtorrent;

class MissingFile extends ListingFile
{
    public function findTorrentHash($data, $missingFile)
    {
        foreach ($data as $torrent) {
            foreach ($torrent['files'] as $file) {
                if ($file['name'] == $missingFile) {
                    $hash = $torrent['hash'];
                    break;
                }
            }
        }

        return $hash;
    }
}
