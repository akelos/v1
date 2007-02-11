<?php

class AkMailParser
{
    var $decode_body = true;
    var $content_type = 'text/plain';
    var $recode_messages = true;
    var $recode_to_charset = AK_ACTION_MAILER_DEFAULT_CHARSET;
    var $raw_message = '';
    var $options = array();

    var $html_charset_on_recoding_failure = false;

    var $headers = array();
    var $body;
    var $parts;

    function AkMailParser($options = array())
    {
        $this->options = $options;
        $default_options = array(
        'content_type' => $this->content_type,
        'decode_body' => $this->decode_body,
        );
        $options = array_merge($default_options, $options);
        foreach ($options as $k=>$v){
            if($k[0] != '_'){
                $this->$k = $v;
            }
        }
    }

    function parse($raw_message = '')
    {
        $Mail = new stdClass();
        $raw_message = empty($raw_message) ? $this->raw_message : $raw_message;
        if(!empty($raw_message)){
            list($raw_header, $raw_body) = $this->_getRawHeaderAndBody($raw_message);
            $Mail->headers = $this->headers = $this->getParsedRawHeaders($raw_header);
            $this->{$this->getContentTypeProcessorMethodName()}($raw_body);
        }
        $this->_expandHeadersOnMailObject($Mail);
        $Mail->body = $this->body;
        $Mail->pats = $this->parts;
        return $Mail;
    }

    function getContentTypeProcessorMethodName()
    {
        $content_type = $this->findHeaderValueOrDefaultTo('content-type', $this->content_type);
        $method_name = 'getParsed'.ucfirst(strtolower(substr($content_type,0,strpos($content_type,"/")))).'Body';
        return method_exists($this, $method_name) ? $method_name : 'getParsedTextBody';
    }

    function getContentDisposition()
    {
        return $this->_findHeader('content-disposition');
    }


    function getParsedTextBody($body)
    {
        $this->body = $this->_getDecodedBody($body);
    }

    function getParsedMultipartBody($body)
    {
        $boundary = trim($this->_findHeaderAttributeValue('content-type','boundary'));
        $this->content_type = $this->options['content_type'] = (trim(strtolower($this->_findHeaderValue('content-type'))) == 'multipart/digest' ? 'message/rfc822' : 'text/plain');


        $this->parts = array();
        $raw_parts = array_diff(array_map('trim',(array)preg_split('/([\-]{0,2}'.preg_quote($boundary).'[\-]{0,2})+/', $body)),array(''));
        foreach ($raw_parts as $raw_part){
            $Parser = new AkMailParser($this->options);
            $this->parts[] = $Parser->parse($raw_part);
        }
    }

    function getParsedMessageBody($body)
    {
        $Parser = new AkMailParser($this->options);
        $this->body = $Parser->parse($raw_part);
    }

    function _getDecodedBody($body)
    {
        $encoding = trim(strtolower($this->_findHeaderValue('content-transfer-encoding')));
        $charset = trim(strtolower($this->_findHeaderAttributeValue('content-type','charset')));

        if($encoding == 'base64'){
            $body = base64_decode($body);
        }elseif($encoding == 'quoted-printable'){
            $body = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", preg_replace("/=\r?\n/", '', $body));
        }
        return empty($charset) ? $body : ($charset && $this->recode_messages ? Ak::recode($body, $this->recode_to_charset, $charset, $this->html_charset_on_recoding_failure) : $body);
    }

    function _findHeaderValue($name)
    {
        $header = $this->_findHeader($name);
        return !empty($header['value']) ? $header['value'] : false;
    }

    function _findHeaderAttributeValue($name, $attribute)
    {
        $header = $this->_findHeader($name);
        return !empty($header['attributes'][$attribute]) ? $header['attributes'][$attribute] : false;
    }

    function findHeaderValueOrDefaultTo($name, $default)
    {
        $value = $this->_findHeaderValue($name);
        return !empty($value) ? $value : $default;
    }

    function _findHeader($name)
    {
        $results = array();
        foreach ($this->headers as $header) {
            if(isset($header['name']) && strtolower($header['name']) == $name){
                $results[] = $header;
            }
        }
        return empty($results) ? false : (count($results) > 1 ? $results : array_shift($results));
    }

    function getParsedRawHeaders($raw_headers)
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
        if(!empty($header['value'])){
            $header_value = $header['value'];
            if(preg_match_all('/(\=\?([^\?]+)\?([BQ]{1})\?([^\?]+)\?\=?)+/i', $header_value, $match)){
                foreach ($match[0] as $k=>$encoded){
                    $charset = strtoupper($match[2][$k]);
                    $decode_function = strtolower($match[3][$k]) == 'q' ? 'quoted_printable_decode' : 'base64_decode';
                    $decoded_part = trim(
                    Ak::recode($decode_function(str_replace('_',' ',$match[4][$k])), $this->recode_to_charset, $charset, $this->html_charset_on_recoding_failure)

                    );

                    $header_value = str_replace(trim($match[0][$k]), $decoded_part, $header_value);
                }
            }
            $header_value = trim(preg_replace("/(%0A|%0D|\n+|\r+)/i",'',$header_value));
            if($header_value != $header['value']){
                $header['encoded'] = $header['value'];
                $header['value'] = $header_value;
            }
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

    function _expandHeadersOnMailObject(&$Mail)
    {
        if(!empty($Mail->headers)){
            foreach ($Mail->headers as $k=>$details){
                if (empty($details['name'])) {
                    continue;
                }
                $caption = AkInflector::underscore($details['name']);
                if(!in_array($caption, array('headers','body','parts'))){
                    if(!empty($details['value'])){
                        if(empty($Mail->$caption)){
                            $Mail->$caption = $details['value'];
                        }elseif (!empty($Mail->$caption) && is_array($Mail->$caption)){
                            $Mail->{$caption}[] = $details['value'];
                        }else{
                            $Mail->$caption = array($Mail->$caption, $details['value']);
                        }
                    }
                    if(!empty($details['attributes'])){
                        $Mail->{$caption.'_attributes'} = $details['attributes'];
                    }
                }
            }
        }
    }
}

?>