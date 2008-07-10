<?php

class AdminHelper extends AkActionViewHelper
{
    function admin_menu()
    {
        return $this->_render_menu('admin');
    }
    
    function can($task, $extension = null, $force_reload = false)
    {
        return User::can($task, $extension, $force_reload);
    }

    function controller_menu()
    {
        return $this->_render_menu('controller');
    }

    function _render_menu($type)
    {
        $controller =& $this->_controller;
        $current_controller = AkInflector::urlize($controller->getControllerName());
        $current_action = !empty($controller->params['action'])?$controller->params['action']:'';
        if($type == 'admin'){
        (array)$menu_options = array_merge($controller->_admin_menu_options, $controller->admin_menu_options);
        }else{
        (array)$menu_options = $controller->controller_menu_options;
        }
        $result = '';
        $i = 0;
        foreach ($menu_options as $k=>$menu_option) {
            $i++;

            $is_active = (@$controller->{"{$type}_selected_tab"} == $k || $current_controller == $menu_option['url']['controller'] && (
            $type == 'admin' || (
            empty($menu_option['url']['action']) || $current_action == $menu_option['url']['action'])
            ) ? true : false);
            
            //$is_active ? $controller->capture_helper->_addVarToView("{$type}_selected_tab", $k) : null;
            
            if(!empty($menu_option['url'])){
                $list_item_options = array(
                'id' => $menu_option['id'].'_link',
                'class' => 'tab'.($is_active?' active':'')
                );
            }else{
                trigger_error($this->t('You need to provide a valid URL for the menu tab.', E_USER_ERROR));
            }


            $show_tab = false;
            if(is_string($menu_option['url'])){
                $show_tab = $this->can($k.' ('.$menu_option['url'].')', 'Admin Menu Tabs');
            }elseif(empty($menu_option['url']['action'])){
                $show_tab = $this->can($k.' ('.$menu_option['url']['controller'].' controller)', 'Admin Menu Tabs');
            }else{
                $show_tab = $this->can($k.' ('.$menu_option['url']['controller'].' controller, '.$menu_option['url']['action'].' action)', 'Admin Menu Tabs');
            }

            if(empty($show_tab)){
                continue;
            }

            $link_options = (array)@$menu_option['link_options'];
            if($type == 'controller' && !isset($link_options['accesskey'])){
                $link_options['accesskey'] = $i;
            }

            $access_key_info = isset($link_options['accesskey']) ? ' '.$this->t('(Access key: %key)', array('%key'=>$link_options['accesskey'])) : '';

            isset($link_options['title']) ? $link_options['title'] = $this->t($link_options['title']).$access_key_info : null;
            !empty($access_key_info) && empty($link_options['title']) ? $link_options['title'] = trim($access_key_info,"() ") : null;

            $result .= $controller->tag_helper->content_tag('li',
            $controller->url_helper->link_to($this->t($k), $menu_option['url'], $link_options), $list_item_options);
        }
        return empty($result) ? '' : '<ul id="'.$type.'_menu">'.$result.'</ul>';
    }
}

?>