<?php

class FirstMailer extends AkActionMailer
{
    function share($recipient)
    {
        $this->recipients = $recipient;
        $this->subject = "using helpers";
        $this->from = "tester@example.com";
    }
}

?>