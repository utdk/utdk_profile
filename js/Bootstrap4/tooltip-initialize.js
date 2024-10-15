// /**
//  * @file
//  * Placeholder file for custom theme behaviors.
//  *
//  */
(function ($, Drupal, debounce) {

  /**
   * Initialize tooltips.
   */
  Drupal.behaviors.tooltipInit = {
    attach: function (context, settings) {
      $('[data-toggle="tooltip"]').tooltip();
    }
  };

})(jQuery, Drupal, Drupal.debounce);
