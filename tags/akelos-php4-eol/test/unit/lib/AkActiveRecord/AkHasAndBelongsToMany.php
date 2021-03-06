<?php

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class HasAndBelongsToManyTestCase extends  AkUnitTest
{

    function test_start()
    {
        $Installer = new AkInstaller();
        @$Installer->dropTable('posts_tags');
        @$Installer->dropTable('friends_friends');
        @$Installer->dropTable('posts_users');
        @Ak::file_delete(AK_MODELS_DIR.DS.'post_tag.php');
        @Ak::file_delete(AK_MODELS_DIR.DS.'post_user.php');
        @Ak::file_delete(AK_MODELS_DIR.DS.'friend_friend.php');
        
        $this->installAndIncludeModels(array('Post', 'Tag','Picture', 'Thumbnail','Panorama', 'Property', 'PropertyType', 'User'));
    }

    /**/
    function test_getAssociatedModelInstance_should_return_a_single_instance()  // bug-fix
    {
        $this->assertReference($this->Post->tag->getAssociatedModelInstance(),$this->Post->tag->getAssociatedModelInstance());
    }


    function test_for_has_and_belongs_to_many()
    {

        $Property =& new Property(array('description'=>'Gandia Palace'));
        $this->assertEqual($Property->property_type->getType(), 'hasAndBelongsToMany');
        $this->assertTrue(is_array($Property->property_types) && count($Property->property_types) === 0);

        $Property->property_type->load();
        $this->assertEqual($Property->property_type->count(), 0);

        $Chalet =& new PropertyType(array('description'=>'Chalet'));

        $Property->property_type->add($Chalet);
        $this->assertEqual($Property->property_type->count(), 1);

        $this->assertReference($Property->property_types[0], $Chalet);

        $Property->property_type->add($Chalet);
        $this->assertEqual($Property->property_type->count(), 1);

        $Condo =& new PropertyType(array('description'=>'Condominium'));
        $Property->property_type->add($Condo);

        $this->assertEqual($Property->property_type->count(), 2);

        $this->assertTrue($Property->save());

        $this->assertFalse($Chalet->isNewRecord());
        $this->assertFalse($Condo->isNewRecord());

        $this->assertTrue($Chalet = $Chalet->findFirstBy('description','Chalet', array('include'=>'properties')));
        $this->assertEqual($Chalet->properties[0]->getId(), $Property->getId());

        $this->assertTrue($Condo = $Condo->findFirstBy('description','Condominium', array('include'=>'properties')));
        $this->assertEqual($Condo->properties[0]->getId(), $Property->getId());

        $this->assertReference($Chalet, $Property->property_types[0]);
        $this->assertReference($Condo, $Property->property_types[1]);

        $Property =& new Property($Property->getId());
        $Property->property_type->load();
        $this->assertEqual($Property->property_type->association_id, 'property_types');
        $this->assertEqual($Property->property_type->count(), 2);

        $Property->property_types = array();
        $this->assertEqual($Property->property_type->count(), 0);

        $Property->property_type->load();
        $this->assertEqual($Property->property_type->count(), 0);

        $Property->property_type->load(true);
        $this->assertEqual($Property->property_type->count(), 2);

        $this->assertEqual($Property->property_types[1]->getType(), 'PropertyType');

        $Property->property_type->delete($Property->property_types[1]);

        $this->assertEqual($Property->property_type->count(), 1);

        $Property->property_type->load(true);
        $this->assertEqual($Property->property_type->count(), 1);


        $Property =& $Property->findFirstBy('description','Gandia Palace');
        $PropertyType =& new PropertyType();

        $PropertyTypes =& $PropertyType->find();

        $Property->property_type->set($PropertyTypes);

        $this->assertEqual($Property->property_type->count(), count($PropertyTypes));

        $Property =& $Property->findFirstBy('description','Gandia Palace');

        $Property->property_type->load();
        $this->assertEqual($Property->property_type->count(), count($PropertyTypes));

        $Property =& $Property->findFirstBy('description','Gandia Palace');
        
        $PropertyType->set('description', 'Palace');

        $Property->property_type->set($PropertyType);

        $this->assertEqual($Property->property_type->count(), 1);

        $this->assertTrue(in_array('property_types', $Property->getAssociatedIds()));

        $Property = $Property->findFirstBy('description','Gandia Palace',array('include'=>'property_types'));
        
        $this->assertIdentical($Property->property_type->count(), 1);
        
        $this->assertTrue($Property->property_type->delete($Property->property_types[0]));

        $this->assertIdentical($Property->property_type->count(), 0);
        
        $Property = $Property->findFirstBy('description','Gandia Palace');
        
        $this->assertIdentical($Property->property_type->count(), 0);
        
        // It should return existing Property even if it doesnt have property_types
        $this->assertTrue($Property->findFirstBy('description','Gandia Palace',array('include'=>'property_types')));

        $Property =& new Property(array('description'=> 'Luxury Downtown House'));
        $Apartment =& $PropertyType->create(array('description'=>'Apartment'));
        $Loft =& $PropertyType->create(array('description'=>'Loft'));
        $Penthouse =& $PropertyType->create(array('description'=>'Penthouse'));

        $Property->property_type->setByIds(array($Apartment->getId(),$Loft->getId(),$Penthouse->getId()));

        $this->assertEqual($Property->property_type->count(), 3);

        $this->assertTrue($Property->save());
        $this->assertTrue($Property->save());

        $this->assertTrue($Property =& $Property->findFirstBy('description', 'Luxury Downtown House'));
        
        $Property->property_type->load();

        $this->assertEqual($Property->property_type->count(), 3);

        $FoundApartment = $Property->property_type->find('first', array('description'=>'Apartment'));
        $this->assertEqual($Apartment->get('description').$Apartment->getId(), $FoundApartment->get('description').$FoundApartment->getId());

        $FoundTypes = $Property->property_type->find();
        
        $this->assertEqual(count($FoundTypes), $Property->property_type->count());

        $descriptions = array();
        foreach ($FoundTypes as $FoundType){
            $descriptions[] = $FoundType->get('description');
        }
        sort($descriptions);

        $this->assertEqual($descriptions, array('Apartment','Loft','Penthouse'));

        $this->assertFalse($Property->property_type->isEmpty());

        $this->assertEqual($Property->property_type->getSize(), 3);

        $this->assertTrue($Property->property_type->clear());

        $this->assertTrue($Property->property_type->isEmpty());

        $this->assertEqual($Property->property_type->getSize(), 0);

        $Property =& new Property();

        $LandProperty =& $Property->property_type->build(array('description'=>'Land'));

        $this->assertReference($LandProperty, $Property->property_types[0]);

        $this->assertTrue($Property->property_types[0]->isNewRecord());

        $this->assertEqual($LandProperty->getType(), 'PropertyType');

        $Property->set('description', 'Plot of Land in Spain');

        $this->assertTrue($Property->save());

        $this->assertTrue($LandProperty = $Property->findFirstBy('description', 'Plot of Land in Spain', array('include'=>'property_types')));

        $this->assertEqual($LandProperty->property_types[0]->get('description'), 'Land');

        $Property =& new Property(array('description'=>'Seaside house in Altea'));
        $SeasidePropertyType =& $Property->property_type->create(array('description'=>'Seaside property'));
        $this->assertReference($SeasidePropertyType, $Property->property_types[0]);
        $this->assertTrue($SeasidePropertyType->isNewRecord());

        $Property =& new Property(array('description'=>'Bermi\'s appartment in Altea'));
        $this->assertTrue($Property->save());
        $SeasidePropertyType =& $Property->property_type->create(array('description'=>'Seaside property'));
        $this->assertReference($SeasidePropertyType, $Property->property_types[0]);
        $this->assertFalse($SeasidePropertyType->isNewRecord());

        $this->assertTrue($PropertyInAltea = $Property->findFirstBy('description', 'Bermi\'s appartment in Altea', array('include'=>'property_types')));

        $this->assertEqual($PropertyInAltea->property_types[0]->get('description'), 'Seaside property');


        // Testing destroy callbacks
        $this->assertTrue($Property =& $Property->findFirstBy('description', 'Bermi\'s appartment in Altea'));
        $property_id = $Property->getId();
        //echo '<pre>'.print_r($Property->_associations, true).'</pre>';

        $this->assertTrue($Property->destroy());

        $RecordSet = $PropertyInAltea->_db->execute('SELECT * FROM properties_property_types WHERE property_id = '.$property_id);
        $this->assertEqual($RecordSet->RecordCount(), 0);

    }

    function test_find_on_unsaved_models_including_associations()
    {
        $Property =& new Property('description->','Chalet by the sea');

        $PropertyType =& new PropertyType();
        $this->assertTrue($PropertyTypes = $PropertyType->findAll());
        $Property->property_type->add($PropertyTypes);
        $this->assertTrue($Property->save());

        $Property =& new Property();

        $expected = array();
        foreach (array_keys($PropertyTypes) as $k){
            $expected[] = $PropertyTypes[$k]->get('description');
        }

        $this->assertTrue($Properties = $Property->findFirstBy('description', 'Chalet by the sea',  array('include'=>'property_type')),'Finding including habtm associated from a new object doesn\'t work');

        foreach (array_keys($Properties->property_types) as $k){
            $this->assertTrue(in_array($Properties->property_types[$k]->get('description'),$expected));
        }
    }


    function test_clean_up_dependencies()
    {
        $Property =& new Property('description->','Luxury Estate');
        $PropertyType =& new PropertyType();
        $this->assertTrue($PropertyType =& $PropertyType->create(array('description'=>'Mansion')));
        $Property->property_type->add($PropertyType);
        $this->assertTrue($Property->save());

        $PropertyType =& $PropertyType->findFirstBy('description','Mansion');
        $PropertyType->property->load();
        $this->assertEqual($PropertyType->properties[0]->getId(), $Property->getId());
        $this->assertEqual($PropertyType->property->count(), 1);

        $this->assertTrue($Property->destroy());


        $PropertyType =& $PropertyType->findFirstBy('description','Mansion');
        $PropertyType->property->load();
        $this->assertTrue(empty($PropertyType->properties[0]));
        $this->assertEqual($PropertyType->property->count(), 0);

    }


    function test_double_assignation()
    {
        $AkelosOffice =& new Property(array('description'=>'Akelos new Office'));
        $this->assertTrue($AkelosOffice->save());

        $PalafollsOffice =& new Property(array('description'=>"Bermi's home office"));
        $this->assertTrue($PalafollsOffice->save());

        $CoolOffice =& new PropertyType(array('description'=>'Cool office'));
        $this->assertTrue($CoolOffice->save());

        $AkelosOffice->property_type->add($CoolOffice);
        $this->assertEqual($CoolOffice->property->count(), 1);

        $PalafollsOffice->property_type->add($CoolOffice);
        $this->assertEqual($CoolOffice->property->count(), 2);
    }


    function test_scope_for_multiple_member_deletion()
    {
        $PisoJose =& new Property('description->','Piso Jose');
        $PisoBermi =& new Property('description->','Piso Bermi');

        $Atico =& new PropertyType('description->','Ático');
        $Apartamento =& new PropertyType('description->','Apartamento');

        $this->assertTrue($PisoJose->save() && $PisoBermi->save() && $Atico->save() && $Apartamento->save());

        $PisoJose->property_type->add($Atico);
        $PisoJose->property_type->add($Apartamento);

        $PisoBermi->property_type->add($Atico);
        $PisoBermi->property_type->add($Apartamento);


        $this->assertTrue($PisoJose =& $PisoJose->findFirstBy('description','Piso Jose'));
        $this->assertTrue($Atico =& $Atico->findFirstBy('description','Ático'));

        $PisoJose->property_type->load();

        $PisoJose->property_type->delete($Atico);

        $this->assertTrue($PisoBermi =& $PisoBermi->findFirstBy('description','Piso Bermi'));

        $this->assertTrue($PisoJose =& $PisoJose->findFirstBy('description','Piso Jose'));
        $PisoJose->property_type->load();

        $this->assertTrue($Atico =& $Atico->findFirstBy('description','Ático'));
        $this->assertTrue($Apartamento =& $Apartamento->findFirstBy('description','Apartamento'));

        $this->assertEqual($PisoJose->property_types[0]->getId(), $Apartamento->getId());
        $this->assertEqual($PisoBermi->property_type->count(), 2);


    }


    function test_associated_uniqueness()
    {
        $Property =& new Property();
        $PropertyType =& new PropertyType();

        $this->assertTrue($RanchoMaria =& $Property->create(array('description'=>'Rancho Maria')));
        $this->assertTrue($Rancho =&  $PropertyType->create(array('description'=>'Rancho')));

        $Rancho->property->load();
        $this->assertEqual($Rancho->property->count(), 0);
        $Rancho->property->add($RanchoMaria);
        $this->assertEqual($Rancho->property->count(), 1);

        $this->assertTrue($RanchoMaria =& $Property->findFirstBy('description','Rancho Maria'));
        $this->assertTrue($Rancho =&  $PropertyType->findFirstBy('description','Rancho', array('include'=>'properties')));

        $Rancho->property->add($RanchoMaria);
        $this->assertEqual($Rancho->property->count(), 1);

        $Rancho->set('description', 'Rancho Type');
        $this->assertTrue($Rancho->save());
        $this->assertTrue($Rancho =&  $PropertyType->findFirstBy('description','Rancho Type', array('include'=>'properties')));
        $this->assertEqual($Rancho->property->count(), 1);
    }

    function test_should_include_associates_using_simple_finder()
    {
        $Property =& new Property();
        $PropertyType =& new PropertyType();
        $this->assertTrue($Rancho =&  $PropertyType->findFirstBy('description','Rancho Type', array('include'=>'properties')));

        $this->assertTrue($RanchoMaria =& $Property->find($Rancho->properties[0]->getId(), array('include'=>'property_types')));

        $this->assertEqual($RanchoMaria->property_types[0]->getId(), $Rancho->getId());
        $this->assertEqual($RanchoMaria->getId(), $Rancho->properties[0]->getId());
    }


    function test_should_remove_associated_using_the_right_key()
    {
        $Installer =& new AkInstaller();
        @$Installer->dropTable('groups_users');
        @Ak::file_delete(AK_MODELS_DIR.DS.'group_user.php');

        
        $this->installAndIncludeModels('User', 'Group', array('instantiate' => true));

        $Admin =& $this->Group->create(array('name' => 'Admin'));

        $Moderator =& $this->Group->create(array('name' => 'Moderator'));

        $this->assertFalse($Admin->hasErrors());
        $this->assertFalse($Moderator->hasErrors());

        $Salavert =& $this->User->create(array('name' => 'Jose'));
        $this->assertFalse($Salavert->hasErrors());

        $Salavert->group->setByIds($Admin->getId(), $Moderator->getId());

        $Salavert =& $this->User->find($Salavert->getId());
        $this->assertEqual(2, $Salavert->group->count());

        $Jyrki =& $this->User->create(array('name' => 'Jyrki'));
        $this->assertFalse($Jyrki->hasErrors());
        $Jyrki->group->setByIds($Admin->getId(), $Moderator->getId());
        $Jyrki =& $this->User->find($Jyrki->getId());
        $this->assertEqual(2, $Jyrki->group->count());

        $Jyrki->destroy();
        $Salavert =& $this->User->find($Salavert->getId());
        $this->assertEqual(2, $Salavert->group->count());

    }


    function test_remove_existing_associates_before_setting_by_id()
    {

        foreach (range(1,10) as $i){
            $Post =& new Post(array('title' => 'Post '.$i));
            $Post->tag->create(array('name' => 'Tag '.$i));
            $this->assertTrue($Post->save());
            $this->assertEqual($Post->tag->count(), 1, 'Failed on #'.$i);  // dont know why but this fails sometimes, randomly -kaste
        }

        $Post11 =& new Post(array('title' => 'Post 11'));
        $this->assertTrue($Post11->save());

        $Post->tag->setByIds(1,2,3,4,5);

        $this->assertTrue($Post =& $Post->find(10, array('include' => 'tags','order' => '_tags.id ASC')));

        foreach (array_keys($Post->tags) as $k){
            $this->assertEqual($Post->tags[$k]->getId(), $k+1);
        }

        // Tag 10 should exist but unrelated to a post
        $this->assertTrue($Tag =& $Post->tags[$k]->find(10));
        $this->assertEqual($Tag->post->count(), 0);

        $Post11->tag->setByIds(array(10,1));

        $this->assertTrue($Tag =& $Tag->find(10, array('include'=>'posts')));
        $this->assertEqual($Tag->posts[0]->getId(), 11);


    }



    function test_should_allow_multiple_habtm_associates_on_fresh_association_owner()
    {
        $Bermi =& new User(array('name'=>'Bermi'));
        $Bermi->post->set(new Post(array('title' => 'Bermi Post')));
        $Bermi->save();

        $Bermi =& $this->User->findFirstBy('name', 'Bermi', array('include'=>'posts'));

        $this->assertEqual($Bermi->posts[0]->title, 'Bermi Post');
    }


    function test_should_remove_existing_associates_when_setting_new_ones_and_parent_is_saved()
    {
        for ($i=0; $i < 3; $i++){
            $Bermi =& $this->User->findFirstBy('name', 'Bermi');
            $Post =& $this->Post->findFirstBy('title', 'Bermi Post');
            $Bermi->post->set($Post);
            $Bermi->save();
        }

        $this->assertEqual($Bermi->post->count(true), 1);

        $PostUser =& new PostUser();
        $PostUsers = $PostUser->findAllBy('user_id', $Bermi->id);

        $this->assertEqual(count($PostUsers), 1);
        
        $PostUsers = $PostUser->findAllBy('post_id', $Post->id);
        $this->assertEqual(count($PostUsers), 1);
    }
    
    
    function test_should_allow_same_model_habtm_associations()
    {
        $this->installAndIncludeModels(array('Friend'=>'id,name'));
        
        $Mary =& $this->Friend->create(array('name' => 'Mary'));

        $Mary->friend->add($this->Friend->create(array('name' => 'James')));
        
        $Mary = $this->Friend->findFirstBy('name', 'Mary', array('include'=>'friends'));
        
        $this->assertEqual($Mary->friends[0]->name, 'James');
    }
    
	function test_find_on_association_with_conditions_string_sql()
    {
        if (AK_PHP5) {
            $this->installAndIncludeModels(array('Friend'=>'id,name'));
            $Mary =& $this->Friend->create(array('name' => 'Mary'));
            $Mary->friend->add($this->Friend->create(array('name' => 'James')));
            
            
            //$db =& new AkDbAdapter(array());  // no conection details, we're using a Mock
            Mock::generate('ADOConnection');
            $connection =& new MockADOConnection();
            $result = new ADORecordSet_array(-1);
            $result->InitArray(array(array('id'=>1,'name'=>'James')),array('id'=>'I','name'=>'C'));
            $connection->setReturnValue('Execute',$result);
            if ($Mary->_db->type()=='sqlite') {
                 $connection->expectAt(0,'Execute',array('SELECT friends.* FROM friends LEFT OUTER JOIN friends_friends AS _FriendFriend ON _FriendFriend.related_id = friends.id LEFT OUTER JOIN friends AS _Friend ON _FriendFriend.friend_id = _Friend.id WHERE (friends.name = \'James\') AND (_FriendFriend.friend_id  LIKE  1) AND 1'));
            } else {
                $connection->expectAt(0,'Execute',array('SELECT friends.* FROM friends LEFT OUTER JOIN friends_friends AS _FriendFriend ON _FriendFriend.related_id = friends.id LEFT OUTER JOIN friends AS _Friend ON _FriendFriend.friend_id = _Friend.id WHERE (friends.name = \'James\') AND (_FriendFriend.friend_id  =  1)'));
            }
            $oldConnection = $Mary->_db->connection;
            $Mary->_db->connection =& $connection;
            //$Mary->_db = $db;
            $Mary->friend->find(array('conditions'=>"name = 'James'"));
            $Mary->_db->connection = $oldConnection;
        }
    }
    function test_find_on_association_with_conditions_string()
    {
        $this->installAndIncludeModels(array('Friend'=>'id,name'));
        $Mary =& $this->Friend->create(array('name' => 'Mary'));
        $Mary->friend->add($this->Friend->create(array('name' => 'James')));
        $Mary->save();
        $result = $Mary->friend->find(array('conditions'=>"name = 'James'"));
        $James = $result[0];
        $this->assertEqual($Mary->friends[0]->name, $James->name);
    }
    
    function test_find_on_association_with_conditions_array_sql()
    {
        if (AK_PHP5) {
            $this->installAndIncludeModels(array('Friend'=>'id,name'));
            $Mary =& $this->Friend->create(array('name' => 'Mary'));
            $Mary->friend->add($this->Friend->create(array('name' => 'James')));
            
            
            //$db =& new AkDbAdapter(array());  // no conection details, we're using a Mock
            Mock::generate('ADOConnection');
            $connection =& new MockADOConnection();
            $result = new ADORecordSet_array(-1);
            $result->InitArray(array(array('id'=>1,'name'=>'James')),array('id'=>'I','name'=>'C'));
            $connection->setReturnValue('Execute',$result);
            if ($Mary->_db->type()=='sqlite') {
                $connection->expectAt(0,'Execute',array('SELECT friends.* FROM friends LEFT OUTER JOIN friends_friends AS _FriendFriend ON _FriendFriend.related_id = friends.id LEFT OUTER JOIN friends AS _Friend ON _FriendFriend.friend_id = _Friend.id WHERE (friends.name = ?) AND (_FriendFriend.friend_id  LIKE  1) AND 1', array('James')));
            
            } else {
                $connection->expectAt(0,'Execute',array('SELECT friends.* FROM friends LEFT OUTER JOIN friends_friends AS _FriendFriend ON _FriendFriend.related_id = friends.id LEFT OUTER JOIN friends AS _Friend ON _FriendFriend.friend_id = _Friend.id WHERE (friends.name = ?) AND (_FriendFriend.friend_id  =  1)', array('James')));
            }
            $oldConnection = $Mary->_db->connection;
            $Mary->_db->connection =& $connection;
            //$Mary->_db = $db;
            $Mary->friend->find(array('conditions'=>array('name = ?','James')));
            $Mary->_db->connection = $oldConnection;
        }
    }
    function test_find_on_association_with_conditions_array()
    {
        $this->installAndIncludeModels(array('Friend'=>'id,name'));
        $Mary =& $this->Friend->create(array('name' => 'Mary'));
        $Mary->friend->add($this->Friend->create(array('name' => 'James')));
        $Mary->save();
        $result = $Mary->friend->find(array('conditions'=>array('name = ?','James')));
        $James = $result[0];
        $this->assertEqual($Mary->friends[0]->name, $James->name);
    }
}

ak_test('HasAndBelongsToManyTestCase');

?>
