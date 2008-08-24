<h2>_{Sign up for an account}</h2>

<%= start_form_tag {:action =>'sign_up'}, :id => 'user_form' %>
    <div class="form">

<%= error_messages_for 'User' %>

<%= javascript_tag "var LOGIN_CHECK_URL = '#{url_for :action => 'is_login_available'}';" %>

<fieldset>
    <label class="required" for="user_login">_{User name}</label>
    <%= input 'user', 'login', :tabindex => '2' %>
    <div id="login_check" style="display:none;">
        <p class="warning">_{User name already in use}</p>
    </div>
</fieldset>

<fieldset>
    <label class="required" for="user_email">_{Email}</label>
    <%= input 'user', 'email', :tabindex => '3' %>
</fieldset>


<fieldset>
    <p>
        <label class="required" for="user_password">_{Password}</label>
        <input id="user_password" name="user[password]" size="30" tabindex="5" type="password" />
    </p>
    <p>
        <label class="required" for="user_password_confirmation">_{Password confirmation}</label>
        <input id="user_password_confirmation" name="user[password_confirmation]" size="30" tabindex="6" type="password" />
    </p>
</fieldset>

  </div>

  <div id="operations">
    <input type="submit" value="_{Sign up}" class="primary" />
  </div>
</form>
