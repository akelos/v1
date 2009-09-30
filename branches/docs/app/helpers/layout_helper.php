<?php

class LayoutHelper extends AkActionViewHelper
{
    function urlize($text)
    {
        return AkInflector::urlize($text);
    }

    function display_tree_recursive($tree, $parent_id = null, $options = array())
    {
        if(!empty($tree)){
            $result = "<ul>\n";
            foreach(array_keys($tree) as $k){
                $Node =& $tree[$k];
                if($Node->parent_id == $parent_id){
                    $result .= "<li>";
                    $result .= $this->link_to_node($Node, $options);
                    $result .= $this->display_tree_recursive($tree, $Node->id);
                    $result .= "</li>\n";
                }
            }
            return $result."</ul>\n";
        }
    }

    function link_to_node($Node, $options = array())
    {
        $detault_options = array(
            'display' => 'name',
            'id' => $Node->id,
            'controller'=> AkInflector::underscore($Node->getModelName()),
            'action' => 'show'
            );
        $options = array_merge($detault_options, $options);
        $display = $Node->get($options['display']);
        unset($options['display']);
        return $this->_controller->url_helper->link_to($display, $options);
    }

}

?>
