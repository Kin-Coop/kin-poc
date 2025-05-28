{crmScope extensionKey='org.civicoop.configitems'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{if count($configuration)}
    <table>
      <thead>
        <tr>
          <th>{ts}Name{/ts}</th>
          <th>{ts}Download Source{/ts}</th>
          <th>{ts}URL{/ts}</th>
          <th>{ts}Branch / Tag{/ts}</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      {foreach from=$configuration item=extension}
        {assign var=source value=$extension.download_source}
        {assign var=key value=$extension.key}
        <tr>
          <td>{$extension.key}</td>
          <td>{$downloadSourceOptions.$source}</td>
          <td>{$extension.url}</td>
          <td>{$extension.branch}</td>
          <td>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/extension' q="action=edit&reset=1&id=`$id`&entity=`$entityName`&extension=`$key`"}">{ts}Edit{/ts}</a>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/extension' q="action=delete&reset=1&id=`$id`&entity=`$entityName`&extension=`$key`"}">{ts}Delete{/ts}</a>
          </td>
        </tr>
      {/foreach}
    </table>
{else}
    <p>{ts}No extensions{/ts}</p>
{/if}

<div class="crm-section">
  <p><a href="{crmURL p='civicrm/admin/civiconfig/edit/extension' q="action=add&reset=1&id=`$id`&entity=`$entityName`"}" title="{ts}Add another extension{/ts}" class="button"><i class="crm-i fa-plus" aria-hidden="true"></i> {ts}Add extension{/ts}</a></p>
  <div class="clear"></div>
</div>
{/crmScope}
