(function($, Drupal) {
  console.log('webhook-events-test-form.js loaded');

  /*
   * Example jQuery subscribers.
   */
  $(document).ready(function() {
    $(document).on('opencollective.webhook_event', 'body', function(event, data) {
      //console.log('jQuery generic event & data', event, data);
    })
    $(document).on('opencollective.collective_update_published', 'body', function(event, data) {
      //console.log('jQuery specific event & data', event, data);
    })
  });

  /*
   * Example Vanilla JS subscribers.
   */
  document.querySelector('body').addEventListener('opencollective.webhook_event', function(event) {
    //console.log('Vanilla generic event', event);
  });
  document.querySelector('body').addEventListener('opencollective.collective_update_published', function(event) {
    //console.log('Vanilla specific event', event);
  });

  /**
   * Container template.
   *
   * @type {string}
   */
  const templateContainer = `
    <div id="polling-output--{{ token }}">
      <div class="polling-output-indicator">
         <img class="indicator-icon spinning" src="/core/misc/icons/bebebe/cog.svg">
      </div>
      <p><strong>Poll Config: </strong></p>
      <pre class="opencollective-test-form poll-config">{{ pollConfig }}</pre>
      <p><strong>JS Event Result:</strong></p>
      <div class="opencollective-test-form data-container"></div>
    </div>
  `;

  /**
   * Event template.
   *
   * @type {string}
   */
  const templateCapturedEvent = `
    <details class="response">
      <summary>{{ summary }}</summary>
      <pre class="opencollective-test-form">{{ responseContent }}</pre>
    </details>
  `;

  /**
   * Simple templating function.
   *
   * @param template
   * @param context
   * @returns {*}
   */
  function template(template, context) {
    Object.keys(context).forEach((key) => {
      const regex = new RegExp("{{[\\W+]" + key + "[\\W+]}}", 'g');
      template = template.replace(regex, context[key]);
    });

    return template;
  }

  /*
   * Testing form is ~ 25% drupal and 75% this js.
   */
  $(document).ready(function() {
    let $container = $('#webhook-events-polling-output');
    let $submit = $('#edit-submit-ajax');

    // Ajax submit.
    $submit.on('click', function(event) {
      event.preventDefault();

     $container.find('.polling-output-indicator .indicator-icon')
        .addClass('spinning')
        .attr('src', '/core/misc/icons/bebebe/cog.svg');

      let payload = $('.opencollective-webhooks-sample-payload--wrapper .opencollective-test-form:visible').text();

      $.ajax({
        url: "/opencollective/webhooks/incoming/" + drupalSettings.openCollectiveWebhooks.testForm.secret,
        method: 'POST',
        dataType: 'json',
        data: payload,
        success: function(response) {
          console.log('Webhook submitted successfully.', response)
        }
      });
    })

    // For each pollConfig on the page, display a UI showing its result.
    $.each(WebhookEventsPollingManager.allPolls, function(token, pollConfig) {
      $container.append(template(templateContainer, {
        token: token,
        pollConfig: JSON.stringify(pollConfig, null, 2),
      }))
    });

    // Subscribe to the webhook event
    $(document).on('opencollective.webhook_event', 'body', function(event, data) {
      console.log(`Webhook event ${data.eventName} heard.`, data);

      $container
        .find(`#polling-output--${data.pollConfig.accessToken} .poll-config`)
        .text( JSON.stringify(data.pollConfig, null, 2))

      $container
        .find(`#polling-output--${data.pollConfig.accessToken} .data-container`)
        .append(template(templateCapturedEvent, {
          summary: `eventId: ${data.webhookPayload.eventLogId}, event: ${data.eventName} <img class="indicator-icon" src="/core/misc/icons/73b355/check.svg">`,
          responseContent: JSON.stringify(data, null, 2),
        }));
    })
  });

})(jQuery, Drupal);
