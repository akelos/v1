<?php

require_once('_HelpersUnitTester.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'text_helper.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'tag_helper.php');


class TextHelperTests extends HelpersUnitTester
{

    public function SetUp()
    {
        $this->text = new TextHelper();
    }

    public function test_truncate()
    {
        // normal with 10 char limit
        $this->assertEqual($this->text->truncate('truncates the last ten characters', 10), 'truncat...');

        //limit of 3 characters
        $this->assertEqual($this->text->truncate('truncates', 3), '...');

        //less of the limit of 3 characters
        $this->assertEqual($this->text->truncate('truncates', 2), 'truncates');

        //with an empty truncate_string
        $this->assertEqual($this->text->truncate('truncates', 5, ''), 'trunc');

        //with a break position greater than the limit
        $this->assertEqual($this->text->truncate('0123456789_0',8,'...', '_'), '01234...');

        //with a break position lesser than the limit
        $this->assertEqual($this->text->truncate('0123_567890',8,'...', '_'), '0...');

        //with a break position lesser than the truncate string length
        $this->assertEqual($this->text->truncate('0_123567890',8,'...', '_'), '0_123567890');
    }

    public function test_highlight()
    {
        $this->assertEqual($this->text->highlight('I am highlighting the phrase','highlighting'),'I am <strong class="highlight">highlighting</strong> the phrase');
        $this->assertEqual($this->text->highlight('I am highlighting the phrase',array('highlighting','the')),'I am <strong class="highlight">highlighting</strong> <strong class="highlight">the</strong> phrase');
        $this->assertEqual($this->text->highlight('I am highlighting the phrase','highlighting', '<span class="my_highlight">\1</span>'),'I am <span class="my_highlight">highlighting</span> the phrase');
    }

    public function test_excerpt()
    {
        $this->assertEqual($this->text->excerpt("hello my world", "my", 3),'...lo my wo...');
        $this->assertEqual($this->text->excerpt("hello my world", "my", 5,'---'),'---ello my worl---');
        $this->assertEqual($this->text->excerpt("hello my world", "my", 0),'...my...');
    }

    public function test_pluralize()
    {
        $this->assertEqual($this->text->pluralize(0, 'Property', 'Properties'),'Properties');
        $this->assertEqual($this->text->pluralize(1, 'Property'),'Property');
        $this->assertEqual($this->text->pluralize(2, 'Property'),'Properties');
        $this->assertEqual($this->text->pluralize(2, 'Address'),'Addresses');
        $this->assertEqual($this->text->pluralize(2, 'Son'),'Sons');
    }

    public function test_word_wrap()
    {
        $this->assertEqual($this->text->word_wrap('Wraps a string to a given number of characters', 20),"Wraps a string to a\ngiven number of\ncharacters");
    }
    public function test_format()
    {
        $this->assertEqual($this->text->format('Format this string which is very long and need to be formated !!!'),"    Format this string which is very long and need to be formated !!!\n\n");
    }

    public function test_textilize()
    {
        $this->assertEqual($this->text->textilize('__Wraps__'),'<p><i>Wraps</i></p>');
        $this->assertEqual($this->text->textilize('_Wraps_'),'<p><em>Wraps</em></p>');
        $this->assertEqual($this->text->textilize('p[no]. paragraph'),'<p lang="no">paragraph</p>');
        $this->assertEqual($this->text->textilize('h3{color:red}. header 3'),'<h3 style="color:red;">header 3</h3>');
    }

    public function test_textilize_without_paragraph()
    {
        $this->assertEqual($this->text->textilize_without_paragraph('__Wraps__'),'<i>Wraps</i>');
        $this->assertEqual($this->text->textilize_without_paragraph('p[no]. paragraph'),'paragraph');
    }

    public function test_simple_format()
    {
        $this->assertEqual($this->text->simple_format("Test\r\n"),"<p>Test\n</p>");
        $this->assertEqual($this->text->simple_format("Test\n"),"<p>Test\n</p>");
        $this->assertEqual($this->text->simple_format("Test\r"),"<p>Test\n</p>");
        $this->assertEqual($this->text->simple_format("Test\n\nTest"),"<p>Test</p>\n<p>Test</p>");
        $this->assertEqual($this->text->simple_format("Test\n\n"),"<p>Test</p><br /><br />");
        $this->assertEqual($this->text->simple_format("Test\n\n\n\n\n\n"),"<p>Test</p><br /><br />");
    }

