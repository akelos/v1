<?php

class Category extends ActiveRecord
{
    var $has_many = array('methods');
    var $habtm = array('related_categories'=>array(
    'association_foreign_key'=>'related_category_id',
    'join_table'=>'related_categories',
    'join_class_name'=>'RelatedCategory',
    'unique' => true
    ));
    
    function validate()
    {
        $this->validatesUniquenessOf('name');
    }
    
    function &updateCategoryDetails(&$Method, $method_details, &$SourceAnalyzer)
    {
        static $updated_categories = array();
        if($method_details['category'] != 'none' && !in_array($method_details['category'], $updated_categories)){
            $Category =& $this->findOrCreateBy('name', $method_details['category']);
            $Category->setAttributes(array(
                'description' => $method_details['category_details']
                ));
            
            $Category->save();
            $Method->category->assign($Category);
            
            $updated_categories[] = $method_details['category'];
            
            if(false && !empty($method_details['category_relations'])){
                $RelatedCategories = array();
                foreach($method_details['category_relations'] as $category_name){
                    $RelatedCategories[] =& $this->findOrCreateBy('name', $category_name);
                }
                $Category->related_category->set($RelatedCategories);
                $Category->save();
            }
        }

        // parameters doc_metadata  category_id

        return $Category;
    }
    /*
    var $habtm = array('categories'=> array(
    'class_name'=>'Category',
    'join_table'=>'related_categories',
    'join_class_name'=>'Category',
    'foreign_key'=>'category_id',
    'association_foreign_key'=>'related_category_id',
    ));
    */
}

?>
