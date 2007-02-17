<?php

// WARNING OPEN THIS FILE AS UTF-8 ONLY

require_once(dirname(__FILE__).'/../../fixtures/config/config.php');

require_once(AK_LIB_DIR.DS.'AkActionMailer.php');

Ak::import('render_mailer,first_mailer,second_mailer,helper_mailer,test_mailer');

class Tests_for_Mailers extends  AkUnitTest
{
    /**/
    function setup()
    {
        $this->Mailer =& new AkActionMailer();
        $this->Mailer->delivery_method = 'test';
        $this->Mailer->perform_deliveries = true;
        $this->Mailer->deliveries = array();
        $this->recipient = 'test@localhost';
    }


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
        $result = AkActionMailerQuoting::quotedPrintableEncode($original);
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
        'X-Akelos-Random: =?ISO-8859-11?B?4L7U6MG7w9DK1Le41MDSvuPL6aHRuuCr1MPsv+DHzcPstOnHwiBEdWFsLUNvcmUgSW50ZWwoUikgWGVvbihSKSBQcm9jZXNzb3Ig48vB6A==?='=>'X-Akelos-Random: เพิ่มประสิทธิภาพให้กับเซิร์ฟเวอร์ด้วย Dual-Core Intel(R) Xeon(R) Processor ใหม่',
        'X-Akelos-Random: =?UTF-8?B?0KDRg9GB0YHQutC40Lkg5Lit5paHINei15HXqNeZ16o=?='=>'X-Akelos-Random: Русский 中文 עברית',
        'X-Akelos-Random: =?UTF-8?B?ZXN0w6Egw6MgYsOkc8OqNjQ=?='=>'X-Akelos-Random: está ã bäsê64',
        //'X-Akelos-Random: =?ISO-2022-JP?B?GyRCJDMkcyRLJEEkT0AkMyYbKEI=?='=>'X-Akelos-Random: こんにちは世界',

        'X-Akelos-Random: =?UTF-8?Q?E=C3=B1e_de_Espa=C3=B1a?= =?UTF-8?Q?_Fwd:_?= =?UTF-8?Q?=E3=81=93=E3=82=93=E3=81=AB=E3=81=A1=E3=81=AF=E4=B8=96=E7=95=8C?='=>'X-Akelos-Random: Eñe de España Fwd: こんにちは世界',
        'From: =?ISO-8859-1?Q?Crist=F3bal_G=F3mez_Moreno?= <cristobal@example.com>'=>'From: Cristóbal Gómez Moreno <cristobal@example.com>',

        "Subject: =?ISO-8859-1?Q?=C9ste_es_el_sof=E1_del_q_habl=E9_=5B?=\n =?ISO-8859-1?Q?Fwd=3A_Sof=E1=2E=5D_?="=>'Subject: Éste es el sofá del q hablé [Fwd: Sofá.]'
        );

        $raw_mail = join("\r\n",array_keys($headers))."\r\n\r\nMail body";
        $Mail = AkMailParser::parse($raw_mail);
        foreach ($Mail->headers as $k=>$header){
            $this->assertEqual($header['name'].': '.$header['value'], array_shift((array_slice($headers, $k,1))));
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
    
    /**/
}


class Tests_for_AkActionMailer extends  AkUnitTest
{
        
    function encode($text, $charset = 'utf-8')
    {
        return AkActionMailerQuoting::quotedPrintable($text, $charset);
    }

    function new_mail($charset = 'utf-8')
    {
        $Mail =& new AkMail();
        $Mail->setMimeVersion('1.0');
        $Mail->setContentType('text/plain; charset:'.$charset);
        return $Mail;

    }

    function setup()
    {
        $this->Mailer =& new AkActionMailer();
        $this->Mailer->delivery_method = 'test';
        $this->Mailer->perform_deliveries = true;
        $this->Mailer->deliveries = array();
        $this->recipient = 'test@localhost';
    }

    /**/

    function test_nested_parts()
    {
        $HelperMailer =& new TestMailer();
        $Created = $HelperMailer->create('nested_multipart', $this->recipient);

        $this->assertEqual(2, count($Created->parts));
        $this->assertEqual(2, count($Created->parts[0]->parts));
        $this->assertEqual( "multipart/mixed", $Created->content_type);
        $this->assertEqual( "multipart/alternative", $Created->parts[0]->content_type );
        //echo "<pre>".print_r($Created,true)."</pre>";
        $this->assertEqual( "bar", $Created->parts[0]->header['Foo'] );
        $this->assertEqual( "text/plain", $Created->parts[0]->parts[0]->content_type );
        $this->assertEqual( "text/html", $Created->parts[0]->parts[1]->content_type );
        $this->assertEqual( "application/octet-stream", $Created->parts[1]->content_type );
    }


    function test_attachment_with_custom_header()
    {
        $HelperMailer =& new TestMailer();
        $Created = $HelperMailer->create('attachment_with_custom_header', $this->recipient);
        $this->assertEqual( "<test@test.com>", $Created->parts[1]->header['Content-ID']);
    }

