<?php

namespace Rtorrent\Cleaner;

class Rtorrent
{
    protected $scgi;
    protected $port;

    public function __construct($scgi, $port)
    {
        $this->scgi = $scgi;
        $this->port = $port;
    }

    public function call($method, $params = [])
    {
        $stream = @fsockopen($this->prefix().$this->scgi, $this->port);

        if (!$stream) {
            throw new \Exception('Unable to connect to rtorrent. Check if rtorrent is running.');
        }

        $content = xmlrpc_encode_request($method, $params, ['encoding' => 'UTF-8']);
        $header = "CONTENT_LENGTH\x0".strlen($content)."\x0"."SCGI\x0"."1\x0";
        $request = strlen($header).':'.$header.','.$content;
        fwrite($stream, $request, strlen($request));
        $xml = stream_get_contents($stream);
        fclose($stream);

        $xml = preg_replace('#^(.*\n){4}#', '', $xml);
        $xml = preg_replace('#\<i8>(.+)\<\/i8>#', '<string>$1</string>', $xml);
        $xml = preg_replace('#\<i4>(.+)\<\/i4>#', '<string>$1</string>', $xml);

        return xmlrpc_decode($xml, 'UTF-8');
    }

    protected function prefix()
    {
        if ($this->port == -1) {
            return 'unix://';
        } else {
            return 'tcp://';
        }
    }
}
