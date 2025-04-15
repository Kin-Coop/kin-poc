{crmScope extensionKey='org.civicoop.configitems'}
{assign var=replace_element value='replace_url_'|cat:$urlKey}
{assign var=imgSrc value='image_'|cat:$urlKey}
{if $url}
  <div class="crm-section">
    <div class="label">&nbsp;</div>
    <div class="content">
      <img src="{$url->getImageUrl()}" style="max-height: 100px;" />
    </div>
    <div class="clear"></div>
  </div>
{/if}
<div class="crm-section" id="{$urlKey|cat:'_replace_container'}">
  <div class="label">{$form.$replace_element.label}</div>
  <div class="content">{$form.$replace_element.html}</div>
  <div class="clear"></div>
</div>
{/crmScope}
