<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{?title}{title}, {end}Akelos PHP Framework Manual</title>
    <%= javascript_include_tag %>
    <%= javascript_include_tag 'api' %>
    <%= stylesheet_for_current_controller %>
    <%= javascript_for_current_controller %>
</head>
<body>
<div id="layout">
    <div id="canvas">
      <%= flash %>
      {content_for_layout}
    </div>
</div>
</body>
</html>