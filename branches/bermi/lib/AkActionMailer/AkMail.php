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

include_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailEncoding.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMimeMail.php');

class AkMail extends Mail
{
    var $rawMessage = '';
    var $charset = AK_ACTION_MAILER_DEFAULT_CHARSET;
    var $contentType = 'text/plain';

    function AkMail()
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
    function setCharset($charset, $append_to_content_type_as_attribute = true)
    {
        $this->_charset = $charset;
        if($append_to_content_type_as_attribute){
            $this->setContenttypeAttributes(array('charset'=>$charset));
        }
    }

    /**
     * Specify the content type for the message. This defaults to <tt>text/plain</tt>
     * in most cases, but can be automatically set in some situations.
     */
    function setContentType($content_type)
    {
        list($this->contentType, $ctype_attrs) = $this->_getContentTypeAndAttributes($content_type);
        $this->setContenttypeAttributes($ctype_attrs);
    }


    function getContentType()
    {
        return $this->contentType.$this->getContenttypeAttributes();
    }

    function setContenttypeAttributes($attributes = array())
    {
        foreach ($attributes as $key=>$value){
            if(strtolower($key) == 'charset'){
                $this->setCharset($value, false);
            }
            $this->content_type_attributes[$key] = $value;
        }
    }

    function getContenttypeAttributes()
    {
        $attributes = '';
        if(!empty($this->content_type_attributes)){
            foreach ((array)$this->content_type_attributes as $key=>$value){
                $attributes .= ";$key=$value";
            }
        }
        return $attributes;
    }

    /**
     * Specify the content disposition for the message. 
     */
    function setContentDisposition($content_disposition)
    {
        $this->contentDisposition = $content_disposition;
    }

    /**
     * Specify the content transfer encoding for the message. 
     */
    function setContentTransferEncoding($content_transfer_encoding)
    {
        $this->contentTransferEncoding = $content_transfer_encoding;
    }

    /**
     * Specify the from address for the message.
     */
    function setFrom($from)
    {
        $this->from = $from;
    }


    function _getAddressHeaderFieldFormated($address_header_field)
    {
        return AkActionMailerQuoting::quoteAddressIfNecessary(Ak::toArray($address_header_field));
    }

    function getFrom()
    {
        return $this->_getAddressHeaderFieldFormated($this->from);
    }


    function getTo()
    {
        return $this->_getAddressHeaderFieldFormated($this->recipients);
    }

    function getBcc()
    {
        return $this->_getAddressHeaderFieldFormated($this->bcc);
    }

    function getCc()
    {
        return $this->_getAddressHeaderFieldFormated($this->cc);
    }



    function setTo($to)
    {
        //$this->to = $to;
        $this->setRecipients($to);
    }

    /**
     * Specify additional headers to be added to the message.
     */
    function setHeaders($headers, $options = array())
    {
        foreach ((array)$headers as $name=>$value){
            $this->setHeader($name, $value, $options);
        }
    }

    function setHeader($name, $value, $options = array())
    {
        if(is_array($value)){
            $this->setHeaders($value, $options);
        }else{
            $this->header[$this->_getFormatedHeaderAttribute($name)] = $value;
        }
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
        $options = array_merge(array('content_disposition' => 'inline', 'content_transfer_encoding' => 'quoted-printable'), $options);

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
        $options = array_merge(array('content_disposition' => 'attachment', 'content_transfer_encoding' => 'base64'), $options);
        $this->setPart($options);
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
        $this->setHeader('to',$recipients);
    }


    /**
     * Specify the subject of the message.
     */
    function setSubject($subject)
    {
        $this->subject = $subject;
    }

