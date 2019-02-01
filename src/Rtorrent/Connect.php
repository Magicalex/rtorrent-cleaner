<?php

namespace Rtorrent\Cleaner\Rtorrent;

class Connect
{
    protected $rtorrent;
    protected $urlXmlRpc;
    protected $username;
    protected $password;

    public function __construct($urlXmlRpc, $username, $password)
    {
        $this->urlXmlRpc = $urlXmlRpc;
        $this->username = $username;
        $this->password = $password;
        $this->rtorrent = $this->rtorrent();
    }

    protected function rtorrent()
    {
        $options = ['verify' => false];

        if ($this->username !== null && $this->password !== null) {
            $options['auth'] = [$this->username, $this->password];
        }

        $httpClient = new \GuzzleHttp\Client($options);

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
