{crmScope extensionKey='org.civicoop.civirules'}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  {include file="CRM/Civirules/Form/RuleBlocks/RuleBlock.tpl"}
  {include file="CRM/Civirules/Form/RuleBlocks/TriggerBlock.tpl"}
  {if $action ne 1}
    {include file="CRM/Civirules/Form/RuleBlocks/ConditionBlock.tpl"}
    <h3>{ts}Linked Action(s){/ts}</h3>
    <crm-angular-js modules="afsearchRuleActions">
      <div id='bootstrap-theme'>
        <afsearch-rule-actions options="{ldelim}rule_id: {$rule->id}{rdelim}">
        </afsearch-rule-actions>
      </div>
    </crm-angular-js>
    <h3>{ts}Trigger History{/ts}</h3>
    <crm-angular-js modules="afsearchRuleTriggerHistory">
      <div id='bootstrap-theme'>
        <afsearch-rule-trigger-history options="{ldelim}rule_id: {$rule->id}{rdelim}">
        </afsearch-rule-trigger-history>
      </div>
    </crm-angular-js>
  {/if}

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/crmScope}
