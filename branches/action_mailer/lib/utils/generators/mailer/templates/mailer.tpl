<?php echo "<?php"; ?>

class <?php echo $class_name; ?> extends AkActionMailer
{
    <?php foreach($actions in $action){ ?>

    function <?php echo $action; ?>($recipient)
    {
        $this->recipients    =  $recipient;
        $this->subject       =  "<?php echo $class_name; ?>#<?php echo $action; ?>";
        $this->from          =  '';
        $this->body          =  array();
        $this->headers       =  array();
     }
     
    <?php } ?>

}

?>