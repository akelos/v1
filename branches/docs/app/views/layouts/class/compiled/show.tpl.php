<p id='component'><?php echo $url_helper->link_to($AkelosClass->component->name, array('controller' => 'component', 'action' => 'show', 'id' => $AkelosClass->component->id)); ?></p>
<h1><?php echo empty($AkelosClass->name) ? '' : $AkelosClass->name; ?></h1>

<p><?php echo empty($AkelosClass->file->path) ? '' : $AkelosClass->file->path; ?></p>

<?php if(!empty($AkelosClass->description)) { ?>
 <div id="akelos_class-description-<?php echo $AkelosClass->id; ?>" class="editable"><?php echo $text_helper->markdown($AkelosClass->description); ?></div>
 <?php echo $javascript_helper->javascript_tag("new Ajax.InPlaceEditor('akelos_class-description-".$AkelosClass->id."', '".$url_helper->url_for( array('controller' => 'class', 'action' => 'edit', 'id' => $AkelosClass->id))."', {okText:'".$text_helper->translate('Save')."', cancelText:'".$text_helper->translate('cancel')."', savingText:'".$text_helper->translate('Saving…')."' , clickToEditText:'".$text_helper->translate('Click to edit')."', rows:20, cols:80 });"); ?>
<?php } ?>


<?php if(!empty($AkelosClass->methods)) { ?>
    <?php 
 empty($AkelosClass->methods) ? null : $method_loop_counter = 0;
 empty($AkelosClass->methods) ? null : $methods_available = count($AkelosClass->methods);
 if(!empty($AkelosClass->methods))
     foreach ($AkelosClass->methods as $method_loop_key=>$method){
         $method_loop_counter++;
         $method_is_first = $method_loop_counter === 1;
         $method_is_last = $method_loop_counter === $methods_available;
         $method_odd_position = $method_loop_counter%2;
?>
        <?php if(empty($method->is_private)) { ?>
        <a name="<?php echo $method->name; ?>"></a>
        <h2><?php echo $method->name; ?></h2>
        <div id="method-description-<?php echo $method->id; ?>" class="editable"><?php if(!empty($method->description)) { ?><?php echo $text_helper->markdown($method->description); ?><?php } else { ?><?php echo $text_helper->translate('click to document this method', array()); ?><?php } ?></div>
        <?php echo $javascript_helper->javascript_tag("new Ajax.InPlaceEditor('method-description-".$method->id."', '".$url_helper->url_for( array('controller' => 'method', 'action' => 'edit', 'id' => $method->id))."', {okText:'".$text_helper->translate('Save')."', cancelText:'".$text_helper->translate('cancel')."', savingText:'".$text_helper->translate('Saving…')."' , clickToEditText:'".$text_helper->translate('Click to edit')."', rows:20, cols:80 });"); ?>
        
        <?php } ?>
    <?php } ?>
<?php } ?>