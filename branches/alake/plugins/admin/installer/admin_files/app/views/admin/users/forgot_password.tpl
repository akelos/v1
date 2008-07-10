<div id="content">
  <?php  echo $active_record_helper->error_messages_for('user');?>  
  <?php  echo  $form_tag_helper->start_form_tag(array('action'=>'forgot_password', 'id' => $id)) ?>
  <div class="form">
      <table>
          <tr>
              <td colspan="3"><h1>_{Security questions for}<br />{name_first} {name_last}</h1></td>
          </tr>
          <tr>
             <td><label for="user_security_question_1">{question_1}</label></td>
             <td><?php  echo $form_helper->password_field('user', 'security_answer_1')?></td>
          </tr>
          <tr>
             <td><label for="user_security_question_2">{question_2}</label></td>
             <td><?php  echo $form_helper->password_field('user', 'security_answer_2')?></td>
          </tr>
          <tr>
             <td><label for="user_security_question_3">{question_3}</label></td>
             <td><?php  echo $form_helper->password_field('user', 'security_answer_3')?></td>
          </tr>
      </table>
  </div>

  <div id="operations">
    <?php  echo $user_helper->save() ?> <?php  echo  $user_helper->cancel()?>
  </div>

  <?php  echo  $form_tag_helper->end_form_tag() ?>
</div>
