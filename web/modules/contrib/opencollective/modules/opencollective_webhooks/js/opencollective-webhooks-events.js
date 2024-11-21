// Available globally for debugging.
let WebhookEventsPollingManager = {};

(function($, Drupal, drupalSettings) {
  /**
   * Polling manager.
   *
   * @type {{init: WebhookEventsPollingManager.init, discoverPollConfigs: (function(): {}), startPolling: WebhookEventsPollingManager.startPolling, selector: string, allPolls: *[]}}
   */
  WebhookEventsPollingManager = {
    selector: 'script.opencollective-webhooks-poll',
    allPolls: [],
    // Track setInterval so we can clearInterval when needed.
    pollIntervals: {},
    pollLengthMicro: drupalSettings.openCollectiveWebhooks.settings.pollLength * 1000,
    debugMode: true,
    eventsDispatched: [],

    /**
     * Start the polling manager.
     */
    init: function(webhooksSettings) {
      this.allPolls = webhooksSettings.polls || {};
      this.debugMode = webhooksSettings.settings.debugMode || false;
      this.startPolling();
    },

    /**
     * Debug logging.
     */
    debug: function() {
      if (this.debugMode) {
        console.log(...arguments);
      }
    },

    /**
     * Get the polling config for the given access token.
     *
     * @param accessToken
     * @returns {*}
     */
    getPollConfig: function(accessToken) {
      return this.allPolls[accessToken];
    },

    /**
     * Dispatch custom events to the document body tag.
     *
     * @param eventName
     * @param data
     */
    dispatchEvent: function(eventName, data) {
      this.eventsDispatched.push({
        eventName,
        data
      })

      // Couldn't get either one of these to work for the other, so do both.
      this.debug('Dispatching event: ' + eventName)

      // jQuery.
      $('body').trigger(eventName, data);

      // Vanilla.
      document.querySelector('body').dispatchEvent(
        new CustomEvent(eventName,data)
      );
    },

    /**
     * Start polling for all polling configurations on the page.
     */
    startPolling: function() {
      // When long polling is enabled, the ajax request should take a little
      // longer than the pollLength.
      // When disabled (short polling), the interval between requests should be
      // quick to appear responsive.
      const intervalTimeout = this.pollLengthMicro ? this.pollLengthMicro : 2500;

      for (let [accessToken, pollConfig] of Object.entries(this.allPolls)) {
        // Start a poll immediately
        this.pollServer(pollConfig);

        // Set interval of polling.
        this.pollIntervals[accessToken] = setInterval(() => {
          // Refresh our pollConfig before each interval to get changes.
          pollConfig = this.getPollConfig(accessToken);
          this.pollServer(pollConfig)
        }, intervalTimeout);
      }
    },

    /**
     * Recursive ajax polling.
     *
     * @param pollConfig {*}
     */
    pollServer: function(pollConfig) {
      const accessToken = pollConfig.accessToken;
      const startTime = Date.now();
      this.debug('Started polling for ' + accessToken, {startTime: startTime});
      let _this = this;

      // If long polling is disabled, allow a longer request timeout to closer
      // match the default ajax() setting of undefined (no timeout).
      const requestTimeout = this.pollLengthMicro ? this.pollLengthMicro : 15000;

      $.ajax({
        url: "/opencollective/js/events/" + accessToken,
        method: 'POST',
        timeout: requestTimeout,
        dataType: "json",
        data: {
          lastEventId: pollConfig.lastEventId,
        },
        success: function(responseData){
          const endTime = Date.now();
          _this.debug(accessToken + ' - Success ajax event in polling', responseData, {
            startTime,
            endTime,
            timeDiff: (endTime - startTime),
            timeDiffSecs: (endTime - startTime) / 1000,
          });

          if (!responseData.success) {
            _this.debug(accessToken + ' - Success ajax event response did not contain success from the endpoint.', responseData);
            return;
          }

          // Update config's latest event id.
          _this.allPolls[accessToken].lastEventId = responseData.updatedLastEventId;

          // Loop through each new payload received from the poll and dispatch
          // events.
          for (const [index, webhookPayload] of Object.entries(responseData.data)) {
            const eventData = {
              eventName: 'opencollective.' + webhookPayload.type.replace(/\./g, '_'),
              pollConfig: pollConfig,
              webhookPayload: webhookPayload,
            };

            // Payload type as event name.
            _this.dispatchEvent(eventData.eventName, eventData);

            // Generic event.
            _this.dispatchEvent('opencollective.webhook_event', eventData);
          }
        },
        error: function(response, status) {
          const endTime = Date.now();
          _this.debug(accessToken + ' - Error: ' + status, response, {
            startTime,
            endTime,
            timeDiff: (endTime - startTime),
            timeDiffSecs: (endTime - startTime) / 1000,
          });
        },
        complete: function(status) {
          const endTime = Date.now();
          _this.debug(accessToken + ' - Completed polling: ' + status, {
            startTime,
            endTime,
            timeDiff: (endTime - startTime),
            timeDiffSecs: (endTime - startTime) / 1000,
          })
        },
      });
    },
  };

  /**
   * Polling as drupal behavior.
   *
   * @type {{attach: Drupal.behaviors.openCollectiveWebhooksEvents.attach}}
   */
  Drupal.behaviors.openCollectiveWebhooksEvents = {
    attach: function (context, settings) {
      let $pollConfigs = $(context).find(WebhookEventsPollingManager.selector).once('webhook_events_polling');
      if (!$pollConfigs.length) {
        return;
      }

      WebhookEventsPollingManager.init(settings.openCollectiveWebhooks);
    }
  }

})(jQuery, Drupal, drupalSettings)
