// js/comment-notify.js
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.commentNotify = {
    attach: function (context, settings) {
      if (!drupalSettings.commentNotify) {
        return;
      }

      var nodeId = drupalSettings.commentNotify.nodeId;
      var checkInterval = drupalSettings.commentNotify.interval || 30000; // 30 seconds
      var lastCheck = Math.floor(Date.now() / 1000);

      // Remove any existing alerts when page loads
      $('.new-comment-alert').remove();

      function checkForNewComments() {
        $.ajax({
          url: '/kin-forum/check-new-comments',
          type: 'GET',
          data: {
            node_id: nodeId,
            last_check: lastCheck
          },
          success: function(response) {
            // Process AJAX commands
            if (response && response.length > 0) {
              response.forEach(function(command) {
                if (command.command === 'insert' && command.method === 'prepend') {
                  // Remove existing alerts first
                  $('.new-comment-alert').remove();
                  // Add new alert
                  $(command.selector).prepend(command.data);
                }
              });
            }
            lastCheck = Math.floor(Date.now() / 1000);
          },
          error: function() {
            console.log('Error checking for new comments');
          }
        });
      }

      // Start polling
      var pollTimer = setInterval(checkForNewComments, checkInterval);

      // Stop polling when user leaves the page
      $(window).on('beforeunload', function() {
        clearInterval(pollTimer);
      });

      // Also check when user comes back to the tab
      document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
          checkForNewComments();
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
