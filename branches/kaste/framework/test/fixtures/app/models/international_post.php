<?php

class InternationalPost extends I18nizedActiveRecord 
{
    function __construct()
    {
        $this->addVirtualAttributesForInternationalizedColumn('title');
        $this->addVirtualAttributesForInternationalizedColumn('body');
        $attributes = (array)func_get_args();
        return $this->init($attributes);
    }
    
    
    /* Mock stuff */
    // we don't need getAvailableLocales() => decoupled the model from the actual website

    function getCurrentLocale()
    {
        return 'en';
    }

    /* concrete stuff; UNUSED code after 2nd refactoring */
    function setTitles($titles)
    {
        $this->setInternationalizedColumnsFromArray('title',$titles);
    }
    
    function getTitles()
    {
        return $this->getInternationalizedColumnsArray('title');
    }

    function getTitle()
    {
        return $this->getCurrentLocaleFromInternationalizedColumn('title');
    }
    
    function setTitle($value)
    {
        $this->setCurrentLocaleFromInternationalizedColumn('title',$value);
    }
        
}

class I18nizedActiveRecord extends ActiveRecord
{
    var $_internationalize=false;  //turn off old implementation
    var $_internationalizedColumns= array();

    function addVirtualAttributesForInternationalizedColumn($column_name)
    {
        $this->_internationalizedColumns[$column_name]= null;    
    }
    
    function _getAvailableLocaleForColumn($column_name)
    {
        if (empty($this->_internationalizedColumns[$column_name])){
            $available_columns = array_keys($this->_columns);
            $locale_for_column = array();
            foreach ($available_columns as $column){
                if (preg_match("/^([a-z]{2})_$column_name$/",$column,$matches)){
                    $locale_for_column[]= $matches[1];
                }
            }
            $this->_internationalizedColumns[$column_name] = $locale_for_column;
        }
        return $this->_internationalizedColumns[$column_name];
    }
    
    function _isInternationalizedColumn($column_name)
    {
        return array_key_exists($column_name,$this->_internationalizedColumns);
    }
    
    function _getSingularAttributeName($attribute_name)
    {
        $singular_name = AkInflector::singularize($attribute_name);
        if ($singular_name === $attribute_name)   return false;
        return $singular_name;
    }
    
    function setAttribute($attribute, $value, $inspect_for_callback_child_method = AK_ACTIVE_RECORD_ENABLE_CALLBACK_SETTERS, $compose_after_set = true)
    {
        if ($this->_isInternationalizedColumn($attribute)){
            return $this->setCurrentLocaleFromInternationalizedColumn($attribute,$value);
        }
        if(($attribute_singular = $this->_getSingularAttributeName($attribute)) && $this->_isInternationalizedColumn($attribute_singular)){
            return $this->setInternationalizedColumnsFromArray($attribute_singular,$value);
        }
        return parent::setAttribute($attribute,$value,$inspect_for_callback_child_method,$compose_after_set);        
    }
    
    function getAttribute($attribute, $inspect_for_callback_child_method = AK_ACTIVE_RECORD_ENABLE_CALLBACK_GETTERS)
    {
        if ($this->_isInternationalizedColumn($attribute)){
            return $this->getCurrentLocaleFromInternationalizedColumn($attribute);
        }
        if(($attribute_singular = $this->_getSingularAttributeName($attribute)) && $this->_isInternationalizedColumn($attribute_singular)){
            return $this->getInternationalizedColumnsFromArray($attribute_singular);
        }
        return parent::getAttribute($attribute,$inspect_for_callback_child_method);        
    }
    
    function setInternationalizedColumnsFromArray($column_name,$values)
    {
        foreach ($values as $lang=>$value){
            $attribute_name = $lang.'_'.$column_name;
            $this->set($attribute_name,$value);
        }
    }
    
    function getInternationalizedColumnsArray($column_name)
    {
        $columns = array();
        foreach ($this->_getAvailableLocaleForColumn($column_name) as $lang){
            $attribute_name = $lang.'_'.$column_name;
            $columns[$lang] = $this->get($attribute_name);
        }
        return $columns;
    }
    
    function getCurrentLocaleFromInternationalizedColumn($column_name)
    {
        $attribute_name = $this->getCurrentLocale().'_'.$column_name;
        return $this->get($attribute_name);
    }

    function setCurrentLocaleFromInternationalizedColumn($column_name,$value)
    {
        $attribute_name = $this->getCurrentLocale().'_'.$column_name;
        $this->set($attribute_name,$value);
    }
}


?>