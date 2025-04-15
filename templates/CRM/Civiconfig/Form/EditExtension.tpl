{crmScope extensionKey='org.civicoop.configitems'}
<div class="crm-block crm-form-block crm-config_item_edit_extension-block">
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <div class="help">
    <p>{ts}Below an example of what you can enter here.{/ts}</p>
    <p><strong>{ts}Action Provider - CiviCRM Extension Directory{/ts}</strong></p>
    <p>
        {ts}Name: action-provider{/ts}<br />
        {ts}Download source: CiviCRM Extension Directory{/ts}
    </p>
    <p><strong>{ts}Action Provider (version 1.83) - ZIP{/ts}</strong></p>
    <p>
        {ts}Name: action-provider{/ts}<br />
        {ts}Download source: ZIP{/ts}<br />
        {ts}URL: https://lab.civicrm.org/extensions/action-provider/-/archive/1.83/action-provider-1.83.zip{/ts}
    </p>
    <p><strong>{ts}Action Provider (version 1.83) - Git{/ts}</strong></p>
    <p>
        {ts}Name: action-provider{/ts}<br />
        {ts}Download source: Gitlab / Github{/ts}<br />
        {ts}URL: https://lab.civicrm.org/extensions/action-provider.git{/ts}<br />
        {ts}Branch / Tag: 1.83{/ts}
    </p>
  </div>

  <div class="crm-section">
    <div class="label">{$form.key.label}</div>
    <div class="content">
        {$form.key.html}
        <p class="description">
            {ts}This is the system name of the extension.{/ts}<br />
            {ts}E.g. dataprocessor or form-processor.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.download_source.label}</div>
    <div class="content">{$form.download_source.html}</div>
    <div class="clear"></div>
  </div>

  <div id="crm-section-url" class="crm-section hiddenElement">
    <div class="label">{$form.url.label}</div>
    <div class="content">
        {$form.url.html}
        <p class="description">
            {ts}E.g. For Data Processor Version 1.44 ZIP File: https://lab.civicrm.org/extensions/dataprocessor/-/archive/1.44/dataprocessor-1.44.zip{/ts}<br />
            {ts}E.g. For Data Processor Version 1.44 with GIT use URL: https://lab.civicrm.org/extensions/dataprocessor.git and set Branch /Tag to 1.44{/ts}
        </p>
    </div>
    <div class="clear"></div>
  </div>

  <div id="crm-section-branch" class="crm-section hiddenElement">
    <div class="label">{$form.branch.label}</div>
    <div class="content">
        {$form.branch.html}
        <p class="description">{ts}Leave empty to use the default git branch. You can use this to specify a certain tag, which is the git-term for version.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $('#download_source').on('change', function(){
        var downloadSource = $('#download_source').val();
        if (downloadSource == 'git') {
          $('#crm-section-url').removeClass('hiddenElement');
          $('#crm-section-branch').removeClass('hiddenElement');
        } else if (downloadSource == 'zip') {
          $('#crm-section-url').removeClass('hiddenElement');
          $('#crm-section-branch').addClass('hiddenElement');
        } else {
          $('#crm-section-url').addClass('hiddenElement');
          $('#crm-section-branch').addClass('hiddenElement');
        }
      });

      $('#download_source').trigger('change');
    });
  </script>
{/literal}
{/crmScope}
