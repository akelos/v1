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

defined('AK_ACTION_MAILER_CHARS_NEEDING_QUOTING_REGEX') ? null :
define('AK_ACTION_MAILER_CHARS_NEEDING_QUOTING_REGEX', "/[\\000-\\011\\013\\014\\016-\\037\\177-\\377]/");


class AkActionMailerQuoting
{

    /**
     * Convert the given text into quoted printable format, with an instruction
     * that the text be eventually interpreted in the given charset.
     */
    function quotedPrintable($text, $charset = 'utf-8')
    {
        $text = str_replace(' ','_', preg_replace('/[^a-z ]/ie', 'AkActionMailerQuoting::quotedPrintableEncode("$0")', $text));
        return "=?$charset?Q?$text?=";
    }

    /**
     * Convert the given character to quoted printable format, taking into
     * account multi-byte characters
     */
    function quotedPrintableEncode($character)
    {
        $characters = unpack('C*', $character);
        $result = '';
        for ($i=1,$count = count($characters);$i<=$count;$i++){
            $result .= sprintf( "=%02X", $characters[$i]);
        }
        return $result;
    }
    
    
    /**
    * Quote the given text if it contains any "illegal" characters
    */
    function quoteIfNecessary($text, $charset = 'utf-8')
    {
        return preg_match(AK_ACTION_MAILER_CHARS_NEEDING_QUOTING_REGEX,$text) ? AkActionMailerQuoting::quotedPrintable($text,$charset) : $text;
    }

    /**
    * Quote any of the given strings if they contain any "illegal" characters
    */
    function quoteAnyIfNecessary($strings = array(), $charset = 'utf-8')
    {
        foreach ($strings as $k=>$v){
            $strings[$k] = AkActionMailerQuoting::quoteIfNecessary($charset, $v);
        }
        return $strings;
    }

    /**
     *  Quote the given address if it needs to be. The address may be a
     * regular email address, or it can be a phrase followed by an address in
     * brackets. The phrase is the only part that will be quoted, and only if
     * it needs to be. This allows extended characters to be used in the
     * "to", "from", "cc", and "bcc" headers.
     */
    function quoteAddressIfNecessary($address, $charset = 'utf-8')
    {
        if(is_array($address)){
            foreach ($address as $k=>$v){
                $address[$k] = AkActionMailerQuoting::quoteAddressIfNecessary($address, $charset);
            }
            return $address;
        }elseif (preg_match('/^(\S.*)\s+(<.*>)$/', $address, $match)){
            $address = $match[2];
            $phrase = AkActionMailerQuoting::quoteIfNecessary(preg_replace('/^[\'"](.*)[\'"]$/', '$1', $match[1]), $charset);
            return "\"$phrase\" $address";
        }else{
            return $address;
        }
    }

    /**
     *  Quote any of the given addresses, if they need to be.
     */
    function quoteAnyAddressIfNecessary($address = array(), $charset = 'utf-8')
    {
        foreach ($address as $k=>$v){
            $address[$k] = AkActionMailerQuoting::quoteAddressIfNecessary($charset, $v);
        }
        return $address;
    }

    function chunkQuoted($quoted_string, $max_length = 74)
    {
        if(empty($max_length)){
            return $quoted_string;
        }
        $lines= preg_split("/(?:\r\n|\r|\n)/", $quoted_string);
        foreach ((array)$lines as $k=>$line){
            if (empty($line)){
                continue;
            }
            preg_match_all( '/.{1,'.($max_length - 2).'}([^=]{0,2})?/', $line, $match );
            $line = implode( "=\n", $match[0] );

            $lines[$k] =& $line;
        }
        return implode("\n",$lines);
    }
}

?>