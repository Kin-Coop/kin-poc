{crmScope extensionKey='org.civicoop.configitems'}
<div class="crm-block crm-form-block crm-config_item_edit_extension-block">
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <div class="crm-section">
    <div class="label">{$form.option_group.label}</div>
    <div class="content">{$form.option_group.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.option_group_select.label}</div>
    <div class="content">{$form.option_group_select.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section onlyWhenInclude">
    <div class="label">{$form.propose_remove.label}</div>
    <div class="content">
        {$form.propose_remove.html}
      <p class="description">{ts}If an option value exists on the target system but it is not in the export file. Propose to remove it from the target system?{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section onlyWhenInclude">
    <div class="label">{$form.select_all_values.label}</div>
    <div class="content">
        {$form.select_all_values.html}
        <p class="description">{ts}When set to no you are able to select certain values. When set to yes all values will be added.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

  {foreach from=$option_values item=option_value_elements key=option_group_name}
    {foreach from=$option_value_elements item=element}
      <div class="crm-section hiddenElement optionValues option-group-value-{$option_group_name}">
        <div class="label">{$form.$element.label}</div>
        <div class="content">{$form.$element.html}</div>
        <div class="clear"></div>
      </div>
    {foreachelse}
      <div class="crm-section hiddenElement optionValues option-group-value-{$option_group_name}">
        <div class="label"></div>
        <div class="content">{ts}No option values found{/ts}</div>
        <div class="clear"></div>
      </div>
    {/foreach}
  {/foreach}


  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      function showHideOptionValues() {
        var selectValue = $('input[name=option_group_select]:checked').val();
        var isGroupIncluded = false;
        if (selectValue == 'include') {
          isGroupIncluded = true;
          $('.crm-section.onlyWhenInclude').removeClass('hiddenElement');
        } else {
          $('.crm-section.onlyWhenInclude').addClass('hiddenElement');
        }
        $('.crm-section.optionValues').addClass('hiddenElement');
        if (isGroupIncluded && $('input[name=select_all_values]:checked').val() == '0') {
          var optionGroupName = $('#option_group').val();
          $('.crm-section.optionValues.option-group-value-'+optionGroupName).removeClass('hiddenElement');
        }
      }

      $('input[name=select_all_values]').on('change', showHideOptionValues);
      $('input[name=option_group_select]').on('change', showHideOptionValues);
      $('#option_group').on('change', showHideOptionValues);

      showHideOptionValues();
    });
  </script>
{/literal}
{/crmScope}
