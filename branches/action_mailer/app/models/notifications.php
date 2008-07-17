<?php

class Notifications extends AkActionMailer
{
    
    function signup($recipient)
    {
        $this->setRecipients($recipient);
        $this->setSubject("[Notifications] Signup");
        $this->setFrom('testing@bermilabs.com');
        
        $this->setBody(array('nombre' => 'Bermi'));

     }
     
    
    function forgot_password($recipient)
    {
        $this->recipients    =  $recipient;
        $this->subject       =  "[Notifications] Forgot password";
        $this->from          =  '';
        $this->body          =  array();
        $this->headers       =  array();
     }
     
    
    function invoice($recipient)
    {
        $this->recipients    =  $recipient;
        $this->subject       =  "[Notifications] Invoice";
        $this->from          =  '';
        $this->body          =  array();
        $this->headers       =  array();
     }
     
    
}

?>