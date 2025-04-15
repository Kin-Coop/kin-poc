{crmScope extensionKey='org.civicoop.configitems'}


  <div class="crm-block crm-form-block crm-config_item_set_title-block">
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
    <div class="crm-section">
    <div class="crm-section">
      <div class="label">{$form.file_format.label}</div>
      <div class="content">{$form.file_format.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.file.label}</div>
      <div class="content">{$form.file.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.overwrite.label}</div>
      <div class="content">{$form.overwrite.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>

{/crmScope}
