<?php

namespace RtorrentCleaner\Rtorrent;

use Zend\XmlRpc\Client;

class Connect
{
    protected $home;
    protected $urlXmlRpc;

    public function __construct($urlXmlRpc, $home)
    {
        $this->home = $home;
        $this->urlXmlRpc = $urlXmlRpc;
    }

    protected function rtorrent()
    {
        return new Client($this->urlXmlRpc);
    }
}
