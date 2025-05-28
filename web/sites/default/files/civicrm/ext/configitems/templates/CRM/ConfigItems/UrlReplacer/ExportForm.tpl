{crmScope extensionKey='org.civicoop.configitems'}
{if ($containsResourceURL)}
  <div class="crm-section">
    <div class="label">{$form.resource_url_replacement.label}</div>
    <div class="content">{$form.resource_url_replacement.html}</div>
    <div class="clear"></div>
  </div>
{/if}

<div class="crm-section">
  <p><a href="{crmURL p='civicrm/admin/civiconfig/edit/urlreplacement' q="action=add&reset=1&id=`$id`&decorator=`$decoratorName`"}" title="{ts}Add another URL replacement{/ts}" class="button"><i class="crm-i fa-plus" aria-hidden="true"></i> {ts}Add URL Replacement{/ts}</a></p>
  <p><a href="{crmURL p='civicrm/admin/civiconfig/edit/imagereplacement' q="action=add&reset=1&id=`$id`&decorator=`$decoratorName`"}" title="{ts}Add another Image replacement{/ts}" class="button"><i class="crm-i fa-plus" aria-hidden="true"></i> {ts}Add Image Replacement{/ts}</a></p>
  <div class="clear"></div>
</div>

{if count($selected_urls) || count($selected_images)}
  <table>
    <tr>
      <th>{ts}URL{/ts}</th>
      <th>{ts}Replacement{/ts}</th>
      <th></th>
    </tr>

  {foreach from=$selected_urls key=urlKey item=url}
      <tr>
        <td>{$url.label}</td>
        <td>{$url.config_label}</td>
        <td>
          <a href="{crmURL p='civicrm/admin/civiconfig/edit/urlreplacement' q="action=update&reset=1&id=`$id`&decorator=`$decoratorName`&urlKey=`$urlKey`"}" title="{ts}Edit URL replacement{/ts}"><i class="crm-i fa-pencil" aria-hidden="true"></i> {ts}Edit{/ts}</a>
          <a href="{crmURL p='civicrm/admin/civiconfig/edit/urlreplacement' q="action=delete&reset=1&id=`$id`&decorator=`$decoratorName`&urlKey=`$urlKey`"}" title="{ts}Delete URL replacement{/ts}"><i class="crm-i fa-trash" aria-hidden="true"></i> {ts}Delete{/ts}</a>
        </td>
      </tr>
  {/foreach}
  {foreach from=$selected_images key=urlKey item=url}
    <tr>
      <td>{$url.label}</td>
      <td>{$url.config_label}</td>
      <td>
        <a href="{crmURL p='civicrm/admin/civiconfig/edit/imagereplacement' q="action=update&reset=1&id=`$id`&decorator=`$decoratorName`&urlKey=`$urlKey`"}" title="{ts}Edit Image replacement{/ts}"><i class="crm-i fa-pencil" aria-hidden="true"></i> {ts}Edit{/ts}</a>
        <a href="{crmURL p='civicrm/admin/civiconfig/edit/imagereplacement' q="action=delete&reset=1&id=`$id`&decorator=`$decoratorName`&urlKey=`$urlKey`"}" title="{ts}Delete Image replacement{/ts}"><i class="crm-i fa-trash" aria-hidden="true"></i> {ts}Delete{/ts}</a>
      </td>
    </tr>
  {/foreach}
  </table>
{/if}

{if count($selected_import_urls) || count($selected_import_images)}
  <h3>{ts}Links in import file{/ts}</h3>
  <table>
    <tr>
      <th>{ts}URL{/ts}</th>
      <th>{ts}Replacement{/ts}</th>
      <th></th>
    </tr>

    {foreach from=$selected_import_urls key=urlKey item=url}
      <tr>
        <td>{$url.label}</td>
        <td>{$url.config_label}</td>
        <td>
          <a href="{crmURL p='civicrm/admin/civiconfig/edit/urlreplacement' q="action=delete&reset=1&id=`$id`&decorator=`$decoratorName`&urlKey=`$urlKey`"}" title="{ts}Delete URL replacement{/ts}"><i class="crm-i fa-trash" aria-hidden="true"></i> {ts}Delete{/ts}</a>
        </td>
      </tr>
    {/foreach}
    {foreach from=$selected_import_images key=urlKey item=url}
      <tr>
        <td>{$url.label}</td>
        <td>{$url.config_label}</td>
        <td>
          <a href="{crmURL p='civicrm/admin/civiconfig/edit/imagereplacement' q="action=delete&reset=1&id=`$id`&decorator=`$decoratorName`&urlKey=`$urlKey`"}" title="{ts}Delete Image replacement{/ts}"><i class="crm-i fa-trash" aria-hidden="true"></i> {ts}Delete{/ts}</a>
        </td>
      </tr>
    {/foreach}
  </table>
{/if}
{/crmScope}
