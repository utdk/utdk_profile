(function ($, Drupal) {
  "use strict";

  /**
   * @file
   *
   * Progressive enhancements to make links more accessible.
   */
  Drupal.behaviors.utexasLinkAccessibility = {
    attach: function (context, settings) {
      $('body a').each(function () {
        modifyLink(this);
      });
    }
  }

  /**
   * If a link has target="_blank"/"new", append an aria-label.
   * @param {object} el The link element.
   */
  function modifyLink(el) {
    let newWindowtext = "Link opens in new window";
    var target = el.getAttribute('target');
    if (target == '_blank' || target == 'new') {
      var label = el.getAttribute('aria-label');
      if (label == null) {
        label = newWindowtext;
      }
      else {
        label = label + '; ' + newWindowtext;
      }
      el.setAttribute('aria-label', label);
    }
  }

})(jQuery, Drupal);
