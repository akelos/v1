<?php

class AkCachedPage extends AkObject
{
    var $_raw_contents;
    var $_headerSeparator;
    var $_options;
    function __construct(&$contents, $header_separator, $options = array())
    {
        $this->_raw_contents = $contents;
        $this->_headerSeparator = $header_separator;
        $this->_options = $options;
    }
    function _handleIfModifiedSince($modifiedTimestamp, $exit=true,$sendHeaders = true, $returnHeaders = false)
    {
        $headers = array();
        $expiryTimestamp = $modifiedTimestamp + 60*60;
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
                header('Expires: '.gmdate('D, d M Y H:i:s', $expiryTimestamp) .' GMT');
                header('Last-Modified: '.gmdate('D, d M Y H:i:s', $modifiedTimestamp).' GMT');
                header('Cache-Control: must-revalidate');
            } else if ($returnHeaders) {
                $headers[]='Expires: '.gmdate('D, d M Y H:i:s', $expiryTimestamp) .' GMT';
                $headers[]='Last-Modified: '.gmdate('D, d M Y H:i:s', $modifiedTimestamp).' GMT';
                $headers[]='Cache-Control: must-revalidate';
            }
        }
        return $headers;
    }
    function render($exit=true, $sendHeaders=true, $returnHeaders=false)
    {

        list($modifiedTimestamp,$headersSerialized,$contents) = @split($this->_headerSeparator,$this->_raw_contents,3);
        
        $sentHeaders = array();
        if (isset($this->_options['use_if_modified_since']) && $this->_options['use_if_modified_since']==true) {
            $sentHeaders = $this->_handleIfModifiedSince(intval($modifiedTimestamp),$exit, $sendHeaders, $returnHeaders);
        }
        
        $headers = @unserialize($headersSerialized);
        $headers = !is_array($headers)?array():$headers;
        if ($sendHeaders) {
            foreach ($headers as $header) {
                header($header);
            }
            
        }
        $addHeaders = $this->_sendAdditionalHeaders($sendHeaders, $returnHeaders);
        if (is_array($addHeaders)) {
            $headers = array_merge($addHeaders, $headers);
        }
        echo $contents;
        if ($returnHeaders) {
            return array_merge($sentHeaders,$headers);
        }
        $exit?exit:null;
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