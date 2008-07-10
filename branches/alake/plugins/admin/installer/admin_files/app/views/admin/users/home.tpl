<div id="sidebar">
  <h1>_{Tasks}:</h1>
  <ul>
    {?user-user_code}
        <li><?php  echo  $url_helper->link_to($text_helper->translate('Show')." ".$user['name_first']." ".$user['name_last'], array('action' => 'show'))?></li>
    {end}
    {?user-admin}
        <li><?php  echo  $url_helper->link_to($text_helper->translate('List Users'), array('action' => 'listing'))?></li>
    {end}
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Log out'), array('action' => 'login'))?></li>
  </ul> 
</div>


<div id="content">
  <h1>System Home Page</h1>

  <div class="show">
      <p>You are logged in as <?= $user_code ?>.</p><br />
      
      <p>You have completed the login procedure.  This page is a placeholder.</p>   
  </div>
</div>