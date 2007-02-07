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

require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailEncoding.php');

class AkMail extends AkObject
{
    var $rawMessage = '';
    var $charset = 'UTF-8';
    var $contentType = 'text/plain';

    function __construct()
    {
        $args = func_get_args();
        if(isset($args[0])){
            if(count($args) == 1 && is_string($args[0])){
                $this->rawMessage = $args[0];
            }elseif(is_array($args[0])){
                $this->_importStructure($args[0]);
            }
        }
    }

    function parse($raw_email)
    {
        $Parser =& new AkMailEncoding($raw_email);
        return new AkMail((array)$Parser->decode());
    }

    function load($email_file)
    {
        $Parser =& new AkMailEncoding(file_get_contents($email_file));
        return new AkMail((array)$Parser->decode());
    }




    function setBody($body)
    {
        $this->body = $body;
    }

    /**
    * Specify the CC addresses for the message.
    */
    function setCc($cc)
    {
        $this->cc = $cc;
    }

    /**
    * Specify the BCC addresses for the message.
    */
    function setBcc($bcc)
    {
        $this->bcc = $bcc;
    }

    /**
     * Specify the charset to use for the message. 
     */
    function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Specify the content type for the message. This defaults to <tt>text/plain</tt>
     * in most cases, but can be automatically set in some situations.
     */
    function setContentType($content_type)
    {
        $this->contentType = $content_type;
    }
    
    /**
     * Specify the content disposition for the message. 
     */
    function setContentDisposition($content_disposition)
    {
        $this->contentDisposition = $content_disposition;
    }

    /**
     * Specify the from address for the message.
     */
    function setFrom($from)
    {
        $this->from = $from;
    }

    function setTo($to)
    {
        //$this->to = $to;
        $this->setRecipients($to);
    }

    /**
     * Specify additional headers to be added to the message.
     */
    function setHeaders($headers)
    {
        foreach ((array)$headers as $name=>$value){
            $this->setHeader($name, $value);
        }
    }

    function setHeader($name, $value)
    {
        $this->header[strtolower($name)] = $value;
    }


    function setParts($parts)
    {
        foreach ($parts as $part){
            $this->setPart($part);
        }
    }

    /**
     * Add a part to a multipart message, with an array of options like (content-type, charset, body, headers, etc.).
     * 
     *   function my_mail_message()
     *   {
     *     $this->setPart("text/plain", array(
     *       'body' => "hello, world",
     *       'transfer_encoding' => "base64"
     *     ));
     *   }
     */
    function setPart()
    {
        $args = func_get_args();
        $options = count($args) >= 1 ? array_shift($args) : array();
        $options['content_type'] = empty($options['content_type']) && count($args) == 1 ? array_shift($args) : (empty($options['content_type'])?null:$options['content_type']);
        $options = array_merge(array('content_disposition' => 'inline', 'transfer_encoding' => 'quoted-printable'), $options);
        
        $Part =& new AkMail();
        $Part->_isPart = true;
        $Part->set($options);
        $this->parts[] =& $Part;
    }
      
    /**
     * Add an attachment to a multipart message. This is simply a part with the
     * content-disposition set to "attachment".
     * 
     *     $this->setAttachment("image/jpg", array(
     *       'body' => Ak::file_get_contents('hello.jpg'),
     *       'filename' => "hello.jpg"
     *     ));
     */
    function setAttachment()
    {
        $args = func_get_args();
        $options = count($args) >= 1 ? array_shift($args) : array();
        $options['content_type'] = empty($options['content_type']) && count($args) == 1 ? array_shift($args) : (empty($options['content_type'])?null:$options['content_type']);
        $options = array_merge(array('disposition' => 'attachment', 'transfer_encoding' => 'base64'), $options);
        $this->setPart($options);
    }
    


    function setDate($date)
    {
        $this->date = $date;
    }

    function setMessageId($id)
    {
        $this->messageId = $id;
    }

    function setReturnPath($return_path)
    {
        $this->returnPath = $return_path;
    }


    /**
    * Specify the order in which parts should be sorted, based on content-type.
    * This defaults to the value for the +default_implicitPartsOrder+.
    */
    function setImplicitPartsOrder($implicitPartsOrder)
    {
        $this->implicitPartsOrder = $implicitPartsOrder;
    }

    /**
     * Defaults to "1.0", but may be explicitly given if needed.
     */
    function setMimeVersion($mime_version)
    {
        $this->mimeVersion = $mime_version;
    }

    /**
     * The recipient addresses for the message, either as a string (for a single
     * address) or an array (for multiple addresses).
     */
    function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
    * The date on which the message was sent. If not set (the default), the
    * header will be set by the delivery agent.
    */
    function setSentOn($date)
    {
        $this->sentOn = $date;
    }


    /**
     * Specify the subject of the message.
     */
    function setSubject($subject)
    {
        $this->subject = $subject;
    }


    /**
     * Generic setter
     * 
     * Calling $this->set(array('body'=>'Hello World', 'subject' => 'First subject'));
     * is the same as calling $this->setBody('Hello World'); and $this->setSubject('First Subject');
     * 
     * This simplifies creating mail objects from datasources.
     * 
     * If the method does not exists the parameter will be added to the header.
     */
    function set($attributes = array())
    {
        if(!empty($attributes['body'])){
            $this->setBody($attributes['body']);
        }
        unset($attributes['body']);
        foreach ((array)$attributes as $key=>$value){
            if($key[0] != '_'){
                $method = 'set'.AkInflector::camelize($key);
                if(method_exists($this, $method)){
                    $this->$method($value);
                }else{
                    $this->setHeader($key, $value);
                }
            }
        }
    }


