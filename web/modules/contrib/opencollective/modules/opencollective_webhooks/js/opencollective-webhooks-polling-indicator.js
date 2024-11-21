console.log('opencollective-webhooks-polling-indicator.js loaded');

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.WebhooksPollingIndicators = {
    attach: function (context) {
      let $indicators = $(context).find('.opencollective-webhooks-polling-indicator').once('webhook_polling_indicators');
      if (!$indicators.length) {
        return;
      }

      console.log($indicators);

      $indicators.each(function () {
        const $indicator = $(this);
        const eventName = $indicator.data('pollingEventName');
        const eventDataExpected = $indicator.data('pollingEventDataExpected')

        $('body').on(eventName, function(event, data) {
          console.log(event, data);
        });
      })

      // $('body').on('opencollective.collective.transaction.created', function(event, data) {
      //   //console.log('checkout.js - jQuery specific event & data', event, data);
      // })
    }
  };

})(jQuery, Drupal, drupalSettings);
