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

        return ($response == 0) ? true : false;
    }

    public function redownload($hash)
    {
        $rtorrent = $this->rtorrent();
        $rtorrent->call('d.stop', [$hash]);
        $rtorrent->call('d.close', [$hash]);
        $rtorrent->call('f.multicall', [$hash, '', 'f.set_create_queued=0', 'f.set_resize_queued=0']);
        $rtorrent->call('d.check_hash', [$hash]);
        $rtorrent->call('d.open', [$hash]);
        $rtorrent->call('d.start', [$hash]);
    }
}