    function sortParts($parts, $order = array())
    {
        $this->_parts_order = array_map('strtolower', empty($order) ? $this->implicitPartsOrder : $order);
        rsort($parts);
        usort($parts, array($this,'_contentTypeComparison'));
        return $parts;
    }

    function _contentTypeComparison($a, $b)
    {
        $a_ct = strtolower($a['content_type']);
        $b_ct = strtolower($b['content_type']);
        $a_in = in_array($a_ct, $this->_parts_order);
        $b_in = in_array($b_ct, $this->_parts_order);
        if($a_in && $b_in){
            $a_pos = array_search($a_ct, $this->_parts_order);
            $b_pos = array_search($b_ct, $this->_parts_order);
            return (($a_pos == $b_pos) ? 0 : (($a_pos < $b_pos) ? -1 : 1));
        }
        return $a_in ? -1 : ($b_in ? 1 : (($a_ct == $b_ct) ? 0 : (($a_ct < $b_ct) ? -1 : 1)));
    }


    function _importStructure($structure = array())
    {
        empty($structure['headers']) ? null : $this->setHeaders($structure['headers'], array('decode'=>false));
        empty($structure['body']) ? null : $this->setBody($structure['body'], array('decode'=>false));
        $this->setContentType($structure['ctype_primary'].'/'.$structure['ctype_secondary']);
        $this->_propagateComonHeaders();
    }

    function _propagateComonHeaders()
    {
        foreach ($this->getCommonHeaders() as $header_key=>$attribute_name){
            if(isset($this->header[$header_key])){
                $method_name = 'set'.ucfirst($attribute_name);
                $this->$method_name($this->header[$header_key]);
                unset($this->header[$header_key]);
            }
        }
    }

    function getCommonHeaders()
    {
        return array(
        'from'=>'from',
        'to' => 'to',
        'cc' => 'cc',
        'bcc' => 'bcc',
        'subject' => 'subject',
        'date' => 'date',
        'content-type' => 'contentType',
        'mime-version' => 'mimeVersion',
        'message-id' => 'messageId',
        'return-path' => 'returnPath',
        );
    }
    
    function getEncodedMail()
    {
        
        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mime.php');
        $Mime =& new Mail_mime();
        //$Mime->getMessage
        echo "<pre>".print_r($this,true)."</pre>";
        die();
    }
    
    function toMail($defaults)
    {
        $Part =& new AkMail();
        list($real_content_type, $ctype_attrs) = $this->_parseContentType($defaults);

        if(empty($this->parts)){

            $Part->setContentTransferEncoding(!empty($this->transfer_encoding) ? $this->transfer_encoding : 'quoted-printable');
            switch (strtolower($this->transfer_encoding)) {
                case 'base64':
                $Part->setBody(chunk_split(base64_encode($this->body)));
                break;

                case 'quoted-printable':
                $Part->setBody(AkActionMailerQuoting::chunkQuoted(AkActionMailerQuoting::quotedPrintableEncode($this->body)));
                break;

                default:
                $Part->setBody($this->body);
                break;
            }

            // Always set the content_type after setting the body and or parts!
            // Also don't set filename and name when there is none (like in
            // non-attachment parts)
            if ($this->content_disposition == 'attachment'){
                unset($this->ctype_attrs['charset']);
                $Part->setContentType($real_content_type, null, array_diff(array_merge(array('filename'=>$this->filename),$ctype_attrs), array('')));
                $Part->setContentDisposition($this->content_disposition ,array_diff(array_merge(array('filename'=>$this->filename),$ctype_attrs), array('')));
            }else{
                $Part->setContentType($real_content_type, null, $ctype_attrs);
                $Part->setContentDisposition($this->content_disposition);
            }
        }else{
            if(is_string($this->body)){
                $Part->setBody($this->body);
                $Part->setContentType($real_content_type, null, $ctype_attrs);
                $Part->setContentDisposition('inline');
                $this->setPart($Part);
            }

            foreach (array_keys($this->parts) as $k){
                $SubPart = strtolower(get_class($this->parts[$k])) != 'akmail' ? $this->parts[$k]->toMail($defaults) : $this->parts[$k];
                $Part->setPart($SubPart);
            }
            if(stristr($real_content_type,'multipart')){
                $Part->setContentType($real_content_type, null, $ctype_attrs);
            }
        }

        $Part->addHeaders($this->header);
        return $Part;
    }
    
    function _parseContentType($defaults=null)
    {
        if(empty($this->content_type)){
            return array((!empty($defaults) && !empty($defaults['content_type']))?$defaults['content_type']:null, array());
        }
        list($ctype, $attrs) = split(";\\s*",$this->content_type);
        if(!empty($attrs)){
            $attributes = array();
            foreach ((array)$attrs as $h=>$s){
                list($k,$v) = array_map('trim',split("=", $s, 2));
                $attributes[$k] = $v;
            }
            $attrs = $attributes;
        }

        $attributes = array_diff(array_merge(array('charset'=>
        (!empty($this->charset) ? $this->charset : (!empty($defaults) && !empty($defaults['charset']) ? $defaults['charset'] : null))
        ),$attrs), array(''));
        return array($ctype, $attributes);
    }

}

?>
