{crmScope extensionKey='org.civicoop.configitems'}
{assign var=replace_url_element value='replace_url_'|cat:$urlKey}
{assign var=replace_method_element value='replace_method_'|cat:$urlKey}
  <div class="crm-section">
    <div class="label">{$form.$replace_method_element.label} <span class="crm-marker" title="{ts}This field is required.{/ts}">*</span></div>
    <div class="content">{$form.$replace_method_element.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section" id="{$urlKey|cat:'_replace_container'}">
    <div class="label">{$form.$replace_url_element.label} <span class="crm-marker" title="{ts}This field is required.{/ts}">*</span></div>
    <div class="content">{$form.$replace_url_element.html}</div>
  <div class="clear"></div>
</div>
{literal}
<script type="text/javascript">
  CRM.$(function($) {
    var urlKey = '{/literal}{$urlKey}{literal}';
    $('#replace_method_'+urlKey).on('change', function() {
      var selectedValue = $('#replace_method_'+urlKey+' option:selected').val();
      if (selectedValue == 'replace_with') {
        $('#'+urlKey+'_replace_container').removeClass('hiddenElement');
      } else {
        $('#'+urlKey+'_replace_container').addClass('hiddenElement');
      }
    });
    $('#replace_method_'+urlKey).trigger('change');
  });
</script>
{/literal}
{/crmScope}
