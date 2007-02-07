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
                    $address_description = '=?UTF-8?Q?'.$this->quoted_printable_encode($address_description, 0).'?=';
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

    function quoted_printable_encode($quoted_string, $max_length = 74, $emulate_imap_8bit = true)
    {
        $lines= preg_split("/(?:\r\n|\r|\n)/", $quoted_string);
        $search_pattern = $emulate_imap_8bit ? '/[^\x20\x21-\x3C\x3E-\x7E]/e' : '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';
        foreach ((array)$lines as $k=>$line){
            $length = strlen($line);
            if ($length == 0){
                continue;
            }
            $line = preg_replace($search_pattern, 'sprintf( "=%02X", ord ( "$0" ) ) ;', $line );
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
    
    
    

    function setMimeContents($options = array())
    {
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'text_helper.php');
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');

        $default_options = array(
        'html' => TextHelper::textilize($this->body),
        'text' => $this->body,
        'attachments' => array(),
        'embed_referenced_images' => AK_MAIL_EMBED_IMAGES_AUTOMATICALLY_ON_EMAILS
        );

        $options = array_merge($default_options, $options);

        if($options['embed_referenced_images']){
            list($html_images, $options['html']) = $this->_embedReferencedImages($options['html']);
        }
        
        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mime.php');
        $this->Mime =& new Mail_mime();

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
    }
    
    function _embedReferencedImages($html)
    {
        $images = TextHelper::get_image_urls_from_html($html);
        $html_images = array();
        if(!empty($images)){
            require_once(AK_LIB_DIR.DS.'AkImage.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');

            foreach ($images as $image){
                $image = AssetTagHelper::_compute_public_path($image);
                $extenssion = substr($image, strrpos('.'.$image,'.'));

                $image_name = Ak::uuid();
                Ak::file_put_contents(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion, file_get_contents($image));
                $NewImage =& new AkImage(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion);
                $NewImage->save(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.'.png');
                $html_images[$image] = $image_name.'.png';
                Ak::file_delete(AK_CACHE_DIR.DS.'tmp'.DS.$image_name);
            }
            $html = str_replace(array_keys($html_images),array_values($html_images), $html);
        }
        return array($html_images, $html);
    }

    



    /**
          function createMail()
          {
              require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'text_helper.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');

        $default_options = array(
        'html' => TextHelper::textilize($this->body),
        'text' => $this->body,
        'attachments' => array()
        );

        $options = array_merge($default_options, $options);

        //AssetTagHelper::_compute_public_path()
        $images = TextHelper::get_image_urls_from_html($options['html']);
        $html_images = array();
        if(!empty($images)){
            require_once(AK_LIB_DIR.DS.'AkImage.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');
            
            foreach ($images as $image){
                $image = AssetTagHelper::_compute_public_path($image);
                $extenssion = substr($image, strrpos('.'.$image,'.'));

                $image_name = Ak::uuid();
                Ak::file_put_contents(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion, file_get_contents($image));
                $NewImage =& new AkImage(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion);
                $NewImage->save(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.'.png');
                $html_images[$image] = $image_name.'.png';
                Ak::file_delete(AK_CACHE_DIR.DS.'tmp'.DS.$image_name);
            }
            $options['html'] = str_replace(array_keys($html_images),array_values($html_images), $options['html']);
        }

        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mime.php');
        $this->Mime =& new Mail_mime();
        
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
              
              
              
              
            if(!$this->_isAscii($this->subject)){
                $this->subject = '=?UTF-8?Q?'.$this->quoted_printable_encode($this->subject, false).'?=';
            }

            $this->recipients = $this->_encodeAddress($this->recipients, '' , AK_OS == 'WINDOWS');
            $this->from = $this->_encodeAddress($this->from, 'From');
            $this->bcc = $this->_encodeAddress($this->bcc, 'Bcc');
            $this->cc = $this->_encodeAddress($this->cc, 'Cc');
            
            $this->mime_version = !empty($this->mime_version) ? 'MIME-Version: '.$this->mime_version.AK_MAIL_HEADER_EOL : '';
            
            
            $Mail->mime_version = mime_version unless mime_version.null?
            $Mail->date = sent_on.to_time rescue sent_on if sent_on
            
            
            
            

    $header .= 'Content-Type: text/plain; charset=UTF-8'.AK_MAIL_HEADER_EOL;
    $header .= 'Content-Transfer-Encoding: quoted-printable'.AK_MAIL_HEADER_EOL;
    $header .= $headers;
    $header  = trim($header);

    $body = $this->quoted_printable_encode($body);
            
            headers.each { |k, v| m[k] = v }
    
            real_content_type, ctype_attrs = parse_content_type
    
            if $this->parts.empty?
              $Mail->set_content_type(real_content_type, null, ctype_attrs)
              $Mail->body = Utils.normalize_new_lines(body)
            else
              if String === body
                part = TMail::Mail.new
                part.body = Utils.normalize_new_lines(body)
                part.set_content_type(real_content_type, null, ctype_attrs)
                part.set_content_disposition "inline"
                $Mail->parts << part
              }
    
              $this->parts.each do |p|
                part = (TMail::Mail === p ? p : p.to_mail(self))
                $Mail->parts << part
              }
              
              if real_content_type =~ /multipart/
                ctype_attrs.delete "charset"
                $Mail->set_content_type(real_content_type, null, ctype_attrs)
              }
            }
            


        $success = true;
        if(empty($EmailAccount)){
        }

        if($this->notifyObservers('beforeSend')){
            $this->save();
            $this->recipient->load(true);
            $this->email_account->assign($EmailAccount);
            if(!empty($this->recipients)){
                $this->_loadMailConnector($EmailAccount->getAttributes());
                foreach (array_keys($this->recipients) as $k){
                    $success = $this->_send(trim($this->recipients[$k]->name.' <'.$this->recipients[$k]->email.'>')) ? $success : false;
                }
            }
            $this->notifyObservers('afterSend');
        }
        if($success){
            $this->draft = false;
            $this->sent = true;
            $this->headers = serialize($this->_getHeaders());
            $this->save();
            return $this;
        }
        return $success;
          }
    * /
    function performDeliverySmtp(&$Mail)
    {
        $body = $Mail->get();
        $headers = $this->Mime->headers($headers);
    $mail = &Mail::factory('mail');
    $mail->send($to, $hdrs, $body);
    
        $destinations = mail.destinations
        $Mail->ready_to_send()
    
            Net::SMTP.start(server_settings[:address], server_settings[:port], server_settings[:domain], 
                server_settings[:user_name], server_settings[:password], server_settings[:authentication]) do |smtp|
              smtp.sendmail(mail.encoded, mail.from, destinations)
            }
          }
    
          function performDeliveryPhp(mail)
            IO.popen("/usr/sbin/sendmail -i -t","w+") do |sm|
              sm.print(mail.encoded.gsub(/\r/, ''))
              sm.flush
            }
          }
    
          function performDeliveryTest($Mail)
          {
            $this->deliveries[] = $Mail;
          }
      }
      */



    function send($EmailAccount = null)
    {
        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail.php');

        $success = true;
        if(empty($EmailAccount)){
        }

        if($this->notifyObservers('beforeSend')){
            $this->save();
            $this->recipient->load(true);
            $this->email_account->assign($EmailAccount);
            if(!empty($this->recipients)){
                $this->_loadMailConnector($EmailAccount->getAttributes());
                foreach (array_keys($this->recipients) as $k){
                    $success = $this->_send(trim($this->recipients[$k]->name.' <'.$this->recipients[$k]->email.'>')) ? $success : false;
                }
            }
            $this->notifyObservers('afterSend');
        }
        if($success){
            $this->draft = false;
            $this->sent = true;
            $this->headers = serialize($this->_getHeaders());
            $this->save();
            return $this;
        }
        return $success;
    }

    function _send($to)
    {
        $headers = $this->_getHeaders($to);
        if(empty($this->Mime)){
            $body = $this->body;
            $headers['Content-Type'] = 'text/plain; charset='.Ak::locale('charset').'; format=flowed';
        }else{
            $body = $this->Mime->get();
            $headers = $this->Mime->headers($headers);
        }

        return $this->Connector->send(
        array(
        'To'=>$to
        ), $headers, $body);
    }

    function _getHeaders($to = null)
    {
        return array(
        'From' => trim($this->email_account->sender_name.' <'.$this->email_account->reply_to.'>'),
        'Return-path' => trim($this->email_account->sender_name.' <'.$this->email_account->reply_to.'>'),
        'Subject' => $this->subject,
        'To' => $to,
        'Message-Id' => '<'.$this->id.'.'.Ak::uuid().substr('bermi@akelos.com', strpos('bermi@akelos.com','@')).'>',
        'Date' => strftime("%a, %d %b %Y %H:%M:%S %z",Ak::getTimestamp()));
    }
    /*
    require_once('Mail.php');      // These two files are part of Pear,
    require_once('Mail/Mime.php'); // and are required for the Mail_Mime class
    $mime = new Mail_Mime();
    $mime->setTxtBody($textMessage);
    $mime->setHtmlBody($htmlMessage);
    $mime->addAttachment($attachment);

    $this->Mime

    $body = $this->Mime->get();
    $hdrs = $this->Mime->headers($headers);
    $mail = &Mail::factory('mail');
    $mail->send($to, $hdrs, $body);
    */

    

}

?>