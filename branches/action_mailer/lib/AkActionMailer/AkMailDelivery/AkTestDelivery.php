<?php


class AkTestDelivery extends AkObject
{
    function deliver(&$Message, $settings = array())
    {
        $settings['ActionMailer']->deliveries[] =& $Message->getEncoded();
    }
}


?>