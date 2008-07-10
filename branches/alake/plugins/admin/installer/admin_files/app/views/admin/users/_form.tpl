<%= error_messages_for 'User' %>

{?need_app_owner}
{else}
<input id="user_app_owner" name="user[app_owner]" value='true' type="hidden" />
{end}
<fieldset><p>
    <label class="required" for="user_login">_{Login}</label>
    <%= input 'user', 'login', :tabindex => '2' %>
</p><p>
    <label class="required" for="user_email">_{Email}</label>
    <%= input 'user', 'email', :tabindex => '3' %>
</p><p>
    <label {!User.id}class="required"{end} for="user_password">_{Password}</label>
    <input id="user_password" name="user[password]" size="30" tabindex="4" type="password" /> {?User.id}<br /><span class="information">_{leave empty in order to keep previous password}</span>{end}
</p><p>
    <label {!User.id}class="required"{end} for="user_password_confirmation">_{Password confirmation}</label>
    <input id="user_password_confirmation" name="user[password_confirmation]" size="30" tabindex="5" type="password" />
</p><p>
    <label {!User.id}class="required"{end} for="user_name_last">_{Name  Last, First}</label>
    <%= input 'user', 'name_last', :size => "15", :tabindex => "6" %>
    <%= input 'user', 'name_first', :size => "15", :tabindex => "7" %>
</p><p>
    <label {!User.id}class="required"{end} for="user_address_1">_{Address}</label>
    <%= input 'user', 'address_1', :size => "15", :tabindex => "8" %>
    <%= input 'user', 'address_2', :size => "15", :tabindex => "9" %>
</p><p>
    <label {!User.id}class="required"{end} for="user_postal_code">_{Postal code}</label>
    <%= input 'user', 'postal_code', :size => "10", :tabindex => "10" %>
</p><p>
    <label {!User.id}class="required"{end} for="user_city">_{City}</label>
    <%= input 'user', 'city', :size => "15", :tabindex => "11" %>
</p><p>
    <label for="user_state">_{State or Province}</label>
    <%= input 'user', 'state', :size => "15", :tabindex => "12" %>
</p><p>
    <label {!User.id}class="required"{end} for="user_country_code">_{Country}</label>
    <?php echo $form_options_helper->country_select('user','country_code', $GLOBALS['priority_countries'])?>
</p><p>
    <label {!User.id}class="required"{end} for="user_lang">_{Language}</label>
    <?= $form_options_helper->select ('user','lang', $languages); ?>
</p><p>
    <label for="user_telephone">_{Telephone}</label>
    <%= input 'user', 'telephone', :size => "15", :tabindex => "15" %>
</p><p>
<span class="information">_{The correct answers to the three required security questions will enable you to log in to the system in case you forget your password.  The questions should be those for which you easily know the answer, but others are extremely unlikely to know.<br />Here are some example questions:}
        <ul>
            <li>&nbsp;&nbsp;_{What is your maternal grandmother's maiden name?}</li>
            <li>&nbsp;&nbsp;_{What was your first pet's name?}</li>
            <li>&nbsp;&nbsp;_{What was the name of your first romantic interest?}</li>
        </ul>
<br />When you log in this way, you will want to edit your account and change your password to something you can remember.</span>
    <table>
      <tr>
        <th><label {!User.id}class="required"{end}>_{Security}</label></th>
        <th>_{Questions}</th>
        <th>_{Answers}</th>
      </tr>
      <tr>
        <td></td>
        <td><%= input 'user', 'security_question_1', :size => "30", :tabindex => "16" %></td>
        <td><input id="user_security_answer_1" name="user[security_answer_1]" size="10" tabindex="17" type="password" /></td>
      </tr>
      <tr>
        <td></td>
        <td><%= input 'user', 'security_question_2', :size => "30", :tabindex => "18" %></td>
        <td><input id="user_security_answer_2" name="user[security_answer_2]" size="10" tabindex="19" type="password" /></td>
      </tr>
      <tr>
        <td></td>
        <td><%= input 'user', 'security_question_3', :size => "30", :tabindex => "20" %></td>
        <td><input id="user_security_answer_3" name="user[security_answer_3]" size="10" tabindex="21" type="password" /></td>
      </tr>
    </table>
</p>
</fieldset>
{?need_app_owner}
    <? if($admin_helper->can('Set roles', 'Admin::Users')) : ?>
        {?Roles}
            <fieldset>
                <legend class="required">_{Please select at least one Role}:</legend>

                <ul>
                {loop Roles}
                        <? $is_checked = (!empty($params['roles'][$Role->getId()])) ? true : in_array($Role->id, $User->collect($User->roles, 'id','id')); ?>
                    <li>
                        <input type="hidden" value="0" name="roles[{Role.id}]"/>
                        <input tabindex="4" type="checkbox" id="roles_id-{Role.id}" name="roles[{Role.id}]" {?is_checked}checked="checked"{end} />
                        <label for="roles_id-{Role.id}">
                            {Role.name}
                            {?Role.description}– <span class="information">{Role.description}</span>{end}
                        </label>
                    </li>
                {end}
                </ul>
            </fieldset>
        {end}
    <? endif; ?>
{end}
