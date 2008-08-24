<?php

class AccountMailer extends AkActionMailer
{
    
    function registration_details($recipient)
    {
        $this->recipients    =  $recipient;
        $this->subject       =  "[AccountMailer] Registration details";
        $this->from          =  '';
        $this->body          =  array();
        $this->headers       =  array();
     }
     
    
    function password_reminder($recipient)
    {
        $this->recipients    =  $recipient;
        $this->subject       =  "[AccountMailer] Password reminder";
        $this->from          =  '';
        $this->body          =  array();
        $this->headers       =  array();
     }
     
    
    function password_modified($recipient)
    {
        $this->recipients    =  $recipient;
        $this->subject       =  "[AccountMailer] Password modified";
        $this->from          =  '';
        $this->body          =  array();
        $this->headers       =  array();
     }
     
    
}

?>