<?php

class AkMailEncoder extends AkObject
{


    
    
    
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







}

?>