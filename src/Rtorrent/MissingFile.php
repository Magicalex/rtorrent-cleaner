<?php

namespace Rtorrent\Cleaner\Rtorrent;

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
            $torrent = $this->findTorrentHash($dataRtorrent['data-torrent'], $file);

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
        $this->call('d.erase', [$hash]);
    }

    public function redownload($hash)
    {
        $this->call('d.stop', [$hash]);
        $this->call('d.close', [$hash]);
        $this->call('f.multicall', [$hash, '', 'f.set_create_queued=0', 'f.set_resize_queued=0']);
        $this->call('d.check_hash', [$hash]);
        $this->call('d.open', [$hash]);
        $this->call('d.start', [$hash]);
    }
}
