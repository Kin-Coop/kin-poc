{crmScope extensionKey='org.civicoop.configitems'}
{assign var=replace_element value='replace_url_'|cat:$urlKey}
<div class="crm-section" id="{$urlKey|cat:'_replace_container'}">
  <div class="label">{$form.$replace_element.label} <span class="crm-marker" title="{ts}This field is required.{/ts}">*</span></div>
  <div class="content">{$form.$replace_element.html}</div>
  <div class="clear"></div>
</div>
{literal}
<script type="text/javascript">
  CRM.$(function($) {
    var urlKey = '{/literal}{$urlKey}{literal}';
    $('#'+urlKey).on('change', function() {
      var selectedValue = $('#'+urlKey+' option:selected').val();
      if (selectedValue == 'replace_with') {
        $('#'+urlKey+'_replace_container').removeClass('hiddenElement');
      } else {
        $('#'+urlKey+'_replace_container').addClass('hiddenElement');
      }
    });
    $('#'+urlKey).trigger('change');
  });
</script>
{/literal}
{/crmScope}
