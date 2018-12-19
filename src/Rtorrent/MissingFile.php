<?php

namespace RtorrentCleaner\Rtorrent;

class MissingFile extends ListingFile
{
    protected function findTorrentHash($data, $missingFile)
    {
        foreach ($data as $torrent) {
            foreach ($torrent['files'] as $file) {
                if ($file['name'] == $missingFile) {
                    $hash = $torrent['hash'];
                    $name = $torrent['name'];
                    break;
                }
            }
        }

        return ['hash' => $hash, 'name' => $name];
    }

    public function listTorrentMissingFile($missingFile, $dataRtorrent)
    {
        $torrentMissingFile = [];
        $findHash = false;

        foreach ($missingFile as $file) {
            $torrent = $this->findTorrentHash($dataRtorrent['info'], $file);

            // check if $hash has been already add
            foreach ($torrentMissingFile as $id => $info) {
                if ($info['hash'] == $torrent['hash']) {
                    $torrentMissingFile[$id]['files'][] = $file;
                    $findHash = true;
                    break;
                }
            }

            if ($findHash === false) {
                $torrentMissingFile[] = [
                    'hash'  => $torrent['hash'],
                    'name'  => $torrent['name'],
                    'files' => [$file]
                ];
            }

            $findHash = false;
        }

        return $torrentMissingFile;
    }

    public function deleteTorrent($hash)
    {
        $rtorrent = $this->rtorrent();
        $response = $rtorrent->call('d.erase', [$hash]);

        return ($response == 0) ? true:false;
    }

    public function redownload($hash)
    {

    }
}
