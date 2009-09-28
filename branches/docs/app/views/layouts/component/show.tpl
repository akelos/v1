<p class="actions"><%= link_to _'All Components', :controller => 'component', :action => 'listing' %></p>

<? $ancestors = $Component->tree->getAncestors(); ?>
<div id="ancestors"><%= display_tree_recursive ancestors %></div>
<h1>{Component.name}</h1>

<p>{Component.description}</p>

<? $children = $Component->tree->getChildren(); ?>
{?children}
    <h2>_{Sub-components}</h2>
    <div id="children"><%= display_tree_recursive children, Component.id %></div>
{end}


{?Component.akelos_classes}
<h2>_{Classes}</h2>
    <ul>
    {loop Component.akelos_classes}
        <li><%= link_to akelos_class.name, :controller => 'class', :action => 'show', :name => akelos_class.name %></li>
    {end}
    </ul>
{end}