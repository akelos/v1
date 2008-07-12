<?php echo "<?php"; ?>

require_once(AK_LIB_DIR.DS.'AkActionMailer.php');

Ak::import('<?php echo $class_name; ?>');

class <?php echo $class_name; ?>TestCase extends AkUnitTest
{
    function setup()
    {
        $this-><?php echo $class_name; ?> =& new <?php echo $class_name; ?>();
        $this-><?php echo $class_name; ?>->delivery_method = 'test';
    }
}

?>