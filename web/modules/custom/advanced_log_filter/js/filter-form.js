(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.advancedLogFilter = {
    attach: function (context, settings) {
      // Add convenient "Select All" / "Deselect All" functionality for checkboxes
      $('.form-checkboxes', context).once('advanced-log-filter').each(function() {
        var $checkboxContainer = $(this);
        var $checkboxes = $checkboxContainer.find('input[type="checkbox"]');

        if ($checkboxes.length > 3) {
          // Add select all/none links
          var $controls = $('<div class="checkbox-controls"></div>');
          var $selectAll = $('<a href="#" class="select-all">Select All</a>');
          var $selectNone = $('<a href="#" class="select-none">Select None</a>');

          $controls.append($selectAll).append(' | ').append($selectNone);
          $checkboxContainer.prepend($controls);

          $selectAll.on('click', function(e) {
            e.preventDefault();
            $checkboxes.prop('checked', true);
          });

          $selectNone.on('click', function(e) {
            e.preventDefault();
            $checkboxes.prop('checked', false);
          });
        }
      });

      // Auto-submit on date change for better UX
      $('.form-item-date-from input, .form-item-date-to input', context).once('auto-filter').on('change', function() {
        // Optional: Auto-submit when dates change
        // $(this).closest('form').find('[data-drupal-selector="edit-submit"]').click();
      });

      // Add filter summary if filters are active
      var $form = $('#advanced-log-filter-form', context);
      if ($form.length && window.location.search) {
        var params = new URLSearchParams(window.location.search);
        var activeFilters = [];

        // Check for active filters
        if (params.get('date_from')) {
          activeFilters.push('Date from: ' + params.get('date_from'));
        }
        if (params.get('date_to')) {
          activeFilters.push('Date to: ' + params.get('date_to'));
        }
        if (params.get('user')) {
          activeFilters.push('User: ' + params.get('user'));
        }
        if (params.get('text_search')) {
          activeFilters.push('Search: "' + params.get('text_search') + '"');
        }

        if (activeFilters.length > 0) {
          var $summary = $('<div class="filter-summary"></div>');
          $summary.append('<h4>Active Filters:</h4>');
          var $list = $('<ul></ul>');

          activeFilters.forEach(function(filter) {
            $list.append('<li>' + filter + '</li>');
          });

          $summary.append($list);
          $summary.append('<div class="clear-filters"><a href="' + window.location.pathname + '" class="button">Clear All Filters</a></div>');

          $form.after($summary);
        }
      }
    }
  };

})(jQuery, Drupal);
