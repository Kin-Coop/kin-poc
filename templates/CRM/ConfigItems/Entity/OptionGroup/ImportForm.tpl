{crmScope extensionKey='org.civicoop.configitems'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{if (count($elements.include))}
  {foreach from=$elements.include item=element key=elementName}
    <h3>{ts 1=$element.name}Included Option Group %1{/ts}</h3>
    <div class="crm-section">
      <div class="label"></div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
    <div id="option_group_{$elementName}" class="crm-section">
      {foreach from=$element.subelements item=subelementName}
        <div class="crm-section">
          <div class="label">{$form.$subelementName.label}</div>
          <div class="content">{$form.$subelementName.html}</div>
          <div class="clear"></div>
        </div>
      {/foreach}
    </div>
  {/foreach}
{/if}
{if (count($elements.remove))}
  {foreach from=$elements.remove item=element key=elementName}
    <h3>{ts 1=$element.name}Remove Option Group %1{/ts}</h3>
    <div class="crm-section">
      <div class="label"></div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}
{/if}

{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      function showHideOptionValues(e) {
        var optionGroupName = $(this).attr('name');
        var value = $("input[name='"+optionGroupName+"']:checked").val();
        if (value > 0) {
          $('#option_group_'+optionGroupName).removeClass('hiddenElement');
        } else {
          $('#option_group_'+optionGroupName).addClass('hiddenElement');
        }
      }
      $('.included_option_group').on('change', showHideOptionValues);
      $('.included_option_group').trigger('change');
    });
  </script>
{/literal}

{/crmScope}
