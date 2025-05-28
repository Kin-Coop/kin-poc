{crmScope extensionKey='org.civicoop.configitems'}

{if $action eq 8}
    {* Are you sure to delete form *}
  <h3>{ts}Delete Configuration Set{/ts}</h3>
  <div class="crm-block crm-form-block crm-config_item_set-block">
    <div class="crm-section">{ts 1=$config_item_set.title}Are you sure to delete configuration set '%1'?{/ts}</div>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>

{else}
  <div class="crm-block crm-form-block crm-config_item_set_title-block">
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
    <div class="crm-section">
      <div class="label">{$form.title.label}</div>
      <div class="content">
          {$form.title.html}
        <span class="">
        {ts}System name:{/ts}&nbsp;
        <span id="systemName" style="font-style: italic;">{if ($config_item_set)}{$config_item_set.name}{/if}</span>
        <a href="javascript:void(0);" onclick="jQuery('#nameSection').removeClass('hiddenElement'); jQuery(this).parent().addClass('hiddenElement'); return false;">
          {ts}Change{/ts}
        </a>
      </span>
      </div>
      <div class="clear"></div>
    </div>
    <div id="nameSection" class="crm-section hiddenElement">
      <div class="label">{$form.name.label}</div>
      <div class="content">
          {$form.name.html}
        <p class="description">{ts}Leave empty to let the system generate a name. The name should consist of lowercase letters, numbers and underscore. E.g newsletter_subscription.{/ts}</p>
      </div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.version.label}</div>
      <div class="content">{$form.version.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.entities.label}</div>
      <div class="content">
          <p class="description">{ts}What do you want to export?{/ts}</p>
          <p class="description">
            <input type="checkbox" class="crm-form-checkbox" name="entities_selectall" id="entities_selectall" value="1" />
            <label for="entities_selectall"><a>{ts}Select all{/ts}</a></label>
          </p>
          {$form.entities.html}
      </div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.description.label}</div>
      <div class="content">{$form.description.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>

  <script type="text/javascript">
      {literal}
      CRM.$(function($) {
        var id = {/literal}{if ($config_item_set)}{$config_item_set.id}{else}false{/if}{literal};

        $('#title').on('blur', function() {
          var title = $('#title').val();
          if ($('#nameSection').hasClass('hiddenElement') && !id) {
            CRM.api4('ConfigItemSet', 'checkName', {
              'title': title
            }).then(function (results) {
              $('#systemName').html(results[0].name);
              $('#name').val(results[0].name);
            }, function (failure) {
              // Do nothing.
            });
          }
        });

        var skipEntitiesSelectall = true;
        $('#entities_selectall').on('change', function() {
          if (!skipEntitiesSelectall) {
            if ($('#entities_selectall').is(":checked")) {
              $('input[type="checkbox"].entities_checkbox').prop("checked", true)
            }
            else {
              $('input[type="checkbox"].entities_checkbox').prop("checked", false)
            }
          }
        });

        if ($('input[type="checkbox"].entities_checkbox:checked').length == $('input[type="checkbox"].entities_checkbox').length) {
          $('#entities_selectall').prop('checked', true);
        } else {
          $('#entities_selectall').prop('checked', false);
        }
        skipEntitiesSelectall = false;
      });
      {/literal}
  </script>

{/if}
{/crmScope}
