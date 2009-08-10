<?php
require_once(AK_BASE_DIR.DS.'app'.DS.'vendor'.DS.'plugins'.DS.'acts_as_sluggable'.DS.'lib'.DS.'ActsAsSluggable.php');

class ActsAsSluggableTest extends AkUnitTest
{

    function setUp()
    {
        $this->installAndIncludeModels('SluggableThing');
        $this->instantiateModel('SluggableThing2');
    }
    
    function test_generate_slug()
    {
        $sluggable = new SluggableThing();
        $sluggable->name = 'test this slug!';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('test-this-slug-exclamation',$sluggable->slug);
    }
    
    function test_generate_slug_german()
    {
        $sluggable = new SluggableThing();
        $sluggable->name = 'dies ist ein verrückter text mit äöü';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('dies-ist-ein-verrueckter-text-mit-aeoeue',$sluggable->slug);
    }
    
    function test_generate_slug_spanish()
    {
        $sluggable = new SluggableThing();
        $sluggable->name = '¡Viva España!';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('exclamation-viva-espanya-exclamation',$sluggable->slug);
    }
    function test_generate_slug_french()
    {
        $sluggable = new SluggableThing();
        $sluggable->name = 'français ( don\'t know much more! )';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('francais-(-dont-know-much-more-exclamation-)',$sluggable->slug);
    }
    
    function test_generate_slug_custom()
    {
        $sluggable = new SluggableThing();
        $slugger = new ActsAsSluggable(&$sluggable,array('slug_source'=>'name','slug_target'=>'slug','replacements'=>array('test'=>'This is a special test')));
        $sluggable->sluggable = &$slugger;
        $sluggable->name = 'TEST';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('This-is-a-special-test',$sluggable->slug);
    }
    
    function test_generate_with_method_slug()
    {
        $sluggable = new SluggableThing2();
        $sluggable->name = 'test this slug!';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('method-colon-test-this-slug-exclamation',$sluggable->slug);
    }
    
    function test_generate_with_method_slug_german()
    {
        $sluggable = new SluggableThing2();
        $sluggable->name = 'dies ist ein verrückter text mit äöü';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('method-colon-dies-ist-ein-verrueckter-text-mit-aeoeue',$sluggable->slug);
    }
    
    function test_generate_with_method_slug_spanish()
    {
        $sluggable = new SluggableThing2();
        $sluggable->name = '¡Viva España!';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('method-colon-exclamation-viva-espanya-exclamation',$sluggable->slug);
    }
    function test_generate_with_method_slug_french()
    {
        $sluggable = new SluggableThing2();
        $sluggable->name = 'français ( don\'t know much more! )';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('method-colon-francais-(-dont-know-much-more-exclamation-)',$sluggable->slug);
    }
    
    function test_generate_with_method_slug_custom()
    {
        $sluggable = new SluggableThing2();
        $sluggable->sluggable->init(array('slug_source'=>'name','slug_target'=>'slug','replacements'=>array('test'=>'This is a special test')));
        $sluggable->name = 'TEST';
        $res = $sluggable->save();
        $this->assertTrue($res);
        $this->assertTrue(!empty($sluggable->slug));
        $this->assertEqual('method-colon-This-is-a-special-test',$sluggable->slug);
    }
}
?>