    public function test_auto_link_email_addresses()
    {
        $this->assertEqual($this->text->auto_link_email_addresses('sending an email to salavert@example.com and to hilario@example.com'),'sending an email to <a href=\'mailto:salavert@example.com\'>salavert@example.com</a> and to <a href=\'mailto:hilario@example.com\'>hilario@example.com</a>');
        $this->assertEqual($this->text->auto_link_email_addresses('salavert@@example.com'),'salavert@@example.com');
        $this->assertEqual($this->text->auto_link_email_addresses('email sent to salavert@example.c'),'email sent to <a href=\'mailto:salavert@example.c\'>salavert@example.c</a>');
    }

    public function test_auto_link_urls()
    {
        $this->assertEqual($this->text->auto_link_urls('http://www.thebigmover.com'),'<a href="http://www.thebigmover.com">http://www.thebigmover.com</a>');
        $this->assertEqual($this->text->auto_link_urls('www.thebigmover.com'),'<a href="http://www.thebigmover.com">www.thebigmover.com</a>');
        $this->assertEqual($this->text->auto_link_urls('www.thebigmover.com nested www.thebigmover.com/search'),'<a href="http://www.thebigmover.com">www.thebigmover.com</a> nested <a href="http://www.thebigmover.com/search">www.thebigmover.com/search</a>');
        $this->assertEqual($this->text->auto_link_urls('Visit http://www.thebigmover.com now'),'Visit <a href="http://www.thebigmover.com">http://www.thebigmover.com</a> now');
        $this->assertEqual($this->text->auto_link_urls('Visit http://www.thebigmover.com now and later http://www.akelos.com'),'Visit <a href="http://www.thebigmover.com">http://www.thebigmover.com</a> now and later <a href="http://www.akelos.com">http://www.akelos.com</a>');
    }

    public function test_strip_links()
    {
        $this->assertEqual($this->text->strip_links('email sent to <a href=\'mailto:salavert@example.c\'>salavert@example.c</a>'),'email sent to salavert@example.c');
        $this->assertEqual($this->text->strip_links('sending an email to <a href="mailto:salavert@example.com">salavert@example.com</a> and to <a href="mailto:hilario@example.com">hilario@example.com</a>'),'sending an email to salavert@example.com and to hilario@example.com');
    }

    public function test_strip_selected_tags()
    {
        $this->assertEqual($this->text->strip_selected_tags('sending <b>email</b> to <a href="mailto:salavert@example.com">salavert@example.com</a>','a','b'),'sending email to salavert@example.com');
        $this->assertEqual($this->text->strip_selected_tags('sending <b>email</b> to <a href="mailto:salavert@example.com">salavert@example.com</a>',array('a','b')),'sending email to salavert@example.com');
        $this->assertEqual($this->text->strip_selected_tags('sending <b>email</b> to <a href="mailto:salavert@example.com">salavert@example.com</a>','a'),'sending <b>email</b> to salavert@example.com');
    }

    public function test_auto_link()
    {
        $this->assertEqual($this->text->auto_link('email sent to salavert@example.com from http://www.thebigmover.com','all'),'email sent to <a href=\'mailto:salavert@example.com\'>salavert@example.com</a> from <a href="http://www.thebigmover.com">http://www.thebigmover.com</a>');
        $this->assertEqual($this->text->auto_link('email sent to salavert@example.com','email_addresses'),'email sent to <a href=\'mailto:salavert@example.com\'>salavert@example.com</a>');
        $this->assertEqual($this->text->auto_link('email sent from http://www.thebigmover.com','urls'),'email sent from <a href="http://www.thebigmover.com">http://www.thebigmover.com</a>');
    }

    public function test_strip_tags()
    {
        $this->assertEqual($this->text->strip_tags('<a href="nowhere" onclick="javascript:alert(\'oops\');">link</a>'),'link');
    }

    public function test_markdown()
    {
        $this->assertEqual($this->text->markdown('> ## This is a header.
> 
> 1.   This is the first list item.
> 2.   This is the second list item.
> 
> Here\'s some example code:
> 
>     return shell_exec("echo $input | $markdown_script");'),'<blockquote>
  <h2>This is a header.</h2>
  
  <ol>
  <li>This is the first list item.</li>
  <li>This is the second list item.</li>
  </ol>
  
  <p>Here\'s some example code:</p>

<pre><code>return shell_exec("echo $input | $markdown_script");
</code></pre>
</blockquote>');
    }
}


ak_test('TextHelperTests');

?>