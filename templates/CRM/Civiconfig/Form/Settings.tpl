{crmScope extensionKey='org.civicoop.configitems'}
<div class="crm-block crm-form-block crm-config_item_set_settings-block">
  <div class="crm-section">
    <div class="label">{$form.overrride_git_command.label}</div>
    <div class="content">{$form.overrride_git_command.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section git_command hiddenElement">
    <div class="label">{$form.git_command.label}</div>
    <div class="content">{$form.git_command.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.overrride_composer_command.label}</div>
    <div class="content">{$form.overrride_composer_command.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section composer_command hiddenElement">
    <div class="label">{$form.composer_command.label}</div>
    <div class="content">{$form.composer_command.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

<script type="text/javascript">
{literal}
CRM.$(function($) {
  $('#overrride_git_command_1').change(function() {
    var isChecked = $('#overrride_git_command_1').prop('checked');
    if (isChecked) {
      $('.crm-section.git_command').removeClass('hiddenElement');
    } else {
      $('.crm-section.git_command').addClass('hiddenElement');
    }
  });

  $('#overrride_composer_command_1').change(function() {
    var isChecked = $('#overrride_composer_command_1').prop('checked');
    if (isChecked) {
      $('.crm-section.composer_command').removeClass('hiddenElement');
    } else {
      $('.crm-section.composer_command').addClass('hiddenElement');
    }
  });

  $('#overrride_git_command_1').trigger('change');
  $('#overrride_composer_command_1').trigger('change');
});
{/literal}
</script>

{/crmScope}
