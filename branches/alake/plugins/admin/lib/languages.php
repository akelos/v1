<?php
class Languages
{
    function Languages()
    {
        $abbr = explode(',',AK_AVAILABLE_LOCALES);
        $name = explode(',',LOCALES);
        $this->language_array = array_combine($name,$abbr);
    }
    
    function get()
    {
    return $this->language_array;
    }
}
?>