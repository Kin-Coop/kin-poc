{crmScope extensionKey='org.civicoop.configitems'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{if (count($elements.include))}
  {foreach from=$elements.include item=element key=elementName}
    <h3>{ts 1=$element.name}Included Custom Group %1{/ts}</h3>
    <div class="crm-section">
      <div class="label">{ts 1=$element.name}Custom Group %1{/ts}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
    <div id="custom_group_{$elementName}" class="crm-section">
      {foreach from=$element.subelements item=subelementName}
        <div class="crm-section">
          <div class="label">{ts}Custom field:{/ts}&nbsp;{$form.$subelementName.label}</div>
          <div class="content">{$form.$subelementName.html}</div>
          <div class="clear"></div>
        </div>
      {/foreach}
    </div>
  {/foreach}
{/if}
{if (count($elements.remove))}
  {foreach from=$elements.remove item=element key=elementName}
    <h3>{ts 1=$element.name}Remove Custom Group %1{/ts}</h3>
    <div class="crm-section">
      <div class="label">{ts 1=$element.name}Custom Group %1{/ts}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}
{/if}

{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      function showHideCustomFields(e) {
        var customGroupName = $(this).attr('name');
        var value = $("input[name='"+customGroupName+"']:checked").val();
        if (value > 0) {
          $('#custom_group_'+customGroupName).removeClass('hiddenElement');
        } else {
          $('#custom_group_'+customGroupName).addClass('hiddenElement');
        }
      }
      $('.included_custom_group').on('change', showHideCustomFields);
      $('.included_custom_group').trigger('change');
    });
  </script>
{/literal}

{/crmScope}
