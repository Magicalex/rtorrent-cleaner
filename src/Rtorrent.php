<?php

namespace Rtcleaner;

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
        $stream = @fsockopen($this->prefix().$this->scgi, $this->port, $error_code, $error_message, 10);

        if (!$stream) {
            throw new \Exception('Unable to connect to rtorrent. '.$error_message.' (code: '.$error_code.')');
        }

        $xmlrpc_options = [
            'output_type' => 'xml',
            'verbosity' => 'no_white_space',
            'version' => 'xmlrpc',
            'encoding' => 'UTF-8'
        ];

        $null = "\x00";
        $content = xmlrpc_encode_request($method, $params, $xmlrpc_options);
        $header = 'CONTENT_LENGTH'.$null.strlen($content).$null.'SCGI'.$null.'1'.$null;
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
        return ($this->port == -1) ? 'unix://':'tcp://';
    }
}
