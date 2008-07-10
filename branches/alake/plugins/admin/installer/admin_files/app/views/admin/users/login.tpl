<div id="content_menu">
    <p class="information">_{Logging in will give you access to the User Management Area where you will be able to create and edit user accounts.}</p>
</div>

<div class="content">
  <h1>_{Log In}</h1>
<%= start_form_tag {:action =>'verify_login', :id => User.id}, :id => 'user_form' %>
  <div class="form">
      <fieldset>
          <p>
              <label for="login">_{User Code}</label><br />
              <?php  echo $active_record_helper->input('user', 'login')?>
          </p>

          <p>
              <label for="user_password">_{Password}</label><br />
              <?php echo $form_helper->password_field("user", "password"); ?>
          </p>
          <p>
          Leave the password field blank if you wish to log in by answering security questions.
          </p>
      </fieldset>
</div>

    <div id="operations">
        <%= save_button %> _{or} <%= cancel_link %>
<!--<%= link_to _('Register as a new user'), :controller => 'users', :action => 'add' %> -->
    </div>
</form>
</div>
