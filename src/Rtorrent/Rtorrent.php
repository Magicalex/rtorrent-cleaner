<?php

namespace Rtorrent\Cleaner\Rtorrent;

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
        $response_xml = '';
        $stream = @fsockopen('tcp://'.$this->scgi, $this->port);

        if ($stream === false) {
            throw new \Exception('Unable to connect to rtorrent. Check if rtorrent is running.');
        }

        $content = xmlrpc_encode_request($method, $params, ['encoding' => 'UTF-8']);
        $contentlength = strlen($content);

        // send data
        $header = "CONTENT_LENGTH\x0".$contentlength."\x0"."SCGI\x0"."1\x0";
        $request = strlen($header).':'.$header.','.$content;
        fwrite($stream, $request, strlen($request));

        while ($line = fread($stream, 4096)) {
            $response_xml .= $line;
        }

        fclose($stream);
        $response_xml = self::fix_xml($response_xml);
        $response = xmlrpc_decode($response_xml, 'UTF-8');

        return $response;
    }

    protected static function fix_xml($xml)
    {
        $xml = preg_replace('/^(.*\n){4}/', '', $xml);
        $xml = str_replace('i8>', 'int>', $xml);

        return $xml;
    }
}
