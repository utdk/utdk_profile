(function ($, Drupal) {
  "use strict";

  /**
   * @file
   *
   * Automatically add anchor-capable HTML ids to all heading elements.
   */
  Drupal.behaviors.utexasAutoAnchors = {
    attach: function (context, settings) {
    let selector = '';
    // Summary is included to facilitate linkable accordions.
    let anchor_elements = ['h2', 'h3', 'h4', 'h5', 'h6', 'summary'];
    for (var j = 0; j < anchor_elements.length; j++) {
      selector += 'body ' + anchor_elements[j] + ', ';
    }
    selector = selector.replace(/,\s*$/, "");
    // Loop through all discovered headings and add a unique ID.
    $(selector).each(function () {
      var anchor = generateAnchor(this);
    });
     // Ensure that *after* JS has added IDs, the page is located on the hash.
     let destination = $(window.location.hash).offset();
     if (typeof destination !== 'undefined') {
       $('html,body').animate({ scrollTop: destination.top }, 'slow');
     }
   }
  }

  /**
   * Passthru method to send an HTML header to a unique ID method.
   * @param {object} el The active heading element.
   */
  function generateAnchor(el) {
    if (el.id) {
      return el.id;
    } else {
      var anchor = generateUniqueId(el);
      el.id = anchor;
      return anchor;
    }
  }

  /**
   * Helper method to provide a URL-safe ID.
   * @param {object} el The active heading element.
   */
  function generateUniqueIdBase(el) {
    var text = $(el).text();
    // Adapted from
    // https://github.com/bryanbraun/anchorjs/blob/65fede08d0e4a705f72f1e7e6284f643d5ad3cf3/anchor.js#L237-L257
    // Regex for finding the non-safe URL characters (many need escaping): & +$,:;=?@"#{}|^~[`%!'<>]./()*\ (newlines, tabs, backspace, & vertical tabs)
    var nonsafeChars = /[& +$,:;=?@"#{}|^~[`%!'<>\]\.\/\(\)\*\\\n\t\b\v]/g,
    urlText;
    // Note: we trim hyphens after truncating because truncating can cause dangling hyphens.
    // Example string:
    // " ⚡⚡ Don't forget: URL fragments should be i18n-friendly, hyphenated, short, and clean."
    urlText = text
      .trim() // "⚡⚡ Don't forget: URL fragments should be i18n-friendly, hyphenated, short, and clean."
      .replace(/\'/gi, "") // "⚡⚡ Dont forget: URL fragments should be i18n-friendly, hyphenated, short, and clean."
      .replace(nonsafeChars, "-") // "⚡⚡-Dont-forget--URL-fragments-should-be-i18n-friendly--hyphenated--short--and-clean-"
      .replace(/-{2,}/g, "-") // "⚡⚡-Dont-forget-URL-fragments-should-be-i18n-friendly-hyphenated-short-and-clean-"
      .substring(0, 64) // "⚡⚡-Dont-forget-URL-fragments-should-be-i18n-friendly-hyphenated-"
      .replace(/^-+|-+$/gm, "") // "⚡⚡-Dont-forget-URL-fragments-should-be-i18n-friendly-hyphenated"
      .toLowerCase(); // "⚡⚡-dont-forget-url-fragments-should-be-i18n-friendly-hyphenated"
    return urlText || el.tagName.toLowerCase();
  }

  /**
   * Create a unique HTML ID based on the name of the element.
   * @param {object} el The active heading element.
   */
  function generateUniqueId(el) {
    var anchorBase = generateUniqueIdBase(el);
    for (var i = 0;; i++) {
      var anchor = anchorBase;
      if (i > 0) {
        // Add suffix
        anchor += "-" + i;
      }
      // Check if ID already exists
      if (!document.getElementById(anchor)) {
        return anchor;
      }
    }
  }
})(jQuery, Drupal);
