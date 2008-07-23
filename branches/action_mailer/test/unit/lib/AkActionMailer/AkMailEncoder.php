<?php

require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

require_once(AK_LIB_DIR.DS.'AkActionMailer.php');

Ak::import('test_mailer');


class AkMailEncoderTestCase extends AkUnitTest
{
    function setup()
    {
        $this->Mailer =& new AkActionMailer();
        $this->Mailer->delivery_method = 'test';
        $this->Mailer->perform_deliveries = true;
        $this->recipient = 'bermi@bermilabs.com';
    }

   
    function test_should_encode_alternative_message_from_templates()
    {
        $TestMailer =& new TestMailer();
        $Delivered = $TestMailer->deliver('alternative_message_from_templates', $this->recipient);
        $MailComposer = new AkMailComposer($Delivered);
        
        echo $MailComposer->compose($Delivered);
    }


}

Ak::test('AkMailEncoderTestCase');

?>
