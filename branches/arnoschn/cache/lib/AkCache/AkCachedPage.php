<?php

class AkCachedPage extends AkObject
{
    var $_raw_contents;
    var $_headerSeparator;
    var $_options;
    var $_encodingAliases = array('gzip','x-gzip', 'compress', 'x-compress');
    function __construct(&$contents, $header_separator, $options = array())
    {
        $this->_raw_contents = $contents;
        $this->_headerSeparator = $header_separator;
        $this->_options = $options;
    }
    function _handleIfModifiedSince($modifiedTimestamp, $exit=true,$sendHeaders = true, $returnHeaders = false)
    {
        $headers = array();
        $expiryTimestamp = time() + 60*60;
        $time = null;
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            $ifModifiedSince = preg_replace('/;.*$/', '', $time);
            $timestamp = strtotime($ifModifiedSince);
        } else {
            $timestamp = 0;
        }
        
        
        $gmTime = mktime(gmdate('H'), gmdate('i'), gmdate('s'), gmdate('m'), gmdate('d'), gmdate('Y'));
        $time = time();
        $diff = $time - $gmTime;
        if ($modifiedTimestamp <= $timestamp) {
            if ($sendHeaders) {
                header('HTTP/1.1 304 Not Modified');
            }
            if ($returnHeaders) {
                $headers[] = 'Status: 304';
            }
            $addHeaders = $this->_sendAdditionalHeaders($sendHeaders, $returnHeaders);
            $headers = array_merge($addHeaders, $headers);
            if ($exit) {
                exit;
            }
            
            if ($returnHeaders) {
                
                return $headers;
            }
        } else {
            if ($sendHeaders) {
                header('Last-Modified: '.gmdate('D, d M Y H:i:s', $modifiedTimestamp).' GMT');
            } else if ($returnHeaders) {
                $headers[]='Last-Modified: '.gmdate('D, d M Y H:i:s', $modifiedTimestamp).' GMT';
            }
        }
        return $headers;
    }
    
    function _handleEtag($headers,$sendHeaders, $returnHeaders, $exit)
    {
        $outHeaders = array();
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            foreach ($headers as $header) {
                if (stristr($header,'etag: '.$_SERVER['HTTP_IF_NONE_MATCH'])) {
                    if ($sendHeaders) {
                        header('HTTP/1.1 304 Not Modified');
                    }
                    if ($returnHeaders) {
                        $outHeaders[] = 'Status: 304';
                        
                    }
                    if ($exit) {
                        exit;
                    }
                    break;
                }
            }
        }
        return $outHeaders;
    }
    
    function render($exit=true, $sendHeaders=true, $returnHeaders=false)
    {

        list($modifiedTimestamp,$headersSerialized,$contents) = @split($this->_headerSeparator,$this->_raw_contents,3);
        

        $headers = @unserialize($headersSerialized);
        $headers = !is_array($headers)?array():$headers;
        $etagHeaders = $this->_handleEtag($headers, $sendHeaders,$returnHeaders, $exit);
        $sentHeaders = array();
        $sentHeaders = $this->_handleIfModifiedSince(intval($modifiedTimestamp),$exit, $sendHeaders, $returnHeaders);
        
        $acceptedEncodings = $this->_getAcceptedEncodings();
        if ($sendHeaders) {
            foreach ($headers as $header) {
                
                header($this->_handleEncodingAliases($header, $acceptedEncodings));
            }
            
        }
        $addHeaders = $this->_sendAdditionalHeaders($sendHeaders, $returnHeaders);
        if (is_array($addHeaders)) {
            $headers = array_merge($addHeaders, $headers);
        }
        $headers = array_merge($etagHeaders, $headers);
        echo $contents;
        if ($returnHeaders) {
            return array_merge($sentHeaders,$headers);
        }
        $exit?exit:null;
    }
    function _handleEncodingAliases($header, $acceptedEncodings)
    {
        $parts = split(': ',$header);
        if (strtolower($parts[0])=='content-encoding' && 
            isset($parts[1]) &&
            in_array($parts[1],$this->_encodingAliases)) {
            $acceptedEncodings = array_intersect($acceptedEncodings,$this->_encodingAliases);
            if (isset($acceptedEncodings[0])) {
                $header =$parts[0].': '.$acceptedEncodings[0];
            }
        }
        return $header;
    }
    function _getAcceptedEncodings()
    {
        $encodings = isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'';
        $encodings = preg_split('/\s*,\s*/',$encodings);
        return $encodings;
    }
    
    function _sendAdditionalHeaders($sendHeaders = true, $returnHeaders = false)
    {
        $additionalHeaders = array();
        if (isset($this->_options['headers'])) {
            $additionalHeaders = is_array($this->_options['headers'])?$this->_options['headers']:array($this->_options['headers']);
        }
        if ($sendHeaders) {
            foreach($additionalHeaders as $additionalHeader) {
                header($additionalHeader);
            }
        }
        if ($returnHeaders) {
            return $additionalHeaders;
        }
    }
}