    function test_signed_up()
    {
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("[Signed up] Welcome $this->recipient");
        $Expected->setBody("Hello there,\n\nMr. $this->recipient");
        $Expected->setFrom("system@example.com");
        $Expected->setDate(Ak::getTimestamp("2004-12-12"));


        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('signed_up', $this->recipient));
        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());
        $this->assertTrue($TestMailer->deliver('signed_up', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }

    function test_custom_template()
    {
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("[Signed up] Welcome $this->recipient");
        $Expected->setBody("Hello there,\n\nMr. $this->recipient");
        $Expected->setFrom("system@example.com");

        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('custom_template', $this->recipient));
        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());
    }

    function test_cancelled_account()
    {
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("[Cancelled] Goodbye $this->recipient");
        $Expected->setBody("Goodbye, Mr. $this->recipient");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");

        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('cancelled_account', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('cancelled_account', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }


    function test_cc_bcc()
    {
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("testing bcc/cc");
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("nobody@example.com");
        $Expected->setBcc("root@example.com");


        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('cc_bcc', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('cc_bcc', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }




    function test_iso_charset()
    {
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setCharset("ISO-8859-1");
        $Expected->setSubject(Ak::recode('testing isø charsets','ISO-8859-1', 'UTF-8'));
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("nobody@example.com");
        $Expected->setBcc("root@example.com");

        $TestMailer =& new TestMailer();

        $this->assertTrue($Created = $TestMailer->create('iso_charset', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('iso_charset', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }



    function test_unencoded_subject()
    {
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("testing unencoded subject");
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("nobody@example.com");
        $Expected->setBcc("root@example.com");

        $TestMailer =& new TestMailer();

        $this->assertTrue($Created = $TestMailer->create('unencoded_subject', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('unencoded_subject', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }


    function test_perform_deliveries_flag()
    {
        $TestMailer =& new TestMailer();

        $TestMailer->perform_deliveries = false;
        $this->assertTrue($TestMailer->deliver('signed_up', $this->recipient));
        $this->assertEqual(count($TestMailer->deliveries), 0);

        $TestMailer->perform_deliveries = true;
        $this->assertTrue($TestMailer->deliver('signed_up', $this->recipient));
        $this->assertEqual(count($TestMailer->deliveries), 1);

    }

    function test_unquote_quoted_printable_subject()
    {
        $msg = <<<EOF
From: me@example.com
Subject: =?UTF-8?Q?testing_testing_=D6=A4?=
Content-Type: text/plain; charset=iso-8859-1

The body
EOF;

        $Mail = AkMail::parse($msg);
        $this->assertEqual("testing testing \326\244", $Mail->subject);
        $this->assertEqual("=?UTF-8?Q?testing_testing_=D6=A4?=", $Mail->getSubject());

    }

    function test_unquote_7bit_subject()
    {
        $msg = <<<EOF
From: me@example.com
Subject: this == working?
Content-Type: text/plain; charset=iso-8859-1

The body
EOF;

        $Mail = AkMail::parse($msg);
        $this->assertEqual("this == working?", $Mail->subject);
        $this->assertEqual("this == working?", $Mail->getSubject());

    }


    function test_unquote_7bit_body()
    {
        $msg = <<<EOF
From: me@example.com
Subject: subject
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: 7bit

The=3Dbody
EOF;

        $Mail = AkMail::parse($msg);
        $this->assertEqual("The=3Dbody", $Mail->body);
        $this->assertEqual("The=3Dbody", $Mail->getBody());

    }

    function test_unquote_quoted_printable_body()
    {
        $msg = <<<EOF
From: me@example.com
Subject: subject
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: quoted-printable

The=3Dbody
EOF;

        $Mail = AkMail::parse($msg);
        $this->assertEqual("The=body", $Mail->body);
        $this->assertEqual("The=3Dbody", $Mail->getBody());

    }

    function test_unquote_base64_body()
    {
        $msg = <<<EOF
From: me@example.com
Subject: subject
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: base64

VGhlIGJvZHk=
EOF;

        $Mail = AkMail::parse($msg);
        $this->assertEqual("The body", $Mail->body);
        $this->assertEqual("VGhlIGJvZHk=", $Mail->getBody());
    }



    function test_extended_headers()
    {
        $this->recipient = "Grytøyr <test@localhost>";
        $Expected =& $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setCharset("ISO-8859-1");
        $Expected->setSubject("testing extended headers");
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("Grytøyr <stian1@example.com>");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("Grytøyr <stian2@example.com>");
        $Expected->setBcc("Grytøyr <stian3@example.com>");

        $TestMailer =& new TestMailer();

        $this->assertTrue($Created = $TestMailer->create('extended_headers', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('extended_headers', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }

    function test_utf8_body_is_not_quoted()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('utf8_body', $this->recipient));
        $this->assertPattern('/åœö blah/', $Created->getBody());
    }

    function test_multiple_utf8_recipients()
    {
        $this->recipient = array("\"Foo áëô îü\" <extended@example.com>", "\"Example Recipient\" <me@example.com>");
        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('utf8_body', $this->recipient));
        $this->assertPattern("/\nFrom: =\?UTF-8\?Q\?Foo_.*?\?= <extended@example.com>\r/", $Created->getEncoded());
        $this->assertPattern("/To: =\?UTF-8\?Q\?Foo_.*?\?= <extended@example.com>, Ex=\r\n ample Recipient <me/", $Created->getEncoded());
    }

    function test_receive_decodes_base64_encoded_mail()
    {
        $TestMailer =& new TestMailer();
        $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email"));
        $this->assertPattern("/Jamis/", $TestMailer->received_body);

    }

    function test_receive_attachments()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email2"));
        $Attachment = Ak::last($Mail->attachments);
        $this->assertEqual("smime.p7s", $Attachment->original_filename);
        $this->assertEqual("application/pkcs7-signature", $Attachment->content_type);
    }

    function test_decode_attachment_without_charset()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email3"));
        $Attachment = Ak::last($Mail->attachments);
        $this->assertEqual(1026, Ak::size($Attachment->data));
    }


    function test_attachment_using_content_location()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email12"));

        $this->assertEqual(1, Ak::size($Mail->attachments));

        $Attachment = Ak::first($Mail->attachments);
        $this->assertEqual("Photo25.jpg", $Attachment->original_filename);
    }


    function test_attachment_with_text_type()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email13"));

        $this->assertTrue($Mail->hasAttachments());
        $this->assertEqual(1, Ak::size($Mail->attachments));

        $Attachment = Ak::first($Mail->attachments);
        $this->assertEqual("hello.rb", $Attachment->original_filename);
    }



    function test_decode_part_without_content_type()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email4"));
        $this->assertNoErrors();
    }

    function test_decode_message_without_content_type()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email5"));
        $this->assertNoErrors();
    }

    function test_decode_message_with_incorrect_charset()
    {
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email6"));
        $this->assertNoErrors();
    }


    function test_multipart_with_mime_version()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('multipart_with_mime_version', $this->recipient));
        $this->assertEqual('1.1', $Created->mime_version);
    }

    function test_multipart_with_utf8_subject()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('multipart_with_utf8_subject', $this->recipient));
        $this->assertPattern("/\nSubject: =\?UTF-8\?Q\?Foo_.*?\?=/", $Created->getEncoded());
    }

    function test_implicitly_multipart_with_utf8()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('implicitly_multipart_with_utf8', $this->recipient));
        $this->assertPattern("/\nSubject: =\?UTF-8\?Q\?Foo_.*?\?=/", $Created->getEncoded());
    }

    function test_explicitly_multipart_with_content_type()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('explicitly_multipart_example', $this->recipient));
        
        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertTrue(empty($Mail->content_type));
        $this->assertEqual("multipart/alternative", $Mail->getContentType());
        
        $this->assertEqual("text/html", $Mail->parts[1]->content_type);
        $this->assertEqual("iso-8859-1", $Mail->parts[1]->content_type_attributes['charset']);
        $this->assertEqual("inline", $Mail->parts[1]->content_disposition);
        
        $this->assertEqual("image/jpeg", $Mail->parts[2]->content_type);
        $this->assertEqual("attachment", $Mail->parts[2]->content_disposition);
        $this->assertEqual("foo.jpg", $Mail->parts[2]->content_disposition_attributes['filename']);
        $this->assertEqual("foo.jpg", $Mail->parts[2]->content_type_attributes['name']);
        $this->assertTrue(empty($Mail->parts[2]->content_type_attributes['charset']));
        
    }

    function test_explicitly_multipart_with_invalid_content_type()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('explicitly_multipart_example', $this->recipient, 'text/xml'));
        
        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertEqual("multipart/alternative", $Mail->getContentType());
        
    }


    function test_implicitly_multipart_messages()
    {
        $TestMailer =& new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('implicitly_multipart_example', $this->recipient));

        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertEqual("1.0", $Mail->mime_version);
        $this->assertEqual("multipart/alternative", $Mail->content_type);

        $this->assertEqual("text/yaml", $Mail->parts[0]->content_type);
        $this->assertEqual('UTF-8', $Mail->parts[0]->content_type_attributes['charset']);

        $this->assertEqual("text/plain", $Mail->parts[1]->content_type);
        $this->assertEqual('UTF-8', $Mail->parts[1]->content_type_attributes['charset']);
        
        $this->assertEqual("text/html", $Mail->parts[2]->content_type);
        $this->assertEqual('UTF-8', $Mail->parts[1]->content_type_attributes['charset']);
    }





}
Ak::test('Tests_for_Mailers');
Ak::test('Tests_for_AkActionMailer');


?>
