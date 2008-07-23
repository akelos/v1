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


require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailBase.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailHeader.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailPart.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailParser.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailComposer.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailEncoding.php');

class AkMailMessage extends AkMailBase
{


    /**
     * Specify the from address for the message.
     */
    function setFrom($from)
    {
        $this->from = $from;
    }

    function getFrom()
    {
        return $this->_getMessageHeaderFieldFormated($this->from);
    }

    function getTo()
    {
        return $this->getRecipients();
    }

    function getRecipients()
    {
        return $this->_getMessageHeaderFieldFormated($this->recipients);
    }

    function getBcc()
    {
        return $this->_getMessageHeaderFieldFormated($this->bcc);
    }

    function getCc()
    {
        return $this->_getMessageHeaderFieldFormated($this->cc);
    }

    function setTo($to)
    {
        //$this->to = $to;
        $this->setRecipients($to);
    }

    function setDate($date = null, $validate = true)
    {
        $date = trim($date);
        $is_valid =  preg_match("/^".AK_ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION."$/",$date);
        $date = !$is_valid ? date('r', (empty($date) ? Ak::time() : (!is_numeric($date) ? strtotime($date) : $date))) : $date;

        if($validate && !$is_valid  && !preg_match("/^".AK_ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION."$/",$date)){
            trigger_error(Ak::t('You need to supply a valid RFC 2822 date. You can just leave the date field blank or pass a timestamp and Akelos will automatically format the date for you'), E_USER_ERROR);
        }

        $this->date = $date;
    }

    function setSentOn($date)
    {
        $this->setDate($date);
    }

    function setReturnPath($return_path)
    {
        $this->returnPath = $return_path;
    }

    /**
     * Defaults to "1.0", but may be explicitly given if needed.
     */
    function setMimeVersion($mime_version = null)
    {
        $this->mime_version = empty($mime_version) ? ((empty($this->mime_version) && !empty($this->parts)) ? '1.0' : $this->mime_version) : $mime_version;
    }

    /**
     * The recipient addresses for the message, either as a string (for a single
     * address) or an array (for multiple addresses).
     */
    function setRecipients($recipients)
    {
        $this->recipients = join(", ", (array)Ak::toArray($recipients));
        $this->setHeader('To',$this->getTo());
    }

    /**
     * Specify the subject of the message.
     */
    function setSubject($subject)
    {
        $this->subject = $subject;
    }

    function getSubject($charset = null)
    {
        $charset = empty($charset) ? $this->getCharset() : $charset;
        return AkActionMailerQuoting::quoteIfNecessary($this->subject, $charset);
    }

    function _getMessageHeaderFieldFormated($address_header_field)
    {
        $charset = empty($this->charset) ? AK_ACTION_MAILER_DEFAULT_CHARSET : $this->charset;
        return join(", ",AkActionMailerQuoting::quoteAnyAddressIfNecessary(Ak::toArray($address_header_field), $charset));
    }
}


?>
