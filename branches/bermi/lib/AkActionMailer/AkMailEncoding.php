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
     * PEAR's header decoding function is buggy and is not enoughtested, so we 
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
}

?>