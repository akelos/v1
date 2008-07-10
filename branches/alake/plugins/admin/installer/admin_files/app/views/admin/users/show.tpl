<div id="content_menu">
    <ul class="menu">
        <li><%= link_to _('Create new User'), :controller => 'users', :action => 'add' %></li>
        <li class="primary"><%= link_to _('Edit this User'), :controller => 'users', :action => 'edit', :id => user.id %></li>
        <li class="active"><%= link_to _('Showing User profile'), :controller => 'users', :action => 'show', :id => user.id %></li>
        <li><%= link_to _('Delete this User'), :controller => 'users', :action => 'destroy', :id => user.id %></li>        <li><%= link_to _('Show available Users'), :controller => 'users', :action => 'listing' %></li>
    </ul>
    <p class="information">_{The User management area allows you to create and edit user accounts.}</p>
</div>


<div class="content">
    <dl>
        <dt>_{Login}:</dt><dd>{User.login}</dd>
        <dt>_{Email}:</dt><dd><%= mail_to User.email %></dd>
        <dt>_{Status}:</dt><dd>{?User.is_enabled}_{active}{else}_{blocked}{end}</dd>
        <dt>_{Last access}:</dt><dd>{?User.last_login_at}<%= _("#{time_ago_in_words(User.last_login_at)} ago") %> – <span class="information"><%= locale_date_time User.last_login_at %></span>{else}_{never}{end}</dd>
        <dt>_{Member for}:</dt><dd><%= time_ago_in_words User.created_at %> – <span class="information"><%= locale_date_time User.created_at %></span></dd>
        <dt>_{Name}:</dt><dd><%= User.name_first %>  <%= User.name_last %></dd>
        {?User.roles}
            <dt>_{Assigned Roles}:</dt>
            <dd>
                {loop User.roles}
                    {role.name}{?role.description} – <span class="information">{role.description}</span>{end}{!role_is_last}, {end}
                {end}
            </dd>
        {end}
        <dt>_{Address}:</dt><dd><%= User.address_1 %>  <%= User.address_2 %></dd>
        <dt>_{Postal Code}:</dt><dd>{User.postal_code}</dd>
        <dt>_{City}:</dt><dd>{User.city}</dd>
        <?php if(User.state) { ?>
            <dt>_{State or Province}:</dt><dd>{User.state}</dd>
        {end}
        <dt>_{Country}:</dt><dd>{User.country_code}  (<?php echo $country_name ?>)</dd>
        <dt>_{Language}:</dt><dd><?php echo $lang_name ?></dd>
        <dt>_{Telephone}:</dt><dd>{User.telephone}</dd>
        <dt>_{Security Questions}:</dt><dd>{User.security_question_1}</dd>
        <dt></dt><dd>{User.security_question_2}</dd>
        <dt></dt><dd>{User.security_question_3}</dd>
    </dl>
<p class="operations"><%= link_to_edit User %> _{or} <%= link_to_destroy User %> _{User} </p>
</div>

