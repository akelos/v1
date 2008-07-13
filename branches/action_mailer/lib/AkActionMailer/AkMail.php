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

class AkMail extends Mail
{
    var $raw_message = '';
    var $charset = AK_ACTION_MAILER_DEFAULT_CHARSET;
    var $content_type;
    var $body;
    var $parts = array();

    function AkMail()
    {
        $args = func_get_args();
        if(isset($args[0])){
            if(count($args) == 1 && is_string($args[0])){
                $this->raw_message = $args[0];
            }elseif(is_array($args[0])){
                $this->_importStructure($args[0]);
            }
        }
    }

    function &parse($raw_email = '')
    {
        if(empty($raw_email)){
            trigger_error(Ak::t('Cannot parse an empty message'), E_USER_ERROR);
        }
        $Mail =& new AkMail((array)AkMailParser::parse($raw_email));
        return $Mail;
    }

    function load($email_file)
    {
        if(!file_exists($email_file)){
            trigger_error(Ak::t('Cannot find mail file at %path',array('%path'=>$email_file)), E_USER_ERROR);
        }
        $Mail =& new AkMail((array)AkMailParser::parse(file_get_contents($email_file)));
        return $Mail;
    }




    function setBody($body)
    {
        if(is_string($body)){
            $this->body = stristr(@$this->content_type,'text/') ? str_replace(array("\r\n","\r"),"\n", $body) : $body;
        }else{
            $this->body = $body;
        }
    }

    function getBody()
    {
        if(!is_array($this->body)){
            $encoding = $this->getContentTransferEncoding();
            $charset = empty($this->_charset) ? AK_ACTION_MAILER_DEFAULT_CHARSET : $this->_charset;

            switch ($encoding) {
                case 'quoted-printable':
                return trim(AkActionMailerQuoting::chunkQuoted(AkActionMailerQuoting::quotedPrintableEncode($this->body,$charset)));
                case 'base64':
                return trim(chunk_split(base64_encode($this->body)));
                default:
                return trim($this->body);
            }
        }
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
        $this->_charset = $this->charset = $charset;
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
        list($this->content_type, $ctype_attrs) = $this->_getContentTypeAndAttributes($content_type);
        $this->setContenttypeAttributes($ctype_attrs);
    }


    function getContentType()
    {
        return empty($this->content_type) ? ($this->_isMultipart()?'multipart/alternative':null) : $this->content_type.$this->getContenttypeAttributes();
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
        $this->content_disposition = $content_disposition;
    }

    /**
     * Specify the content transfer encoding for the message. 
     */
    function setContentTransferEncoding($content_transfer_encoding)
    {
        $this->content_transfer_encoding = $content_transfer_encoding;
    }

    /**
     * Alias for  setContentTransferEncoding
     */
    function setTransferEncoding($content_transfer_encoding)
    {
        $this->setContentTransferEncoding($content_transfer_encoding);
    }

    function getContentTransferEncoding()
    {
        if(empty($this->content_transfer_encoding)){
            return null;
        }
        return $this->content_transfer_encoding;
    }

    function getTransferEncoding()
    {
        return $this->getTransferEncoding();
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
        $charset = empty($this->_charset) ? AK_ACTION_MAILER_DEFAULT_CHARSET : $this->_charset;
        return join(", ",AkActionMailerQuoting::quoteAnyAddressIfNecessary(Ak::toArray($address_header_field), $charset));
    }

    function getFrom()
    {
        return $this->_getAddressHeaderFieldFormated($this->from);
    }


    function getTo()
    {
        return $this->getRecipients();
    }

    function getRecipients()
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

    function setHeader($name, $value = null, $options = array())
    {
        if(is_array($value)){
            $this->setHeaders($value, $options);
        }else{
            $this->header[$name] = $value;
        }
    }


    function setParts($parts)
    {
        foreach ((array)$parts as $k=>$part){
            if(is_numeric($k)){
                $this->setPart((array)$part);
            }else{
                $this->setPart($parts);
                break;
            }
        }
    }

