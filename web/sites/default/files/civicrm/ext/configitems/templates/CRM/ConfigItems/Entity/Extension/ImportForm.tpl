{crmScope extensionKey='org.civicoop.configitems'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{if count($extensions)}
    <table>
      <thead>
        <tr>
          <th>{ts}Name{/ts}</th>
          <th>{ts}Source{/ts}</th>
          <th>{ts}Installed Version{/ts}</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      {foreach from=$extensions item=extension}
        {assign var=source value=$extension.download_source}
        {assign var=key value=$extension.key}
        {assign var=name value=$elementNames.$key}
        <tr>
          <td>{$extension.key}</td>
          <td>
            {if $extension.url}<a title="{$extension.url}">{/if}{$downloadSourceOptions.$source}{if $extension.url}</a>{/if}
            {if $extension.branch}<br />{ts 1=$extension.branch}Branch/Tag: %1{/ts}{/if}
          </td>
          <td>
              {if $extension.current_version}{$extension.current_version}{/if}
              {if $extension.current_status}({$extension.current_status}){/if}
          </td>
          <td>{$form.$name.html}</td>
        </tr>
      {/foreach}
    </table>
{else}
    <p>{ts}No extensions{/ts}</p>
{/if}

{/crmScope}
