{crmScope extensionKey='org.civicoop.configitems'}
<div class="crm-block crm-form-block crm-config_item_edit_extension-block">
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <div class="crm-section">
    <div class="label">{$form.custom_group.label}</div>
    <div class="content">{$form.custom_group.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.custom_group_select.label}</div>
    <div class="content">{$form.custom_group_select.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section onlyWhenInclude">
    <div class="label">{$form.propose_remove.label}</div>
    <div class="content">
        {$form.propose_remove.html}
      <p class="description">{ts}If an custom field exists on the target system but it is not in the export file. Propose to remove it from the target system?{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section onlyWhenInclude">
    <div class="label">{$form.select_all_fields.label}</div>
    <div class="content">
        {$form.select_all_fields.html}
        <p class="description">{ts}When set to no you are able to select certain fields. When set to yes all fields will be added.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

  {foreach from=$custom_fields item=custom_field_elements key=custom_group_name}
    {foreach from=$custom_field_elements item=element}
      <div class="crm-section hiddenElement customFields custom-group-value-{$custom_group_name}">
        <div class="label">{$form.$element.label}</div>
        <div class="content">{$form.$element.html}</div>
        <div class="clear"></div>
      </div>
    {foreachelse}
      <div class="crm-section hiddenElement customFields custom-group-value-{$custom_group_name}">
        <div class="label"></div>
        <div class="content">{ts}No custom fields found{/ts}</div>
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
      function showHideCustomFields() {
        var selectValue = $('input[name=custom_group_select]:checked').val();
        var isGroupIncluded = false;
        if (selectValue == 'include') {
          isGroupIncluded = true;
          $('.crm-section.onlyWhenInclude').removeClass('hiddenElement');
        } else {
          $('.crm-section.onlyWhenInclude').addClass('hiddenElement');
        }
        $('.crm-section.customFields').addClass('hiddenElement');
        if (isGroupIncluded && $('input[name=select_all_fields]:checked').val() == '0') {
          var customGroupName = $('#custom_group').val();
          $('.crm-section.customFields.custom-group-value-'+customGroupName).removeClass('hiddenElement');
        }
      }

      $('input[name=select_all_fields]').on('change', showHideCustomFields);
      $('input[name=custom_group_select]').on('change', showHideCustomFields);
      $('#custom_group').on('change', showHideCustomFields);

      showHideCustomFields();
    });
  </script>
{/literal}
{/crmScope}