    /**
     * Add a part to a multipart message, with an array of options like (content-type, charset, body, headers, etc.).
     * 
     *   function my_mail_message()
     *   {
     *     $this->setPart(array(
     *       'content-type' => 'text/plain', 
     *       'body' => "hello, world",
     *       'transfer_encoding' => "base64"
     *     ));
     *   }
     */
    function setPart($options = array(), $position = 'append')
    {
        $default_options = array('content_disposition' => 'inline', 'content_transfer_encoding' => 'quoted-printable');
        $options = array_merge($default_options, $options);

        $Part =& new AkMail($options);
        $Part->_isPart = true;
        $position == 'append' ? array_push($this->parts, $Part) : array_unshift($this->parts, $Part);
        empty($this->_avoid_multipart_propagation) ? $this->_propagateMultipartParts() : null;
    }

    function _propagateMultipartParts()
    {
        if(!empty($this->parts)){
            foreach (array_keys($this->parts) as $k){
                $Part =& $this->parts[$k];
                if(empty($Part->_propagated)){
                    $Part->_propagated = true;
                    if(!empty($Part->content_disposition)){
                        // Inline bodies
                        if(isset($Part->content_type) && stristr($Part->content_type,'text/') && $Part->content_disposition == 'inline'){
                            if((!empty($this->body) && is_string($this->body))
                            ||  (!empty($this->body) && is_array($this->body) && ($this->_isMultipart() || $this->content_type == 'text/plain'))
                            ){
                                $this->_moveBodyToInlinePart();
                            }
                            $type = strstr($Part->content_type, '/') ? substr($Part->content_type,strpos($Part->content_type,"/")+1) : $Part->content_type;
                            $Part->_on_body_as = $type;
                            $this->body[$type] = $Part->body;

                        }

                        // Attachments
                        elseif ($Part->content_disposition == 'attachment' || ($Part->content_disposition == 'inline' && !stristr($Part->content_type,'text/')) || !empty($Part->content_location)){
                            $this->_addAttachment($Part);
                        }
                    }
                }
            }
        }
    }

    function _moveBodyToInlinePart()
    {
        $options = array(
        'content_type' => @$this->content_type,
        'body' => @$this->body,
        'charset' => @$this->charset,
        'content_disposition' => 'inline'
        );
        foreach (array_keys($options) as $k){
            unset($this->$k);
        }
        $this->_multipart_message = true;
        $this->setPart($options, 'preppend');
    }

    function _isMultipart()
    {
        return !empty($this->_multipart_message);
    }

    function _addAttachment($Part)
    {
        $Part->original_filename = !empty($Part->content_type_attributes['name']) ? $Part->content_type_attributes['name'] :
        (!empty($Part->content_disposition_attributes['filename']) ? $Part->content_disposition_attributes['filename'] :
        (empty($Part->filename) ? @$Part->content_location : $Part->filename));
        
        $Part->original_filename = preg_replace('/[^A-Z^a-z^0-9^\-^_^\.]*/','',$Part->original_filename);
                
        if(!empty($Part->body)){
            $Part->data =& $Part->body;
        }
        if(empty($Part->content_disposition_attributes['filename'])){
            $Part->content_disposition_attributes['filename'] = $Part->original_filename;
        }
        if(empty($Part->content_type_attributes['name'])){
            $Part->content_type_attributes['name'] = $Part->original_filename;
        }
        unset($Part->content_type_attributes['charset']);
        $this->attachments[] =& $Part;
    }

