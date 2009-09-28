<h1><?php echo $text_helper->translate('Akelos PHP Framework components', array()); ?></h1>

<?php echo $manual_helper->display_tree_recursive($Components); ?>
<hide>
<ul>
<?php 
 empty($Components) ? null : $Component_loop_counter = 0;
 empty($Components) ? null : $Components_available = count($Components);
 if(!empty($Components))
     foreach ($Components as $Component_loop_key=>$Component){
         $Component_loop_counter++;
         $Component_is_first = $Component_loop_counter === 1;
         $Component_is_last = $Component_loop_counter === $Components_available;
         $Component_odd_position = $Component_loop_counter%2;
?>
    <li><?php echo $url_helper->link_to($text_helper->translate($Component->name), array('controller' => 'manual', 'action' => 'component', 'name' => $manual_helper->urlize($Component->name))); ?></li>
<?php } ?>
</ul>
</hide>