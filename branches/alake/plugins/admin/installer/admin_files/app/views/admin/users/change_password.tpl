<div id="content">
  <h1>_{Change Password of} {user_code}</h1>
  <?php  echo $active_record_helper->error_messages_for('user');?>  
  <?php  echo  $form_tag_helper->start_form_tag(array('action'=>'change_password', 'id' => $id)) ?>
  <div class="form">
      <table>
        <tr>
           <td><label for="user_password">_{Password}</label></td>
           <td><?php echo $form_helper->password_field("user", "password"); ?></td>
        </tr>
        <tr>
           <td><label for="user_password">_{Confirm Password}</label></td>
           <td><?php echo $form_helper->password_field("user", "password_confirmation"); ?></td>
        </tr>
      </table>
  </div>

  <div id="operations">
    <?php  echo $user_helper->save() ?> <?php  echo  $user_helper->cancel()?>
  </div>

  <?php  echo  $form_tag_helper->end_form_tag() ?>
</div>
