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
        return $rtorrent = new fXmlRpc\Client(
            $this->urlXmlRpc,
            new fXmlRpc\Transport\HttpAdapterTransport(
                new Http\Message\MessageFactory\DiactorosMessageFactory(),
                new Http\Adapter\Guzzle6\Client(new GuzzleHttp\Client())
            )
        );
    }
}
