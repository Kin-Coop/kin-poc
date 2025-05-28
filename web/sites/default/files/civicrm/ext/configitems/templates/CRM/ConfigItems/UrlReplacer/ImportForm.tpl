{crmScope extensionKey='org.civicoop.configitems'}
{if ($askForResourceUrlReplacement)}
<div class="crm-section"}">
  <div class="label">{$form.resource_url_replacement_cms_root_url.label}</div>
  <div class="content">{$form.resource_url_replacement_cms_root_url.html}</div>
  <div class="clear"></div>
  <div class="label">{$form.resource_url_replacement_civicrm_files_url.label}</div>
  <div class="content">{$form.resource_url_replacement_civicrm_files_url.html}</div>
  <div class="clear"></div>
{/if}
{if count($urls)}
  <table>
  {foreach from=$urls item=url key=urlKey}
    <tr>
      <td>
        {$url->getLabel()}
      </td>
    </tr>
    <tr>
      <td>
        {if $url_templates.$urlKey}
          {include file=$url_templates.$urlKey urlKey=$urlKey}
        {/if}
      </td>
    </tr>
  {/foreach}
  </table>
{else}
  <div class="crm-event-info">{ts}There is no additional configuration required to replace links and images.{/ts}</div>
{/if}
{/crmScope}
