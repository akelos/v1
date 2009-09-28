<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php if(!empty($title)) { ?><?php echo $title; ?>, <?php } ?>Akelos PHP Framework Manual</title>
    <?php echo $asset_tag_helper->javascript_include_tag(); ?>
    <?php echo $asset_tag_helper->javascript_include_tag('api'); ?>
    <?php echo $asset_tag_helper->stylesheet_for_current_controller(); ?>
    <?php echo $asset_tag_helper->javascript_for_current_controller(); ?>
</head>
<body>
<div id="layout">
    <div id="canvas">
      <?php echo $text_helper->flash(); ?>
      <?php echo $content_for_layout; ?>
    </div>
</div>
</body>
</html>