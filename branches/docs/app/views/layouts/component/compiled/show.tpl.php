<p class="actions"><?php echo $url_helper->link_to('All Components', array('controller' => 'component', 'action' => 'listing')); ?></p>

<?php $ancestors = $Component->tree->getAncestors(); ?>
<div id="ancestors"><?php echo $layout_helper->display_tree_recursive($ancestors); ?></div>
<h1><?php echo $Component->name; ?></h1>

<p><?php echo $Component->description; ?></p>

<?php $children = $Component->tree->getChildren(); ?>
<?php if(!empty($children)) { ?>
    <h2><?php echo $text_helper->translate('Sub-components', array()); ?></h2>
    <div id="children"><?php echo $layout_helper->display_tree_recursive($children, $Component->id); ?></div>
<?php } ?>


<?php if(!empty($Component->akelos_classes)) { ?>
<h2><?php echo $text_helper->translate('Classes', array()); ?></h2>
    <ul>
    <?php 
 empty($Component->akelos_classes) ? null : $akelos_class_loop_counter = 0;
 empty($Component->akelos_classes) ? null : $akelos_classes_available = count($Component->akelos_classes);
 if(!empty($Component->akelos_classes))
     foreach ($Component->akelos_classes as $akelos_class_loop_key=>$akelos_class){
         $akelos_class_loop_counter++;
         $akelos_class_is_first = $akelos_class_loop_counter === 1;
         $akelos_class_is_last = $akelos_class_loop_counter === $akelos_classes_available;
         $akelos_class_odd_position = $akelos_class_loop_counter%2;
?>
        <li><?php echo $url_helper->link_to($akelos_class->name, array('controller' => 'class', 'action' => 'show', 'name' => $akelos_class->name)); ?></li>
    <?php } ?>
    </ul>
<?php } ?>