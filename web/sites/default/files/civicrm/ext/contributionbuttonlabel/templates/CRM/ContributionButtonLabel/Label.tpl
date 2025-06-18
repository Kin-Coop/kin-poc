<div id="contributionbuttonlabel" class="crm-accordion-wrapper crm-custom-accordion collapsed">
  <div class="crm-accordion-header">{ts}Button Labels{/ts}</div>
  <div class="crm-accordion-body">
    <table class="form-layout-compressed">
      <tr class="">
        <td class="label">{$form.main_page_button_label.label}</td>
        <td class="html-adjust">{$form.main_page_button_label.html}</td>
      </tr>
      <tr class="">
        <td class="label">{$form.confirm_page_button_label.label}</td>
        <td class="html-adjust">{$form.confirm_page_button_label.html}</td>
      </tr>
    </table>
  </div>
</div>

{literal}
<script type="text/javascript">
  CRM.$(function($) {
    $($('div#contributionbuttonlabel')).insertAfter('.crm-contribution-contributionpage-settings-form-block table:last');
  });
</script>
{/literal}
