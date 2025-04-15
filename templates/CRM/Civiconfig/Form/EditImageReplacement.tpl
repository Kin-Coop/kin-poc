{crmScope extensionKey='org.civicoop.configitems'}
<div class="crm-block crm-form-block crm-config_item_edit_extension-block">
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <div class="crm-section">
    <div class="label">{$form.url.label}</div>
    <div class="content">{$form.url.html}</div>
    <div class="clear"></div>
  </div>

  {foreach from=$url_templates item=urlTemplateFile key=urlKey}
      <div class="hiddenElement" id="additional_url_config_{$urlKey}">
        {include file=$urlTemplateFile urlKey=$urlKey}
      </div>
  {/foreach}

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      var currentSelectedUrlKey;
      $('#url').on('change', function() {
        if (currentSelectedUrlKey) {
          $('#additional_url_config_' + currentSelectedUrlKey).addClass('hiddenElement');
        }
        currentSelectedUrlKey = $('#url option:selected').val();
        if (currentSelectedUrlKey) {
          $('#additional_url_config_' + currentSelectedUrlKey).removeClass('hiddenElement');
        }
      });
      $('#url').trigger('change');
    });
  </script>
{/literal}
{/crmScope}
