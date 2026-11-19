/**
 * @file
 * Opens the grid scrolled to the newest period and switches on note popovers.
 */
(function (Drupal, once) {
  Drupal.behaviors.kinContributionsGrid = {
    attach(context) {
      once('kin-cgrid', '.kin-cgrid__scroll', context).forEach((scroller) => {
        scroller.scrollLeft = scroller.scrollWidth;

        // The theme may already do this for the list page; this makes sure.
        if (window.bootstrap && window.bootstrap.Popover) {
          scroller.querySelectorAll('[data-bs-toggle="popover"]').forEach((link) => {
            window.bootstrap.Popover.getOrCreateInstance(link);
          });
        }
      });
    },
  };
})(Drupal, once);
