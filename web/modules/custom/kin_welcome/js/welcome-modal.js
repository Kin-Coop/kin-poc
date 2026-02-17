(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.kinWelcomeModal = {
    attach: function (context, settings) {
      // Only attach once to the document
      once('kinWelcomeModal', 'body', context).forEach(function (el) {
        var slides = drupalSettings.kinWelcome.slides;
        var currentSlide = 0;
        var totalSlides = slides.length;

        // Create and inject modal HTML
        var $modal = $('\
          <div id="kin-welcome-overlay">\
            <div id="kin-welcome-modal">\
              <div id="kin-welcome-header">\
                <div id="kin-welcome-progress"></div>\
              </div>\
              <div id="kin-welcome-body">\
                <h2 id="kin-welcome-title"></h2>\
                <div id="kin-welcome-content"></div>\
              </div>\
              <div id="kin-welcome-footer">\
                <button id="kin-welcome-prev" class="btn btn-light">' + Drupal.t('Previous') + '</button>\
                <span id="kin-welcome-counter"></span>\
                <button id="kin-welcome-next" class="btn btn-primary">' + Drupal.t('Next') + '</button>\
              </div>\
            </div>\
          </div>\
        ');

        $('body').append($modal);

        // Render the current slide
        function renderSlide() {
          var slide = slides[currentSlide];
          var isFirst = currentSlide === 0;
          var isLast = currentSlide === totalSlides - 1;

          // Update content
          $('#kin-welcome-title').text(slide.title);
          $('#kin-welcome-content').html(slide.body);

          // Update counter
          $('#kin-welcome-counter').text((currentSlide + 1) + ' / ' + totalSlides);

          // Update progress bar
          var progress = ((currentSlide + 1) / totalSlides) * 100;
          $('#kin-welcome-progress').css('width', progress + '%');

          // Show/hide previous button
          if (isFirst) {
            $('#kin-welcome-prev').hide();
          } else {
            $('#kin-welcome-prev').show();
          }

          // Update next button on last slide
          if (isLast) {
            //$('#kin-welcome-next').text(Drupal.t('Finish'));
            $('#kin-welcome-next').text(Drupal.t('Set Up Kin Membership')).attr('href', slide.link_url);

            // Add link if provided
            //if (slide.link_text && slide.link_url) {
              //if (!$('#kin-welcome-link').length) {
                //var $link = $('<a id="kin-welcome-link" class="button button--secondary"></a>');
                //$('#kin-welcome-footer').append($link);
              //}
              //$('#kin-welcome-link')
                //.text(slide.link_text)
                //.attr('href', slide.link_url);
            //}
          } else {
            $('#kin-welcome-next').text(Drupal.t('Next'));
            $('#kin-welcome-link').remove();
          }
        }

        // Next button click
        $('#kin-welcome-next').on('click', function () {
          if (currentSlide < totalSlides - 1) {
            currentSlide++;
            renderSlide();
          } else {
            // Last slide - mark as shown and close modal
            markModalShown();
          }
        });

        // Previous button click
        $('#kin-welcome-prev').on('click', function () {
          if (currentSlide > 0) {
            currentSlide--;
            renderSlide();
          }
        });

        // Mark modal as shown via AJAX
        function markModalShown() {
          $.ajax({
            url: drupalSettings.kinWelcome.markShownUrl,
            type: 'POST',
            headers: {
              'X-CSRF-Token': drupalSettings.kinWelcome.csrfToken
            },
            success: function () {
              closeModal();
            },
            error: function () {
              // Close anyway even if the request fails
              closeModal();
            }
          });
        }

        // Close modal
        function closeModal() {
          $('#kin-welcome-overlay').fadeOut(300, function () {
            $(this).remove();
          });
        }

        // Render first slide
        renderSlide();

        // Show modal with fade in
        $('#kin-welcome-overlay').hide().fadeIn(300);
      });
    }
  };
})(jQuery, Drupal, drupalSettings, once);