    function hasAttachments()
    {
        return !empty($this->attachments);
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
    function setImplicitPartsOrder($implicit_parts_order)
    {
        $this->implicit_parts_order = $implicit_parts_order;
    }

    /**
     * Defaults to "1.0", but may be explicitly given if needed.
     */
    function setMimeVersion($mime_version)
    {
        $this->mime_version = $mime_version;
    }

    /**
     * The recipient addresses for the message, either as a string (for a single
     * address) or an array (for multiple addresses).
     */
    function setRecipients($recipients)
    {
        $this->recipients = join(", ", (array)Ak::toArray($recipients));
        $this->setHeader('to',$this->getTo());
    }


    /**
     * Specify the subject of the message.
     */
    function setSubject($subject)
    {
        $this->subject = $subject;
    }

    function getSubject($charset = AK_ACTION_MAILER_DEFAULT_CHARSET)
    {
        return AkActionMailerQuoting::quoteIfNecessary($this->subject, $charset);
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
        foreach ((array)$attributes as $key=>$value){
            if($key[0] != '_'){
                $attribute_setter = 'set'.AkInflector::camelize($key);
                if(method_exists($this, $attribute_setter)){
                    $this->$attribute_setter($value);
                }else{
                    $this->setHeader($key, $value);
                }
            }
        }
    }


    function getSortedParts($parts, $order = array())
    {
        $this->_parts_order = array_map('strtolower', empty($order) ? $this->implicit_parts_order : $order);
        usort($parts, array($this,'_contentTypeComparison'));
        return array_reverse(&$parts);
    }

    function _contentTypeComparison($a, $b)
    {
        $a_ct = strtolower($a->content_type);
        $b_ct = strtolower($b->content_type);
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
        if(isset($structure['header'])){
            $structure['headers'] = $structure['header'];
            unset($structure['header']);
        }
        foreach ($structure as $attribute=>$value){
            if($attribute[0] != '_'){
                $attribute_setter = 'set'.AkInflector::camelize($attribute);
                if(method_exists($this, $attribute_setter)){
                    $this->$attribute_setter($value);
                }else{
                    $this->{AkInflector::underscore($attribute)} = $value;
                }
            }
        }
        return ;
    }


    function _moveMailInstanceAttributesToHeaders()
    {
        foreach ((array)$this as $k=>$v){
            if($k[0] != '_' && $this->_belongsToHeaders($k)){
                $attribute_getter = 'get'.ucfirst($k);
                $attribute_name = AkInflector::underscore($k);
                $this->setHeader($attribute_name, method_exists($this,$attribute_getter) ? $this->$attribute_getter() : $v);
            }
        }
    }

    function _belongsToHeaders($attribute)
    {
        return !in_array(strtolower($attribute),array('body','recipients','part','parts','raw_message','sep','implicit_parts_order','header','headers'));
    }

    function getEncoded()
    {
        return $this->_getHeadersAsText().AK_ACTION_MAILER_EOL.AK_ACTION_MAILER_EOL.$this->getBody();
    }

    function _getHeadersAsText()
    {
        if(empty($this->date)){
            $this->setDate();
        }

        $this->_moveMailInstanceAttributesToHeaders();
        $headers = array();
        foreach (array_map(array('AkActionMailerQuoting','chunkQuoted'), $this->header) as $header=>$value){
            if(!is_numeric($header)){
                $headers[ucfirst($header)] = $value;
            }
        }
        unset($headers['Charset']);
        $this->_sanitizeHeaders($headers);
        return array_pop($this->prepareHeaders($headers));
    }

    function toMail($defaults = array())
    {
        $Part =& new AkMail();
        list($real_content_type, $ctype_attrs) = $this->_getContentTypeAndAttributes();

        if(empty($this->parts)){

            $Part->setContentTransferEncoding(!empty($this->transfer_encoding) ? $this->transfer_encoding : 'quoted-printable');
            switch (strtolower($Part->content_transfer_encoding)) {
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

        $Part->setHeaders($this->header);
        return $Part;
    }


    function getDefault($field)
    {
        $field = AkInflector::underscore($field);
        $defaults = array(
        'charset' => AK_ACTION_MAILER_DEFAULT_CHARSET,
        'content_type' => 'text/plain',
        );
        return isset($defaults[$field]) ? $defaults[$field] : null;
    }

    function _getContentTypeAndAttributes($content_type = null)
    {
        if(empty($content_type)){
            return array($this->getDefault('content_type'), array());
        }
        $attributes = array();
        if(strstr($content_type,';')){
            list($content_type, $attrs) = split(";\\s*",$content_type);
            if(!empty($attrs)){
                foreach ((array)$attrs as $s){
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


    function bodyToString($Mail = null)
    {
        $Mail = empty($Mail) ? $this : $Mail;
        $result = '';
        foreach ((array)$Mail as $field => $value){
            if(empty($Mail->_isPart) && $field=='body' && !empty($value)){
                $result .= $value."\n";
            }elseif(empty($Mail->data) && $field=='body' && !empty($value)){
                $result .= $value."\n";
            }elseif(!empty($Mail->data) && $field=='original_filename' && !empty($value)){
                $result .= $value;
            }
            if($field == 'parts' && !empty($value)){
                foreach ($value as $part){
                    if(!empty($part->data) && !empty($part->original_filename)){
                        $result .= "Attachment: ";
                        $result .= $this->bodyToString($part)."\n";
                    }else{
                        $result .= $this->bodyToString($part)."\n";
                    }
                }
            }
        }
        return $result;
    }


}

?>
