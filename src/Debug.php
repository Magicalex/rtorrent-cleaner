<?php

namespace Rtcleaner;

class Debug
{
    protected $hash;
    protected $rtorrent;

    public function __construct($scgi, $port, $hash)
    {
        $this->hash = $hash;
        $this->rtorrent = new Rtorrent($scgi, $port);
    }

    public function getTorrentInfo()
    {
        return [
            'd.hash'                      => $this->hash,
            'd.accepting_seeders'         => $this->rtorrent->call('d.accepting_seeders', [$this->hash]),
            'd.base_filename'             => $this->rtorrent->call('d.base_filename', [$this->hash]),
            'd.base_path'                 => $this->rtorrent->call('d.base_path', [$this->hash]),
            'd.bitfield'                  => $this->rtorrent->call('d.bitfield', [$this->hash]),
            'd.bytes_done'                => $this->rtorrent->call('d.bytes_done', [$this->hash]),
            'd.chunk_size'                => $this->rtorrent->call('d.chunk_size', [$this->hash]),
            'd.chunks_hashed'             => $this->rtorrent->call('d.chunks_hashed', [$this->hash]),
            'd.chunks_seen'               => $this->rtorrent->call('d.chunks_seen', [$this->hash]),
            'd.complete'                  => $this->rtorrent->call('d.complete', [$this->hash]),
            'd.completed_bytes'           => $this->rtorrent->call('d.completed_bytes', [$this->hash]),
            'd.completed_chunks'          => $this->rtorrent->call('d.completed_chunks', [$this->hash]),
            'd.connection_current'        => $this->rtorrent->call('d.connection_current', [$this->hash]),
            'd.connection_leech'          => $this->rtorrent->call('d.connection_leech', [$this->hash]),
            'd.connection_seed'           => $this->rtorrent->call('d.connection_seed', [$this->hash]),
            'd.creation_date'             => $this->rtorrent->call('d.creation_date', [$this->hash]),
            'd.custom1'                   => $this->rtorrent->call('d.custom1', [$this->hash]),
            'd.custom2'                   => $this->rtorrent->call('d.custom2', [$this->hash]),
            'd.custom3'                   => $this->rtorrent->call('d.custom3', [$this->hash]),
            'd.custom4'                   => $this->rtorrent->call('d.custom4', [$this->hash]),
            'd.custom5'                   => $this->rtorrent->call('d.custom5', [$this->hash]),
            'd.directory'                 => $this->rtorrent->call('d.directory', [$this->hash]),
            'd.directory_base'            => $this->rtorrent->call('d.directory_base', [$this->hash]),
            'd.down.choke_heuristics'     => $this->rtorrent->call('d.down.choke_heuristics', [$this->hash]),
            'd.down.rate'                 => $this->rtorrent->call('d.down.rate', [$this->hash]),
            'd.down.total'                => $this->rtorrent->call('d.down.total', [$this->hash]),
            'd.downloads_max'             => $this->rtorrent->call('d.downloads_max', [$this->hash]),
            'd.downloads_min'             => $this->rtorrent->call('d.downloads_min', [$this->hash]),
            'd.free_diskspace'            => $this->rtorrent->call('d.free_diskspace', [$this->hash]),
            'd.group'                     => $this->rtorrent->call('d.group', [$this->hash]),
            'd.group.name'                => $this->rtorrent->call('d.group.name', [$this->hash]),
            'd.hashing'                   => $this->rtorrent->call('d.hashing', [$this->hash]),
            'd.hashing_failed'            => $this->rtorrent->call('d.hashing_failed', [$this->hash]),
            'd.ignore_commands'           => $this->rtorrent->call('d.ignore_commands', [$this->hash]),
            'd.incomplete'                => $this->rtorrent->call('d.incomplete', [$this->hash]),
            'd.is_active'                 => $this->rtorrent->call('d.is_active', [$this->hash]),
            'd.is_hash_checked'           => $this->rtorrent->call('d.is_hash_checked', [$this->hash]),
            'd.is_hash_checking'          => $this->rtorrent->call('d.is_hash_checking', [$this->hash]),
            'd.is_meta'                   => $this->rtorrent->call('d.is_meta', [$this->hash]),
            'd.is_multi_file'             => $this->rtorrent->call('d.is_multi_file', [$this->hash]),
            'd.is_not_partially_done'     => $this->rtorrent->call('d.is_not_partially_done', [$this->hash]),
            'd.is_open'                   => $this->rtorrent->call('d.is_open', [$this->hash]),
            'd.is_partially_done'         => $this->rtorrent->call('d.is_partially_done', [$this->hash]),
            'd.is_pex_active'             => $this->rtorrent->call('d.is_pex_active', [$this->hash]),
            'd.is_private'                => $this->rtorrent->call('d.is_private', [$this->hash]),
            'd.left_bytes'                => $this->rtorrent->call('d.left_bytes', [$this->hash]),
            'd.load_date'                 => $this->rtorrent->call('d.load_date', [$this->hash]),
            'd.local_id'                  => $this->rtorrent->call('d.local_id', [$this->hash]),
            'd.local_id_html'             => $this->rtorrent->call('d.local_id_html', [$this->hash]),
            'd.max_file_size'             => $this->rtorrent->call('d.max_file_size', [$this->hash]),
            'd.max_size_pex'              => $this->rtorrent->call('d.max_size_pex', [$this->hash]),
            'd.message'                   => $this->rtorrent->call('d.message', [$this->hash]),
            'd.name'                      => $this->rtorrent->call('d.name', [$this->hash]),
            'd.peer_exchange'             => $this->rtorrent->call('d.peer_exchange', [$this->hash]),
            'd.peers_accounted'           => $this->rtorrent->call('d.peers_accounted', [$this->hash]),
            'd.peers_complete'            => $this->rtorrent->call('d.peers_complete', [$this->hash]),
            'd.peers_connected'           => $this->rtorrent->call('d.peers_connected', [$this->hash]),
            'd.peers_max'                 => $this->rtorrent->call('d.peers_max', [$this->hash]),
            'd.peers_min'                 => $this->rtorrent->call('d.peers_min', [$this->hash]),
            'd.peers_not_connected'       => $this->rtorrent->call('d.peers_not_connected', [$this->hash]),
            'd.priority'                  => $this->rtorrent->call('d.priority', [$this->hash]),
            'd.priority_str'              => $this->rtorrent->call('d.priority_str', [$this->hash]),
            'd.ratio'                     => $this->rtorrent->call('d.ratio', [$this->hash]),
            'd.resume'                    => $this->rtorrent->call('d.resume', [$this->hash]),
            'd.save_full_session'         => $this->rtorrent->call('d.save_full_session', [$this->hash]),
            'd.save_resume'               => $this->rtorrent->call('d.save_resume', [$this->hash]),
            'd.size_bytes'                => $this->rtorrent->call('d.size_bytes', [$this->hash]),
            'd.size_chunks'               => $this->rtorrent->call('d.size_chunks', [$this->hash]),
            'd.size_files'                => $this->rtorrent->call('d.size_files', [$this->hash]),
            'd.size_pex'                  => $this->rtorrent->call('d.size_pex', [$this->hash]),
            'd.skip.rate'                 => $this->rtorrent->call('d.skip.rate', [$this->hash]),
            'd.skip.total'                => $this->rtorrent->call('d.skip.total', [$this->hash]),
            'd.state'                     => $this->rtorrent->call('d.state', [$this->hash]),
            'd.state_changed'             => $this->rtorrent->call('d.state_changed', [$this->hash]),
            'd.state_counter'             => $this->rtorrent->call('d.state_counter', [$this->hash]),
            'd.tied_to_file'              => $this->rtorrent->call('d.tied_to_file', [$this->hash]),
            'd.timestamp.finished'        => $this->rtorrent->call('d.timestamp.finished', [$this->hash]),
            'd.timestamp.started'         => $this->rtorrent->call('d.timestamp.started', [$this->hash]),
            'd.tracker_focus'             => $this->rtorrent->call('d.tracker_focus', [$this->hash]),
            'd.tracker_numwant'           => $this->rtorrent->call('d.tracker_numwant', [$this->hash]),
            'd.tracker_size'              => $this->rtorrent->call('d.tracker_size', [$this->hash]),
            'd.up.choke_heuristics'       => $this->rtorrent->call('d.up.choke_heuristics', [$this->hash]),
            'd.up.choke_heuristics.leech' => $this->rtorrent->call('d.up.choke_heuristics.leech', [$this->hash]),
            'd.up.choke_heuristics.seed'  => $this->rtorrent->call('d.up.choke_heuristics.seed', [$this->hash]),
            'd.up.rate'                   => $this->rtorrent->call('d.up.rate', [$this->hash]),
            'd.up.total'                  => $this->rtorrent->call('d.up.total', [$this->hash]),
            'd.uploads_max'               => $this->rtorrent->call('d.uploads_max', [$this->hash]),
            'd.uploads_min'               => $this->rtorrent->call('d.uploads_min', [$this->hash]),
            'd.wanted_chunks'             => $this->rtorrent->call('d.wanted_chunks', [$this->hash])
        ];
    }

