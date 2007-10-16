<?php class AkTestMember extends AkTestUser { 
            //var $_inheritanceColumn = "ak_test_user_id";
                function AkTestMember(){
                    $this->setTableName("ak_test_members");
                    $this->init(@(array)func_get_args());
                }
            } ?>