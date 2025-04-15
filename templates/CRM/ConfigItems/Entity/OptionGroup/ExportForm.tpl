{crmScope extensionKey='org.civicoop.configitems'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{if isset($configuration.include) && count($configuration.include)}
    <h3>{ts}Included option groups{/ts}</h3>
    <table>
      <thead>
        <tr>
          <th>{ts}Option Group{/ts}</th>
          <th>{ts}Propose to remove{/ts}</th>
          <th>{ts}Selected values{/ts}</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      {foreach from=$configuration.include key=option_group_name item=item}
        {assign var=option_group value=$option_groups.$option_group_name}
        <tr>
          <td>{$option_group.title}</td>
          <td>{if ($item.propose_remove)}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
          <td>
            {if ($item.select_all_values)}
              {ts}Export all values{/ts}
            {elseif (count($item.include) || count($item.remove))}
              <p>{ts}Include:{/ts}
              {foreach from=$item.include item=option_value_name name=option_values}
                {assign var=optionValueLabel value=$option_value_name}
                {if ($option_values_by_group.$option_group_name.$option_value_name)}
                  {assign var=optionValueLabel value=$option_values_by_group.$option_group_name.$option_value_name.label}
                {/if}
                {$optionValueLabel}{if (!$smarty.foreach.option_values.last)}, {/if}
              {/foreach}
              </p>
              <p>{ts}Mark as removed:{/ts}
              {foreach from=$item.remove item=option_value_name name=option_values}
                {assign var=optionValueLabel value=$option_value_name}
                {if ($option_values_by_group.$option_group_name.$option_value_name)}
                    {assign var=optionValueLabel value=$option_values_by_group.$option_group_name.$option_value_name.label}
                {/if}
                {$optionValueLabel}{if (!$smarty.foreach.option_values.last)}, {/if}
              {/foreach}
              </p>
            {else}
              {ts}No values selected{/ts}
            {/if}
          </td>
          <td>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/optiongroup' q="action=edit&reset=1&id=`$id`&entity=`$entityName`&option_group_name=`$option_group_name`"}">{ts}Edit{/ts}</a>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/optiongroup' q="action=delete&reset=1&id=`$id`&entity=`$entityName`&option_group_name=`$option_group_name`"}">{ts}Delete{/ts}</a>
          </td>
        </tr>
      {/foreach}
    </table>
{/if}
{if isset($configuration.remove) && count($configuration.remove)}
  <h3>{ts}Option groups marked as removed{/ts}</h3>
  <table>
    <thead>
    <tr>
      <th>{ts}Option Group{/ts}</th>
      <th>&nbsp;</th>
    </tr>
    </thead>
      {foreach from=$configuration.remove item=option_group_name}
        {assign var=option_group value=$option_groups.$option_group_name}
        <tr>
          <td>{$option_group.title}</td>
          <td>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/optiongroup' q="action=edit&reset=1&id=`$id`&entity=`$entityName`&option_group_name=`$option_group_name`"}">{ts}Edit{/ts}</a>
            <a href="{crmURL p='civicrm/admin/civiconfig/edit/optiongroup' q="action=delete&reset=1&id=`$id`&entity=`$entityName`&option_group_name=`$option_group_name`"}">{ts}Delete{/ts}</a>
          </td>
        </tr>
      {/foreach}
  </table>
{/if}
{if (!isset($configuration.include) || !count($configuration.include)) && (!isset($configuration.remove) || !count($configuration.remove))}
    <p>{ts}No option groups selected{/ts}</p>
{/if}

<div class="crm-section">
  <p><a href="{crmURL p='civicrm/admin/civiconfig/edit/optiongroup' q="action=add&reset=1&id=`$id`&entity=`$entityName`"}" title="{ts}Add option group{/ts}" class="button"><i class="crm-i fa-plus" aria-hidden="true"></i> {ts}Add option group{/ts}</a></p>
  <div class="clear"></div>
</div>
{/crmScope}
