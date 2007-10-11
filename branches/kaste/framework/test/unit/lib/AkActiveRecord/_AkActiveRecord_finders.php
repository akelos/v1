<?php

class AkActiveRecord_finders_TestCase extends  AkUnitTest
{

    function setup()
    {
        $this->installAndIncludeModels(array('Post', 'Tag', 'Comment'));
        $Installer = new AkInstaller();
        @$Installer->dropTable('posts_tags');
        @Ak::file_delete(AK_MODELS_DIR.DS.'post_tag.php');
    }
    
    function test_should_find_using_id_and_options()
    {
        $Tag =& new Tag();

        $One =& $Tag->create(array('name' => 'One'));
        $Two =& $Tag->create(array('name' => 'Two'));

        //find by id is always 'first'; API-change
        //$Found =& $Tag->find('first', $Two->getId(), array('order'=>'name'));
        $Found =& $Tag->find($Two->getId(), array('order'=>'name'));

        $this->assertEqual($Found->getId(), $Two->getId());

    }

    function test_should_not_return_duplicated_owners_when_including_multiple_associates()
    {
        $Post =& new Post(array('title' => 'The best PHP Framework is ...'));
        $Post->comment->create(array('name'=>'Comment 1'));
        $Post->comment->create(array('name'=>'Comment 2'));
        $Post->tag->create(array('name'=>'Tag 1'));
        $Post->tag->create(array('name'=>'Tag 2'));

        $this->assertTrue($Post->save());

        // on PostgreSQL we get an unordered comments-list
        $this->assertTrue($Post =& $Post->find($Post->getId(), array('include'=>array('comments', 'tags'))));
        $exptected = array('Comment 1','Comment 2');
        $this->assertTrue(in_array($Post->comments[0]->get('name'),$exptected));
        $this->assertTrue(in_array($Post->comments[1]->get('name'),$exptected));
        
        // so we could do this 
        $this->assertTrue($Post =& $Post->find($Post->getId(), array('include'=>array('comments', 'tags'),'order'=>'_comments.id ASC')));
        $this->assertEqual(count($Post->comments), 2);
        $this->assertEqual($Post->comments[0]->get('name'), 'Comment 1');
        $this->assertEqual($Post->comments[1]->get('name'), 'Comment 2');

        $this->assertEqual(count($Post->tags), 2);
        $this->assertEqual($Post->tags[0]->get('name'), 'Tag 1');
        $this->assertEqual($Post->tags[1]->get('name'), 'Tag 2');

    }


}

?>