    function getSubject()
    {
        return AkActionMailerQuoting::quoteIfNecessary($this->subject, empty($this->_charset) ? AK_ACTION_MAILER_DEFAULT_CHARSET : $this->_charset);
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

    /**
     * Moves attributes passed to the header to be used as intance attributes
     * if they have an setter available
     */
    function _propagateComonHeaders()
    {
        foreach (array_merge((array)$this->header, (array)$this) as $k=>$v){
            if($k[0] != '_' && $this->_belongsToHeaders($k)){
                $attribute_name = $this->_getFormatedHeaderAttribute($k);
                if(isset($this->header[$attribute_name])){
                    $attribute_setter = 'set'.ucfirst($k);
                    if(method_exists($this, $attribute_setter)){
                        $this->$attribute_setter($this->header[$attribute_name]);
                        unset($this->header[$attribute_name]);
                    }
                }
            }
        }
    }

    function _moveMailInstanceAttributesToHeaders()
    {
        foreach ((array)$this as $k=>$v){
            if($k[0] != '_' && $this->_belongsToHeaders($k)){
                $attribute_getter = 'get'.ucfirst($k);
                $attribute_name = $this->_getFormatedHeaderAttribute(AkInflector::underscore($k));
                $this->setHeader($attribute_name, method_exists($this,$attribute_getter) ? $this->$attribute_getter() : $v);
            }
        }
    }

    function _getFormatedHeaderAttribute($attribute_name)
    {
        return trim(join("-",(array_map('ucwords',explode(" ",str_replace(array('_','-','  '),' ',$attribute_name)." "))))," -:");
    }

    function _belongsToHeaders($attribute)
    {
        return !in_array(strtolower($attribute),array('body','recipients','part','parts','rawmessage','sep','implicitpartsorder','header','headers'));
    }

    function getEncoded()
    {
        if(empty($this->date)){
            $this->setDate();
        }
        $this->_moveMailInstanceAttributesToHeaders();
        $headers = array_map(array('AkActionMailerQuoting','chunkQuoted'),$this->header);
        unset($headers['Charset']);
        $this->_sanitizeHeaders($headers);
        list(,$text_headers) = Mail::prepareHeaders($headers);
        return $text_headers.AK_ACTION_MAILER_EOL.AK_ACTION_MAILER_EOL.trim(AkActionMailerQuoting::quoteIfNecessary($this->body));
        /*


        $Mail =& new AkMimeMail();


        $this->Mime->_build_params['text_encoding'] = '8bit';
        $this->Mime->_build_params['html_charset'] = $this->Mime->_build_params['text_charset'] = $this->Mime->_build_params['head_charset'] = Ak::locale('charset');

        $this->Mime->setTxtBody($options['text']);
        $this->Mime->setHtmlBody($options['html']);
        foreach ($html_images as $html_image){
        $this->Mime->addHTMLImage(AK_CACHE_DIR.DS.'tmp'.DS.$html_image, 'image/png');
        }
        foreach ((array)$options['attachments'] as $attachment){
        $this->Mime->addAttachment($attachment);
        }
        return '';
        /**
        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mime.php');
        $Mime =& new Mail_mime();
        //$Mime->getMessage
        echo "<pre>".print_r($this,true)."</pre>";
        die();
        */
    }

    function toMail($defaults = array())
    {
        $Part =& new AkMail();
        list($real_content_type, $ctype_attrs) = $this->_getContentTypeAndAttributes();

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


    function getDefault($field)
    {
        $field = AkInflector::variablize($field);
        $defaults = array(
        'charset' => AK_ACTION_MAILER_DEFAULT_CHARSET,
        'contentType' => 'text/plain',
        );
        return isset($defaults[$field]) ? $defaults[$field] : null;
    }

    function _getContentTypeAndAttributes($content_type)
    {
        if(empty($content_type)){
            return array($this->getDefault('contentType'), array());
        }
        $attributes = array();
        if(strstr($content_type,';')){
            list($content_type, $attrs) = split(";\\s*",$content_type);
            if(!empty($attrs)){
                foreach ((array)$attrs as $h=>$s){
                    if(strstr($s,'=')){
                        list($k,$v) = array_map('trim',split("=", $s, 2));
                        if(!empty($v)){
                            $attributes[$k] = $v;
                        }
                    }
                }
            }
        }

        $attributes = array_diff(array_merge(array('charset'=> (empty($this->_charset)?$this->getDefault('charset'):$this->_charset)),$attributes), array(''));
        return array(trim($content_type), $attributes);
    }



}

?>
