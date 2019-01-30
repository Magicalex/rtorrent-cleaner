<?php

namespace Rtorrent\Cleaner\Rtorrent;

class Connect
{
    protected $rtorrent;
    protected $urlXmlRpc;

    public function __construct($urlXmlRpc)
    {
        $this->urlXmlRpc = $urlXmlRpc;
        $this->rtorrent = $this->rtorrent();
    }

    protected function rtorrent()
    {
        $httpClient = new \GuzzleHttp\Client(['verify' => false]);

        return new \fXmlRpc\Client(
            $this->urlXmlRpc,
            new \fXmlRpc\Transport\HttpAdapterTransport(
                new \Http\Message\MessageFactory\DiactorosMessageFactory(),
                new \Http\Adapter\Guzzle6\Client($httpClient)
            ),
            new \fXmlRpc\Parser\NativeParser(),
            new \fXmlRpc\Serializer\NativeSerializer()
        );
    }
}
