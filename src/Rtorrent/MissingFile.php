<?php

namespace RtorrentCleaner\Rtorrent;

class MissingFile extends ListingFile
{

    protected function findTorrentHash($dataRtorrent, $missingFile)
    {
        $id = 0;

        foreach ($dataRtorrent[$id]['files'] as $file) {
            if ($missingFile == $file['name']) {
                $hash = $dataRtorrent[$id]['hash'];
                break;
            }
            $id++;
        }

        return $hash;
    }


    // 1 - trouver le hash du fichier
    // 2 - Construire au faire et à mesure une liste à afficher (ajouter les fichiers qui correspondent au torrent)
    // 3 - afficher que le nom du torrent
    //  -> Des fichiers manquent dans ce torrent (montrer la liste)
    // 4 - Proposer une suppression du torrent OU de redownload le torrent OU ne rien faire
}
