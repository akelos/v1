<?php

class AkMailParser
{
    function parse($raw_email)
    {
        list($raw_header, $raw_body) = $this->_getRawHeaderAndBody($raw_email);
        $headers = $this->_getHeaders($raw_header);

        if($content_type = $this->getContentType($headers)){
            foreach ($this->{$this->getContentTypeProcessorMethodName($content_type)}($raw_body, $headers) as $k => $v) {
                $body[$k] = $v;
            }
        }

        $body = empty($body) ? false : (count($body) > 1 ? $body : array_shift($body));

        echo "<pre>".print_r($headers,true)."</pre>";
        echo $body;
        return new AkMail();
    }

    function getContentType($headers)
    {
        return $this->_findHeader('content-type',$headers);
    }

    function getContentTypeProcessorMethodName($content_type)
    {
        $content_type = is_array($content_type) ? $content_type['value'] : $content_type;
        $method_name = 'getParsed'.ucfirst(strtolower(substr("text/plain",0,strpos("text/plain","/")))).'Body';
        return method_exists($this, $method_name) ? $method_name : 'getParsedTextBody';
    }

    function getContentDisposition($headers)
    {
        return $this->_findHeader('content-disposition',$headers);
    }


    function getParsedTextBody($body, $headers)
    {
        return array($this->_getDecodedBody($body, $headers));
    }

    function getParsedMultipartBody($body, $headers)
    {

    }

    function getParsedMessageBody($body, $headers)
    {

    }
    
    function _getDecodedBody($body, $headers)
    {
        $encoding = trim(strtolower($this->_findHeaderValue('content-transfer-encoding', $headers)));
        $charset = trim(strtolower($this->_findHeaderAttributeValue('content-type','charset', $headers)));

        if($encoding == 'base64'){
            $body = base64_decode($body);
        }elseif($encoding == 'quoted-printable'){
            $body = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", preg_replace("/=\r?\n/", '', $body));
        }
        return empty($charset) ? $body : Ak::recode($body, 'UTF-8', $charset);
    }

    function _findHeaderValue($name, $headers)
    {
        $header = $this->_findHeader($name, $headers);
        return !empty($header['value']) ? $header['value'] : false;
    }
    
    function _findHeaderAttributeValue($name, $attribute, $headers)
    {
        $header = $this->_findHeader($name, $headers);
        return !empty($header['attributes'][$attribute]) ? $header['attributes'][$attribute] : false;
    }

    function _findHeader($name, $headers)
    {
        $results = array();
        foreach ($headers as $header) {
            if(isset($header['name']) && strtolower($header['name']) == $name){
                $results[] = $header;
            }
        }
        return empty($results) ? false : (count($results) > 1 ? $results : array_shift($results));
    }

    function _getHeaders($raw_headers)
    {
        $headers = array();
        if(!empty($raw_headers)){
            foreach (explode("\n",$raw_headers."\n") as $header_line){
                if(!empty($header_line)){
                    $headers[] = $this->_parseHeaderLine($header_line);
                }
            }
        }

        return $headers;
    }

    function _parseHeaderLine($header_line)
    {
        $header = array();
        if(preg_match("/^([A-Za-z\-]+)\:? *(.*)$/",$header_line,$match)){
            $header['name'] = $match[1];
            $header['value'] = $match[2];
        }
        $this->_decodeHeader_($header);
        $this->_extractAttributesForHeader_($header);
        return $header;
    }

    function _extractAttributesForHeader_(&$header)
    {
        $attributes = array();
        if(preg_match_all("/(([A-Za-z\-]+)=([^;]*);?)+/", $header['value'], $matches)){
            $header['value'] = str_replace($matches[0],'', $header['value']);
            foreach ($matches[0] as $k=>$match){
                $attributes[$matches[2][$k]] = trim($matches[3][$k],';"');
            }
        }

        $header['value'] = trim($header['value']," ;");

        if(strstr($header['value'],';') && strtolower($header['name']) != 'date' &&
        preg_match("/([; ])*(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun), *)?(\d\d?)".
        " +(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) +(\d\d\d\d) ".
        "+(\d{2}:\d{2}(?::\d\d)) +([\( ]*(UT|GMT|EST|EDT|CST|CDT|MST|MDT|".
        "PST|PDT|[A-Z]|(?:\+|\-)\d{4})[\) ]*)+/",$header['value'],$match)){
            $header['value'] = str_replace($match[0],'', $header['value']);
            $attributes['Date'] = trim(str_replace('  ',' ',$match[0]),"; ");
        }

        if(!empty($attributes)){
            $header['attributes'] = $attributes;
        }
    }

    function _decodeHeader_(&$header)
    {
        $header_value = $header['value'];
        if(preg_match_all('/(\=\?([^\?]+)\?([BQ]{1})\?([^\?]+)\?\=?)+/i', $header_value, $match)){
            foreach ($match[0] as $k=>$encoded){
                $charset = strtoupper($match[2][$k]);
                $decode_function = strtolower($match[3][$k]) == 'q' ? 'quoted_printable_decode' : 'base64_decode';
                $decoded_part = trim(Ak::recode($decode_function(str_replace('_',' ',$match[4][$k])), AK_ACTION_MAILER_DEFAULT_CHARSET, $charset, true));

                $header_value = str_replace(trim($match[0][$k]), $decoded_part, $header_value);
            }
        }
        $header_value = trim(preg_replace("/(%0A|%0D|\n+|\r+)/i",'',$header_value));
        if($header_value != $header['value']){
            $header['encoded'] = $header['value'];
            $header['value'] = $header_value;
        }
    }

    function _getRawHeaderAndBody($raw_part)
    {
        return
        array_map('trim',
        preg_split("/\n\n/",
        preg_replace("/(\n[\t ]+)+/",' ', // Join multiline headers
        str_replace(array("\r\n","\r"),"\n", $raw_part."\n") // Lets keep it simple and use only \n for decoding
        ),2));
    }
}

?>