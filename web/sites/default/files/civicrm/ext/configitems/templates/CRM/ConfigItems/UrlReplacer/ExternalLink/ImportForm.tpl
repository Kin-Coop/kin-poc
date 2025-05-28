{crmScope extensionKey='org.civicoop.configitems'}
{assign var=replace_element value='replace_url_'|cat:$urlKey}
<div class="crm-section" id="{$urlKey|cat:'_replace_container'}">
  <div class="label">{$form.$replace_element.label}</div>
  <div class="content">{$form.$replace_element.html}</div>
  <div class="clear"></div>
</div>
{/crmScope}
