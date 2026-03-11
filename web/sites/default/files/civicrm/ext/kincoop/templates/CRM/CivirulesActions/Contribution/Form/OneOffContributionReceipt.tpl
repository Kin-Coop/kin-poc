{* Template for One-Off Contribution Receipt action configuration *}

<div class="crm-block crm-form-block crm-civirule-rule_action-block-one-off-contribution-receipt">

  <h3>{ts}One-Off Contribution Receipt Configuration{/ts}</h3>

  <div class="help">
    <p>{ts}Select the message template to send when a one-off contribution is created.{/ts}</p>
  </div>

  <div class="crm-section">
    <div class="label">
      {$form.message_template_id.label}
      <span class="crm-marker" title="{ts}This field is required.{/ts}">*</span>
    </div>
    <div class="content">
      {$form.message_template_id.html}
      <div class="description">
        {ts}Choose which message template should be sent to the contact.{/ts}
      </div>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

</div>
