<?php

// WARNING OPEN THIS FILE AS UTF-8 ONLY

require_once(dirname(__FILE__).'/../../fixtures/config/config.php');

require_once(AK_LIB_DIR.DS.'AkActionMailer.php');

Ak::import('render_mailer,first_mailer,second_mailer,helper_mailer');

class Tests_for_Mailers extends  AkUnitTest
{
    function setup()
    {
        $this->Mailer =& new AkActionMailer();
        $this->Mailer->delivery_method = 'test';
        $this->Mailer->perform_deliveries = true;
        $this->Mailer->deliveries = array();
        $this->recipient = 'test@localhost';
    }

    /**/
    function test_inline_template()
    {
        $RenderMailer =& new RenderMailer();
        $Mail = $RenderMailer->create('inline_template', $this->recipient);
        $this->assertEqual("Hello, World", $Mail->body);
    }
    
    function test_file_template()
    {
        $RenderMailer =& new RenderMailer();
        $Mail = $RenderMailer->create('file_template',$this->recipient);
        $this->assertEqual("Hello there,\n\nMr. test@localhost", trim($Mail->body));
    }

    // FirstSecondHelper
    function test_ordering()
    {
        $FirstMailer =& new FirstMailer();
        $Mail = $FirstMailer->create('share', $this->recipient);
        $this->assertEqual('first mail', trim($Mail->body));
        
        $SecondMailer =& new SecondMailer();
        $Mail = $SecondMailer->create('share', $this->recipient);
        $this->assertEqual('second mail', trim($Mail->body));
        
        
        $FirstMailer =& new FirstMailer();
        $Mail = $FirstMailer->create('share', $this->recipient);
        $this->assertEqual('first mail', trim($Mail->body));
        
        $SecondMailer =& new SecondMailer();
        $Mail = $SecondMailer->create('share', $this->recipient);
        $this->assertEqual('second mail', trim($Mail->body));
    }

    function test_use_helper()
    {
        $HelperMailer =& new HelperMailer();
        $Mail = $HelperMailer->create('use_helper', $this->recipient);
        $this->assertPattern('/Mr\. Joe Person/', trim($Mail->body));
    }

    function test_use_example_helper()
    {
        $HelperMailer =& new HelperMailer();
        $Mail = $HelperMailer->create('use_example_helper', $this->recipient);
        $this->assertPattern('/<em><strong><small>emphasize me!/', trim($Mail->body));
    }

    function test_use_helper_method()
    {
        $HelperMailer =& new HelperMailer();
        $Mail = $HelperMailer->create('use_helper_method', $this->recipient);
        $this->assertPattern('/HelperMailer/', trim($Mail->body));
    }

    function test_use_mail_helper()
    {
        $HelperMailer =& new HelperMailer();
        $Mail = $HelperMailer->create('use_mail_helper', $this->recipient);
        $this->assertPattern('/  But soft!/', trim($Mail->body));
        $this->assertPattern("/east,\n  and Juliet/", trim($Mail->body));
    }

    
    function test_quote_multibyte_chars()
    {
        $original = "\303\246 \303\270 and \303\245";
        $result = AkMailEncoding::_convertQuotedPrintableTo8Bit($original);
        $unquoted = quoted_printable_decode($result);
        $this->assertEqual($unquoted, $original);
    }

    function test_mime_header_to_utf()
    {
        $headers = array(
        "Subject: =?ISO-8859-1?Q?=C9ste_es_el_sof=E1_del_q_habl=E9_=5B?=\n\r =?ISO-8859-1?Q?Fwd=3A_Sof=E1=2E=5D_?="=>'Subject: Éste es el sofá del q hablé [Fwd: Sofá.]',
        "Subject: =?ISO-8859-1?Q?=C9ste_es_el_sof=E1_del_q_habl=E9_=5B?==?ISO-8859-1?Q?Fwd=3A_Sof=E1=2E=5D_?="=>'Subject: Éste es el sofá del q hablé [Fwd: Sofá.]',

        'Subject: =?UTF-8?B?UHLDvGZ1bmcgUHLDvGZ1bmc=?='=>'Subject: Prüfung Prüfung',
        'Subject: =?iso-8859-1?Q?RV:_=5BFwd:__chiste_inform=E1tico=5D?='=>'Subject: RV: [Fwd:  chiste informático]',
        '=?ISO-8859-11?B?4L7U6MG7w9DK1Le41MDSvuPL6aHRuuCr1MPsv+DHzcPstOnHwiBEdWFsLUNvcmUgSW50ZWwoUikgWGVvbihSKSBQcm9jZXNzb3Ig48vB6A==?='=>'เพิ่มประสิทธิภาพให้กับเซิร์ฟเวอร์ด้วย Dual-Core Intel(R) Xeon(R) Processor ใหม่',
        '=?UTF-8?B?0KDRg9GB0YHQutC40Lkg5Lit5paHINei15HXqNeZ16o=?='=>'Русский 中文 עברית',
        '=?UTF-8?B?ZXN0w6Egw6MgYsOkc8OqNjQ=?='=>'está ã bäsê64',
        // NOT SUPPORTED YET   '=?ISO-2022-JP?B?GyRCJDMkcyRLJEEkT0AkMyYbKEI=?='=>'こんにちは世界',
        '=?UTF-8?Q?E=C3=B1e_de_Espa=C3=B1a?= =?UTF-8?Q?_Fwd:_?= =?UTF-8?Q?=E3=81=93=E3=82=93=E3=81=AB=E3=81=A1=E3=81=AF=E4=B8=96=E7=95=8C?='=>'Eñe de España Fwd: こんにちは世界',
        'From: =?ISO-8859-1?Q?Crist=F3bal_G=F3mez_Moreno?= <cristobal@example.com>'=>'From: Cristóbal Gómez Moreno <cristobal@example.com>',

        "Subject: =?ISO-8859-1?Q?=C9ste_es_el_sof=E1_del_q_habl=E9_=5B?=\n =?ISO-8859-1?Q?Fwd=3A_Sof=E1=2E=5D_?="=>'Subject: Éste es el sofá del q hablé [Fwd: Sofá.]'
        );

        
        foreach ($headers as $encoded_header=>$expected){
            $this->assertEqual(AkMailEncoding::_decodeHeader($encoded_header), $expected);
        }
    }
    
    // test an email that has been created using \r\n newlines, instead of
    // \n newlines.
    function test_email_quoted_with_0d0a()
    {
        $Mail = AkMail::parse(file_get_contents(AK_TEST_DIR.'/fixtures/data/raw_email_quoted_with_0d0a'));
        $this->assertPattern('/Elapsed time/', $Mail->body);
    }

    function test_email_with_partially_quoted_subject()
    {
        $Mail = AkMail::parse(file_get_contents(AK_TEST_DIR.'/fixtures/data/raw_email_with_partially_quoted_subject'));
        $this->assertEqual("Re: Test: \"\346\274\242\345\255\227\" mid \"\346\274\242\345\255\227\" tail", $Mail->subject);
    }
    
    
}





Ak::test('Tests_for_Mailers');


?>
