<h1><?php echo $Component->name; ?></h1>

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
 <li><?php echo $akelos_class->name; ?></li>
<?php } ?>
</ul>