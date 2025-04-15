{crmScope extensionKey='org.civicoop.configitems'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{if isset($configuration.include) && count($configuration.include)}
    <h3>{ts}Included custom groups{/ts}</h3>
    <table>
      <thead>
        <tr>
          <th>{ts}Custom Group{/ts}</th>
          <th>{ts}Propose to remove{/ts}</th>
          <th>{ts}Selected values{/ts}</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      {foreach from=$configuration.include key=custom_group_name item=item}
        {assign var=custom_group value=$customgroups.$custom_group_name}
        <tr>
          <td>{$custom_group.title}</td>
          <td>{if ($item.propose_remove)}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
          <td>
            {if ($item.select_all_values)}
              {ts}Export all values{/ts}
            {elseif (count($item.include) || count($item.remove))}
              <p>{ts}Include:{/ts}
              {foreach from=$item.include item=custom_field_name name=customfields}
                {assign var=customFieldLabel value=$custom_field_name}
                {if ($customfields_by_group.$custom_group_name.$custom_field_name)}
                  {assign var=customFieldLabel value=$customfields_by_group.$custom_group_name.$custom_field_name.label}
                {/if}
                {$customFieldLabel}{if (!$smarty.foreach.customfields.last)}, {/if}
              {/foreach}
              </p>
              <p>{ts}Mark as removed:{/ts}
              {foreach from=$item.remove item=custom_field_name name=customfields}
                {assign var=customFieldLabel value=$custom_field_name}
                {if ($customfields_by_group.$custom_group_name.$custom_field_name)}
                    {assign var=customFieldLabel value=$customfields_by_group.$custom_group_name.$custom_field_name.label}
                {/if}
                {$customFieldLabel}{if (!$smarty.foreach.customfields.last)}, {/if}
              {/foreach}
              </p>
            {else}
              {ts}No values selected{/ts}
            {/if}
          </td>
          <td>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/customgroup' q="action=edit&reset=1&id=`$id`&entity=`$entityName`&custom_group_name=`$custom_group_name`"}">{ts}Edit{/ts}</a>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/customgroup' q="action=delete&reset=1&id=`$id`&entity=`$entityName`&custom_group_name=`$custom_group_name`"}">{ts}Delete{/ts}</a>
          </td>
        </tr>
      {/foreach}
    </table>
{/if}
{if isset($configuration.remove) && count($configuration.remove)}
  <h3>{ts}Custom groups marked as removed{/ts}</h3>
  <table>
    <thead>
    <tr>
      <th>{ts}Custom Group{/ts}</th>
      <th>&nbsp;</th>
    </tr>
    </thead>
      {foreach from=$configuration.remove item=custom_group_name}
          {assign var=custom_group value=$customgroups.$custom_group_name}
        <tr>
          <td>{$custom_group.title}</td>
          <td>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/customgroup' q="action=edit&reset=1&id=`$id`&entity=`$entityName`&custom_group_name=`$custom_group_name`"}">{ts}Edit{/ts}</a>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/customgroup' q="action=delete&reset=1&id=`$id`&entity=`$entityName`&custom_group_name=`$custom_group_name`"}">{ts}Delete{/ts}</a>
          </td>
        </tr>
      {/foreach}
  </table>
{/if}
{if (!isset($configuration.include) || !count($configuration.include)) && (!isset($configuration.remove) || !count($configuration.remove))}
    <p>{ts}No custom groups selected{/ts}</p>
{/if}

<div class="crm-section">
  <p><a href="{crmURL p='civicrm/admin/civiconfig/edit/customgroup' q="action=add&reset=1&id=`$id`&entity=`$entityName`"}" title="{ts}Add custom group{/ts}" class="button"><i class="crm-i fa-plus" aria-hidden="true"></i> {ts}Add custom group{/ts}</a></p>
  <div class="clear"></div>
</div>
{/crmScope}