    public function getFilesInfo()
    {
        $files = $this->rtorrent->call('f.multicall', [
            $this->hash, '',
            'f.path=',
            'f.size_bytes=',
            'f.completed_chunks=',
            'f.frozen_path=',
            'f.is_create_queued=',
            'f.is_created=',
            'f.is_open=',
            'f.is_resize_queued=',
            'f.last_touched=',
            'f.match_depth_next=',
            'f.match_depth_prev=',
            'f.offset=',
            'f.path_depth=',
            'f.prioritize_first=',
            'f.prioritize_last=',
            'f.priority=',
            'f.range_first=',
            'f.range_second=',
            'f.size_chunks='
        ]);

        foreach ($files as $file) {
            $filesInfos[] = [
                'f.path'             => $file[0],
                'f.size_bytes'       => $file[1],
                'f.completed_chunks' => $file[2],
                'f.frozen_path'      => $file[3],
                'f.is_create_queued' => $file[4],
                'f.is_created'       => $file[5],
                'f.is_open'          => $file[6],
                'f.is_resize_queued' => $file[7],
                'f.last_touched'     => $file[8],
                'f.match_depth_next' => $file[9],
                'f.match_depth_prev' => $file[10],
                'f.offset'           => $file[11],
                'f.path_depth'       => $file[12],
                'f.prioritize_first' => $file[13],
                'f.prioritize_last'  => $file[14],
                'f.priority'         => $file[15],
                'f.range_first'      => $file[16],
                'f.range_second'     => $file[17],
                'f.size_chunks'      => $file[18]
            ];
        }

        return $filesInfos;
    }
}
