<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package AkelosFramework
 * @subpackage AkActionMailer
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

include_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mimeDecode.php');

class AkMailEncoding extends Mail_mimeDecode
{
    /**
     * PEAR's header decoding function is buggy and is not enough tested, so we 
     * override it using the Akelos charset transcoding engine to get the result
     * as UTF-8
     */
    function _decodeHeader($encoded_header)
    {
        $encoded_header =  str_replace(array('_',"\r","\n =?"),array(' ',"\n","\n=?"),
        preg_replace('/\?\=([^=^\n^\r]+)?\=\?/', "?=$1\n=?",$encoded_header));

        $decoded = $encoded_header;
        if(preg_match_all('/(\=\?([^\?]+)\?([BQ]{1})\?([^\?]+)\?\=?)+/i',$encoded_header,$match)){
            foreach ($match[0] as $k=>$encoded){
                $charset = strtoupper($match[2][$k]);
                $decode_function = strtolower($match[3][$k]) == 'q' ? 'quoted_printable_decode' : 'base64_decode';
                $decoded_part = trim(Ak::recode($decode_function($match[4][$k]),'UTF-8', $charset, true));

                $decoded = str_replace(trim($match[0][$k]), $decoded_part, $decoded);
            }
        }
        return trim(preg_replace("/(%0A|%0D|\n+|\r+)/i",'',$decoded));
    }

    function decode()
    {
        $this->_include_bodies = $this->_decode_bodies = $this->_decode_headers = true;

        $structure = $this->_decode($this->_header, $this->_body);
        if ($structure === false) {
            $structure = $this->raiseError($this->_error);
        }

        return $structure;
    }
    
    
    ////

    function _encodeAddress($address_string, $header_name = '', $names = true)
    {
        $headers = '';
        $addresses = Ak::toArray($address_string);
        $addresses = array_map('trim', $addresses);
        foreach ($addresses as $address){
            $address_description = '';
            if(preg_match('#(.*?)<(.*?)>#', $address, $matches)){
                $address_description = trim($matches[1]);
                $address = $matches[2];
            }

            if(empty($address) || !$this->_isAscii($address) || !$this->_isValidAddress($address)){
                continue;
            }
            if($names && !empty($address_description)){
                $address = "<$address>";
                if(!$this->_isAscii($address_description)){
                    $address_description = '=?UTF-8?Q?'.$this->_convertQuotedPrintableTo8Bit($address_description, 0).'?=';
                }
            }
            $headers .= (!empty($headers)?','.AK_MAIL_HEADER_EOL.' ':'').$address_description.$address;
        }

        return empty($headers) ? false : (!empty($header_name) ? $header_name.': '.$headers.AK_MAIL_HEADER_EOL : $headers);
    }

    function _isValidAddress($email)
    {
        return preg_match(AK_EMAIL_REGULAR_EXPRESSION, $email);
    }

    function _convertQuotedPrintableTo8Bit($quoted_string, $max_length = 74, $emulate_imap_8bit = true)
    {
        $lines= preg_split("/(?:\r\n|\r|\n)/", $quoted_string);
        $search_pattern = $emulate_imap_8bit ? '/[^\x20\x21-\x3C\x3E-\x7E]/e' : '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';
        $match_replacement = 'sprintf( "=%02X", ord ( "$0" ) ) ;';
        foreach ((array)$lines as $k=>$line){
            $length = strlen($line);
            if ($length == 0){
                continue;
            }
            $line = preg_replace($search_pattern, $match_replacement, $line );
            $is_last_char = ord($line[$length-1]);
            if (!($emulate_imap_8bit && ($k==count($lines)-1)) && ($is_last_char==0x09) || ($is_last_char==0x20)) {
                $line[$length-1] = '=';
                $line .= ($is_last_char==0x09) ? '09' : '20';
            }
            if ($emulate_imap_8bit) {
                $line = str_replace(' =0D', '=20=0D', $line);
            }
            if($max_length){
                preg_match_all( '/.{1,'.($max_length - 2).'}([^=]{0,2})?/', $line, $match );
                $line = implode( '=' . AK_MAIL_HEADER_EOL, $match[0] );
            }
            $lines[$k] =& $line;
        }
        return implode(AK_MAIL_HEADER_EOL,$lines);
    }

}

?>