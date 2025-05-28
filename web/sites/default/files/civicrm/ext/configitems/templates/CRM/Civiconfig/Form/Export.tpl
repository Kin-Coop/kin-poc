{crmScope extensionKey='org.civicoop.configitems'}
<div class="crm-block crm-form-block crm-config_item_export-block">
  <div class="crm-section">
    <div class="label">{$form.file_format.label}</div>
    <div class="content">{$form.file_format.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label"><label>{ts}Current version{/ts}</label></div>
    <div class="content" id="configItemSetVersion">{$config_item_set.version}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.increment_version.label}</div>
    <div class="content">{$form.increment_version.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-submit-buttons">
      <a id="downloadButton" class="crm-button button submit-link" href=""><i class="fa-download crm-i" aria-hidden="true">&nbsp;</i>{ts}Download export file{/ts}</a>
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
  <script type="text/javascript">
      {literal}
      CRM.$(function($) {
        var id = {/literal}{if ($config_item_set)}{$config_item_set.id}{else}false{/if}{literal};

        $('#downloadButton').on('click', function() {
          var file_format = $('#file_format option:selected').val();
          var increment_version = $('input[name="increment_version"]:checked').val();
          var url = CRM.url('civicrm/admin/civiconfig/export', 'action=export&id='+id+'&file_format='+file_format+'&increment_version='+increment_version);
          $('input[name="increment_version"][value="0"]').prop('checked', true);
          $('#file_format option:selected').trigger('change');
          if (increment_version > 0) {
            var version = Number($('#configItemSetVersion').text());
            version ++;
            $('#configItemSetVersion').text(version);
          }
          window.location.href = url;
          return false;
        });

        $('#file_format').on('change', validate);
        $('input[name="increment_version"]').on('change', validate);
        $('#file_format option:selected').trigger('change');

        function validate() {
          var file_format = $('#file_format option:selected').val();
          var increment_version = $('input[name="increment_version"]:checked').val();
          if (file_format == undefined || !file_format || increment_version == undefined) {
            $('#downloadButton').hide();
          } else {
            $('#downloadButton').show();
          }

          CRM.api4('ConfigItemSet', 'get', {
            select: ["version"],
            where: [["id", "=", id]],
            limit: 1
          }).then(function(configItemSets) {
            $('#configItemSetVersion').text(configItemSets[0].version);
          }, function(failure) {
            // handle failure
          });
        }
      });
      {/literal}
  </script>
{/crmScope}
