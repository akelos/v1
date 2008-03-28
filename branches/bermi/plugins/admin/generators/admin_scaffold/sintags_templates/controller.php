<?php echo '<?php'?>

<?php
$CamelCaseSingular = AkInflector::camelize($singular_name);
$CamelCasePlural = AkInflector::camelize($plural_name);
?>

class <?php echo $controller_class_name?> extends AdminController
{

    var $controller_information = '<?php  echo AkInflector::humanize($singular_name)?> management area.';
    
<?php 
    if($model_name != $controller_name){ // if equal will be handled by the Akelos directly
        echo "    var \$models = '$CamelCaseSingular';\n";
    }
    echo "    var \$admin_menu_options = array(
    '".AkInflector::humanize($controller_name)."'   => array('id' => '$controller_name', 'url'=>array('controller'=>'".AkInflector::underscore($controller_name)."', 'action'=>'listing')));\n\n";

    echo "    var \$controller_menu_options = array(";
    
    foreach (array('listing', 'add') as $k){
    echo "'".AkInflector::humanize($k)."'   => array('id' => '$k', 'url'=>array('controller'=>'".AkInflector::underscore($controller_name)."', 'action'=>'$k')),\n";
    }
    echo ");\n\n";

    
?>
   
    function index()
    {
        $this->renderAction('listing');
    }

<?php  foreach((array)@$actions as $action) :?>
    function <?php echo $action?>()
    {
    }

<?php  endforeach; ?>
    function listing()
    {
        $this-><?php echo $singular_name?>_pages = $this->pagination_helper->getPaginator($this-><?php echo $model_name?>, array('items_per_page' => 10));        
        $this-><?php echo $CamelCasePlural?> =& $this-><?php echo $model_name?>->find('all', $this->pagination_helper->getFindOptions($this-><?php echo $model_name?>));
    }

    function show()
    {
        $this-><?php echo $CamelCaseSingular?> = $this-><?php echo $model_name?>->find(@$this->params['id']);
    }

    function add()
    {
        if(!empty($this->params['<?php echo $singular_name?>'])){
            $this-><?php echo $model_name?>->setAttributes($this->params['<?php echo $singular_name?>']);
            if ($this->Request->isPost() && $this-><?php echo $model_name?>->save()){
                $this->flash['notice'] = $this->t('<?php echo $model_name?> was successfully created.');
                $this->redirectTo(array('action' => 'show', 'id' => $this-><?php echo $model_name?>->getId()));
            }
        }
    }
    <?php  if($model_name != $controller_name){ ?>

    function edit()
    {
        if(!empty($this->params['id'])){
            if(empty($this-><?php echo $CamelCaseSingular?>->id) || $this-><?php echo $CamelCaseSingular?>->id != $this->params['id']){
                $this-><?php echo $CamelCaseSingular?> =& $this-><?php echo $model_name?>->find($this->params['id']);
            }
        }else{
            $this->redirectToAction('listing');
        }

        if(!empty($this->params['<?php echo $singular_name?>'])){
            $this-><?php echo $CamelCaseSingular?>->setAttributes($this->params['<?php echo $singular_name?>']);
            if($this->Request->isPost() && $this-><?php echo $CamelCaseSingular?>->save()){
                $this->flash['notice'] = $this->t('<?php echo $model_name?> was successfully updated.');
                $this->redirectTo(array('action' => 'show', 'id' => $this-><?php echo $CamelCaseSingular?>->getId()));
            }
        }
    }
    <?php } else { ?>

    function edit()
    {
        if (empty($this->params['id'])){
         $this->redirectToAction('listing');
        }
        if(!empty($this->params['<?php echo $singular_name?>']) && !empty($this->params['id'])){
            $this-><?php echo $CamelCaseSingular?>->setAttributes($this->params['<?php echo $singular_name?>']);
            if($this->Request->isPost() && $this-><?php echo $CamelCaseSingular?>->save()){
                $this->flash['notice'] = $this->t('<?php echo $model_name?> was successfully updated.');
                $this->redirectTo(array('action' => 'show', 'id' => $this-><?php echo $CamelCaseSingular?>->getId()));
            }
        }
    }
    <?php } ?>
    

    function destroy()
    {
        if(!empty($this->params['id'])){
            if($this-><?php echo $CamelCaseSingular?> =& $this-><?php echo $CamelCaseSingular?>->find($this->params['id'])){
                if($this->Request->isPost()){
                    $this-><?php echo $CamelCaseSingular?>->destroy();
                    $this->flash_options = array('seconds_to_close' => 10);
                    $this->flash['notice'] = $this->t('<?php  echo AkInflector::humanize($singular_name)?> was successfully deleted.');
                    $this->redirectToAction('listing');
                }
            }else {
                $this->flash['error'] = $this->t('<?php  echo AkInflector::humanize($singular_name)?> not found.');
                $this->redirectToAction('listing');
            }
        }
    }
}

?>