{crmScope extensionKey='org.civicoop.configitems'}

  <div class="crm-content-block">

    <div class="crm-block crm-form-block crm-basic-criteria-form-block">
      <div class="crm-accordion-wrapper crm-configitems_search-accordion collapsed">
        <div class="crm-accordion-header crm-master-accordion-header">{ts}Search configuration sets{/ts}</div><!-- /.crm-accordion-header -->
        <div class="crm-accordion-body">
          <table class="form-layout">
            <tbody>
            <tr>
              <td style="width: 25%;">
                <label>{$form.title.label}</label><br>
                  {$form.title.html}
              </td>
              <td style="width: 25%;">
              </td>
              <td style="width: 25%;"></td>
              <td style="width: 25%;"></td>
            </tr>
            </tbody>
          </table>
          <div class="crm-submit-buttons">
              {include file="CRM/common/formButtons.tpl" location="bottom"}
          </div>
        </div><!- /.crm-accordion-body -->
      </div><!-- /.crm-accordion-wrapper -->
    </div><!-- /.crm-form-block -->


    <div class="action-link">
      <a class="button" href="{crmURL p="civicrm/admin/civiconfig/edit" q="reset=1&action=add" }">
        <i class="crm-i fa-plus-circle">&nbsp;</i>
          {ts}Add Configuration Set{/ts}
      </a>
      <a class="button" href="{crmURL p="civicrm/admin/civiconfig/import" q="reset=1&action=add" }">
        <i class="crm-i fa-upload">&nbsp;</i>
          {ts}Import Configuration{/ts}
      </a>
      <a class="button" href="{crmURL p="civicrm/admin/civiconfig/settings" q="reset=1" }">
        <i class="crm-i fa-gear">&nbsp;</i>
          {ts}Settings{/ts}
      </a>
    </div>

    <div class="clear"></div>

    <div class="crm-results-block">

      <div class="crm-search-results">
        <table class="selector row-highlight">
          <thead class="sticky">
          <tr>
            <th scope="col">{ts}Configuration Set{/ts}</th>
            <th scope="col">{ts}Description{/ts}</th>
            <th scope="col">{ts}Version{/ts}</th>
            <th>&nbsp;</th>
          </tr>
          </thead>
            {foreach from=$config_item_sets item=config_item_set}
              <tr>
                <td>{$config_item_set.title}</td>
                <td>{$config_item_set.description|truncate:20:"...":true}
                    {if (strlen($config_item_set.description) > 20)}
                      <a id="" class="crm-popup medium-popup helpicon" onclick="showConfigItemDescription('{$config_item_set.description}')" href="#"></a>
                    {/if}
                </td>
                <td class="">{$config_item_set.version}</td>
                <td class="right nowrap" style="width: 100px;">
                  <div class="crm-configure-actions">
                        <span class="btn-slide crm-hover-button">{ts}Actions{/ts}
                        <ul class="panel">
                          <li><a class="action-item crm-hover-button" href="{crmURL p='civicrm/admin/civiconfig/edit' q="reset=1&action=update&id=`$config_item_set.id`"}"title="{ts}Edit Configuration Set{/ts}">{ts}Edit{/ts}</a></li>
                          <li><a class="action-item crm-hover-button" href="{crmURL p='civicrm/admin/civiconfig/export' q="reset=1&action=preview&id=`$config_item_set.id`"}"title="{ts}Export Configuration Set{/ts}">{ts}Export{/ts}</a></li>
                          <li><a class="action-item crm-hover-button" href="{crmURL p='civicrm/admin/civiconfig/edit' q="reset=1&action=delete&id=`$config_item_set.id`"}"title="{ts}Delete Configuration Set{/ts}">{ts}Delete{/ts}</a></li>
                          {if $config_item_set.import_file_format && $config_item_set.import_sub_directory}
                            <li><a class="action-item crm-hover-button" href="{crmURL p='civicrm/admin/civiconfig/import' q="reset=1&action=update&id=`$config_item_set.id`"}"title="{ts}Import Configuration Set{/ts}">{ts}Import{/ts}</a></li>
                          {/if}
                        </ul>
                        </span>
                  </div>
                </td>
              </tr>
            {/foreach}
        </table>
      </div>

    </div>
  </div>

    {* dialog for description text *}
  <div id="config_item_set_description_dialog-block"></div>

  <style type="text/css">
    {literal}
    .crm-container .CRM_Civiconfig_Form_Manage .crm-configure-actions .btn-slide {
      padding-right: 15px !important;
      text-indent: initial;
      width: auto;
    }
    {/literal}
  </style>

<script>
{literal}
  function showConfigItemDescription(description) {
    cj("#config_item_set_description_dialog-block").dialog({
      width: 600,
      height: 300,
      buttons: {
        "Done": function () {
          cj(this).dialog("close");
        }
      }
    });
    cj("#config_item_set_description_dialog-block").html(description);
  }
{/literal}
</script>
{/crmScope}
