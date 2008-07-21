<?php

class AkMailPart extends AkMailBase
{

    
    function _prepareHeadersForRendering()
    {
        $this->_removeUnnecesaryHeaders();
        $this->_addHeaderAttributes();
    }

    function _removeUnnecesaryHeaders()
    {
        $headers = $this->getHeaders();

        $this->headers = array();
        foreach (array(
        'Content-Type',
        'Content-Transfer-Encoding',
        'Content-Id',
        'Content-Disposition',
        'Content-Description',
        ) as $allowed_header){
            if(isset($headers[$allowed_header])){
                $this->headers[$allowed_header] = $headers[$allowed_header];
            }
        }
    }
}

?>