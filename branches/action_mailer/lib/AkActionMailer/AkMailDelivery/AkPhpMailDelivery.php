<?php


class AkPhpMailDelivery extends AkObject
{
    function deliver(&$Message, $settings = array())
    {
        return mail($Message->getTo(), $Message->getSubject(), $Message->bodyToString(), $Message->_getHeadersAsText());
    }
}


?>