/**
 * @file
 *
 * jQuery for various fixes for layout_builder.
 */

/**
 * Fix issue: Modal cannot loses focus when using Layout Builder.
 *
 * @see https://www.drupal.org/project/drupal/issues/3065095#comment-13311079
 */
(function ($, Drupal) {
  let orig_allowInteraction = $.ui.dialog.prototype._allowInteraction;
  $.ui.dialog.prototype._allowInteraction = function (event) {
    if ($(event.target).closest('.cke_dialog').length) {
      return true;
    }
    return orig_allowInteraction.apply(this, arguments);
  };

})(jQuery, Drupal);
