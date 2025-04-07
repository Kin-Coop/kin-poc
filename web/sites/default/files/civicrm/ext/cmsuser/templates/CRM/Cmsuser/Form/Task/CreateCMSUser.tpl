{crmScope extensionKey='cmsuser'}
  <h3>{$title}</h3>
{if ($status)}
  <div class="messages status no-popup">
      {$status}
  </div>
{/if}
  <div class="crm-block crm-form-block crm-task-createcmsuser-confirm-block">
      {if ($help_text)}
        <div class="help">{$help_text}</div>
      {/if}
    <div class="cmsuser-notify-user crm-section form-item">
      <div class="notify-user label">
          {$form.notify_user.label}
      </div>
      <div class="notify-user content">
          {$form.notify_user.html}
      </div>
    </div>
  </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/crmScope}
