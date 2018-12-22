<?php

namespace Rtorrent\Cleaner\Rtorrent;

class Connect
{
    protected $home;
    protected $rtorrent;
    protected $urlXmlRpc;

    public function __construct($urlXmlRpc)
    {
        $this->urlXmlRpc = $urlXmlRpc;
        $this->rtorrent = $this->rtorrent();
        $this->home = $this->getDefaultHome();
    }

    protected function rtorrent()
    {
        return new \fXmlRpc\Client(
            $this->urlXmlRpc,
            new \fXmlRpc\Transport\HttpAdapterTransport(
                new \Http\Message\MessageFactory\DiactorosMessageFactory(),
                new \Http\Adapter\Guzzle6\Client(new \GuzzleHttp\Client())
            ),
            new \fXmlRpc\Parser\NativeParser(),
            new \fXmlRpc\Serializer\NativeSerializer()
        );
    }

    protected function getDefaultHome()
    {
        return $this->rtorrent->call('directory.default');
    }
}
