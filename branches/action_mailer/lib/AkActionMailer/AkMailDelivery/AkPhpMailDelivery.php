<?php


class AkPhpMailDelivery extends AkObject
{
    function deliver(&$Mailer, $settings = array())
    {
        $Message =& $Mailer->Message;
        return mail($Message->getTo(), $Message->getSubject(), $Message->bodyToString(), $Message->_getHeadersAsText());
    }
}


?>