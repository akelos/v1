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
    } /**/
}


class Tests_for_AkActionMailer extends  AkUnitTest
{/**/
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


    function test_nested_parts()
    {
        $HelperMailer =& new TestMailer();
        $Created = $HelperMailer->create('nested_multipart', $this->recipient);

        $this->assertEqual(2, count($Created->parts));
        $this->assertEqual(2, count($Created->parts[0]->parts));
        $this->assertEqual( "multipart/mixed", $Created->contentType );
        $this->assertEqual( "multipart/alternative", $Created->parts[0]->contentType );
        $this->assertEqual( "bar", $Created->parts[0]->header['Foo'] );
        $this->assertEqual( "text/plain", $Created->parts[0]->parts[0]->contentType );
        $this->assertEqual( "text/html", $Created->parts[0]->parts[1]->contentType );
        $this->assertEqual( "application/octet-stream", $Created->parts[1]->contentType );
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
        return;
        $TestMailer =& new TestMailer();
        //$Mail =& $TestMailer->receive(file_get_contents('/Users/bermi/test'));
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email12"));
        echo "<pre>".print_r($Mail,true)."</pre>";
        /**
        $TestMailer =& new TestMailer();
        $Mail =& $TestMailer->receive(file_get_contents(AK_TEST_DIR."/fixtures/data/raw_email2"));
        echo "<pre>".print_r($Mail,true)."</pre>";
        return ;
        $Attachment = Ak::last($Mail->attachments);
        $this->assertEqual("smime.p7s", $Attachment->original_file_name);
        $this->assertEqual("application/pkcs7-signature", $Attachment->content_type);*/
    }
    
   
    /**



        file_put_contents('/Users/bermi/A',$Expected->getEncoded());
        file_put_contents('/Users/bermi/B',$Created->getEncoded());

  def test_receive_attachments
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email2")
    mail = TMail::Mail.parse(fixture)
    attachment = mail.attachments.last
    assert_equal "smime.p7s", attachment.original_filename
    assert_equal "application/pkcs7-signature", attachment.content_type
  end

  def test_decode_attachment_without_charset
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email3")
    mail = TMail::Mail.parse(fixture)
    attachment = mail.attachments.last
    assert_equal 1026, attachment.read.length
  end

  def test_attachment_using_content_location
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email12")
    mail = TMail::Mail.parse(fixture)
    assert_equal 1, mail.attachments.length
    assert_equal "Photo25.jpg", mail.attachments.first.original_filename
  end

  def test_attachment_with_text_type
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email13")
    mail = TMail::Mail.parse(fixture)
    assert mail.has_attachments?
    assert_equal 1, mail.attachments.length
    assert_equal "hello.rb", mail.attachments.first.original_filename
  end

  def test_decode_part_without_content_type
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email4")
    mail = TMail::Mail.parse(fixture)
    assert_nothing_raised { mail.body }
  end

  def test_decode_message_without_content_type
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email5")
    mail = TMail::Mail.parse(fixture)
    assert_nothing_raised { mail.body }
  end

  def test_decode_message_with_incorrect_charset
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email6")
    mail = TMail::Mail.parse(fixture)
    assert_nothing_raised { mail.body }
  end

  def test_multipart_with_mime_version
    mail = TestMailer.create_multipart_with_mime_version(@recipient)
    assert_equal "1.1", mail.mime_version
  end
  
  def test_multipart_with_utf8_subject
    mail = TestMailer.create_multipart_with_utf8_subject(@recipient)
    assert_match(/\nSubject: =\?utf-8\?Q\?Foo_.*?\?=/, mail.encoded)
  end

  def test_implicitly_multipart_with_utf8
    mail = TestMailer.create_implicitly_multipart_with_utf8
    assert_match(/\nSubject: =\?utf-8\?Q\?Foo_.*?\?=/, mail.encoded)
  end

  def test_explicitly_multipart_messages
    mail = TestMailer.create_explicitly_multipart_example(@recipient)
    assert_equal 3, mail.parts.length
    assert_nil mail.content_type
    assert_equal "text/plain", mail.parts[0].content_type

    assert_equal "text/html", mail.parts[1].content_type
    assert_equal "iso-8859-1", mail.parts[1].sub_header("content-type", "charset")
    assert_equal "inline", mail.parts[1].content_disposition

    assert_equal "image/jpeg", mail.parts[2].content_type
    assert_equal "attachment", mail.parts[2].content_disposition
    assert_equal "foo.jpg", mail.parts[2].sub_header("content-disposition", "filename")
    assert_equal "foo.jpg", mail.parts[2].sub_header("content-type", "name")
    assert_nil mail.parts[2].sub_header("content-type", "charset")
  end

  def test_explicitly_multipart_with_content_type
    mail = TestMailer.create_explicitly_multipart_example(@recipient, "multipart/alternative")
    assert_equal 3, mail.parts.length
    assert_equal "multipart/alternative", mail.content_type
  end

  def test_explicitly_multipart_with_invalid_content_type
    mail = TestMailer.create_explicitly_multipart_example(@recipient, "text/xml")
    assert_equal 3, mail.parts.length
    assert_nil mail.content_type
  end

  def test_implicitly_multipart_messages
    mail = TestMailer.create_implicitly_multipart_example(@recipient)
    assert_equal 3, mail.parts.length
    assert_equal "1.0", mail.mime_version
    assert_equal "multipart/alternative", mail.content_type
    assert_equal "text/yaml", mail.parts[0].content_type
    assert_equal "utf-8", mail.parts[0].sub_header("content-type", "charset")
    assert_equal "text/plain", mail.parts[1].content_type
    assert_equal "utf-8", mail.parts[1].sub_header("content-type", "charset")
    assert_equal "text/html", mail.parts[2].content_type
    assert_equal "utf-8", mail.parts[2].sub_header("content-type", "charset")
  end

  def test_implicitly_multipart_messages_with_custom_order
    mail = TestMailer.create_implicitly_multipart_example(@recipient, nil, ["text/yaml", "text/plain"])
    assert_equal 3, mail.parts.length
    assert_equal "text/html", mail.parts[0].content_type
    assert_equal "text/plain", mail.parts[1].content_type
    assert_equal "text/yaml", mail.parts[2].content_type
  end

  def test_implicitly_multipart_messages_with_charset
    mail = TestMailer.create_implicitly_multipart_example(@recipient, 'iso-8859-1')

    assert_equal "multipart/alternative", mail.header['content-type'].body
    
    assert_equal 'iso-8859-1', mail.parts[0].sub_header("content-type", "charset")
    assert_equal 'iso-8859-1', mail.parts[1].sub_header("content-type", "charset")
    assert_equal 'iso-8859-1', mail.parts[2].sub_header("content-type", "charset")
  end

  def test_html_mail
    mail = TestMailer.create_html_mail(@recipient)
    assert_equal "text/html", mail.content_type
  end

  def test_html_mail_with_underscores
    mail = TestMailer.create_html_mail_with_underscores(@recipient)
    assert_equal %{<a href="http://google.com" target="_blank">_Google</a>}, mail.body
  end

  def test_various_newlines
    mail = TestMailer.create_various_newlines(@recipient)
    assert_equal("line #1\nline #2\nline #3\nline #4\n\n" +
                 "line #5\n\nline#6\n\nline #7", mail.body)
  end

  def test_various_newlines_multipart
    mail = TestMailer.create_various_newlines_multipart(@recipient)
    assert_equal "line #1\nline #2\nline #3\nline #4\n\n", mail.parts[0].body
    assert_equal "<p>line #1</p>\n<p>line #2</p>\n<p>line #3</p>\n<p>line #4</p>\n\n", mail.parts[1].body
  end
  
  def test_headers_removed_on_smtp_delivery
    ActionMailer::Base.delivery_method = :smtp
    TestMailer.deliver_cc_bcc(@recipient)
    assert MockSMTP.deliveries[0][2].include?("root@loudthinking.com")
    assert MockSMTP.deliveries[0][2].include?("nobody@loudthinking.com")
    assert MockSMTP.deliveries[0][2].include?(@recipient)
    assert_match %r{^Cc: nobody@loudthinking.com}, MockSMTP.deliveries[0][0]
    assert_match %r{^To: #{@recipient}}, MockSMTP.deliveries[0][0]
    assert_no_match %r{^Bcc: root@loudthinking.com}, MockSMTP.deliveries[0][0]
  end

  def test_recursive_multipart_processing
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email7")
    mail = TMail::Mail.parse(fixture)
    assert_equal "This is the first part.\n\nAttachment: test.rb\nAttachment: test.pdf\n\n\nAttachment: smime.p7s\n", mail.body
  end

  def test_decode_encoded_attachment_filename
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email8")
    mail = TMail::Mail.parse(fixture)
    attachment = mail.attachments.last
    assert_equal "01QuienTeDijat.Pitbull.mp3", attachment.original_filename
  end

  def test_wrong_mail_header
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email9")
    assert_raise(TMail::SyntaxError) { TMail::Mail.parse(fixture) }
  end

  def test_decode_message_with_unknown_charset
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email10")
    mail = TMail::Mail.parse(fixture)
    assert_nothing_raised { mail.body }
  end

  def test_decode_message_with_unquoted_atchar_in_header
    fixture = File.read(File.dirname(__FILE__) + "/fixtures/raw_email11")
    mail = TMail::Mail.parse(fixture)
    assert_not_nil mail.from
  end

  def test_empty_header_values_omitted
    result = TestMailer.create_unnamed_attachment(@recipient).encoded
    assert_match %r{Content-Type: application/octet-stream[^;]}, result
    assert_match %r{Content-Disposition: attachment[^;]}, result
  end

  def test_headers_with_nonalpha_chars
    mail = TestMailer.create_headers_with_nonalpha_chars(@recipient)
    assert !mail.from_addrs.empty?
    assert !mail.cc_addrs.empty?
    assert !mail.bcc_addrs.empty?
    assert_match(/:/, mail.from_addrs.to_s)
    assert_match(/:/, mail.cc_addrs.to_s)
    assert_match(/:/, mail.bcc_addrs.to_s)
  end

  def test_deliver_with_mail_object
    mail = TestMailer.create_headers_with_nonalpha_chars(@recipient)
    assert_nothing_raised { TestMailer.deliver(mail) }
    assert_equal 1, TestMailer.deliveries.length
  end

  def test_multipart_with_template_path_with_dots
    mail = FunkyPathMailer.create_multipart_with_template_path_with_dots(@recipient)
    assert_equal 2, mail.parts.length
  end

  def test_custom_content_type_attributes
    mail = TestMailer.create_custom_content_type_attributes
    assert_match %r{format=flowed}, mail['content-type'].to_s
    assert_match %r{charset=utf-8}, mail['content-type'].to_s
  end
end

end # uses_mocha

class InheritableTemplateRootTest < Test::Unit::TestCase
  def test_attr
    expected = "#{File.dirname(__FILE__)}/fixtures/path.with.dots"
    assert_equal expected, FunkyPathMailer.template_root

    sub = Class.new(FunkyPathMailer)
    sub.template_root = 'test/path'

    assert_equal 'test/path', sub.template_root
    assert_equal expected, FunkyPathMailer.template_root
  end
end

class MethodNamingTest < Test::Unit::TestCase
  class TestMailer < ActionMailer::Base
    def send
      body 'foo'
    end
  end

  def setup
    ActionMailer::Base.delivery_method = :test
    ActionMailer::Base.perform_deliveries = true
    ActionMailer::Base.deliveries = []
  end

  def test_send_method
    assert_nothing_raised do
      assert_emails 1 do
        TestMailer.deliver_send
      end
    end
  end
end
     /**/

}
Ak::test('Tests_for_Mailers');
Ak::test('Tests_for_AkActionMailer');


?>
