<p id='component'><%= link_to AkelosClass.component.name, :controller => 'component', :action => 'show', :id => AkelosClass.component.id %></p>
<h1>{AkelosClass.name?}</h1>

<p>{AkelosClass.file.path?}</p>

{?AkelosClass.description}
 <div id="akelos_class-description-{AkelosClass.id}" class="editable"><%= markdown AkelosClass.description %></div>
 <%= javascript_tag "new Ajax.InPlaceEditor('akelos_class-description-#{AkelosClass.id}', '#{url_for(:controller=>'class',:action=>'edit',:id=>AkelosClass.id)}', {okText:'#{_'Save'}', cancelText:'#{_'cancel'}', savingText:'#{_'Saving…'}' , clickToEditText:'#{_'Click to edit'}', rows:20, cols:80 });" %>
{end}


{?AkelosClass.methods}
    {loop AkelosClass.methods}
        {!method.is_private}
        <a name="{method.name}"></a>
        <h2>{method.name}</h2>
        <div id="method-description-{method.id}" class="editable">{?method.description}<%= markdown method.description %>{else}_{click to document this method}{end}</div>
        <%= javascript_tag "new Ajax.InPlaceEditor('method-description-#{method.id}', '#{url_for(:controller=>'method',:action=>'edit',:id=>method.id)}', {okText:'#{_'Save'}', cancelText:'#{_'cancel'}', savingText:'#{_'Saving…'}' , clickToEditText:'#{_'Click to edit'}', rows:20, cols:80 });" %>
        
        {end}
    {end}
{end}