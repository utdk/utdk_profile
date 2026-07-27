/**
 * @file
 * Adds accessibility enhancements to Google search results.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.utexasGoogleSearchAccessibility = {
    attach: function () {
      window.addEventListener('load', function () {
        let results = document.getElementsByClassName('gsc-result-info');
        Object.keys(results).forEach(key => {
          results[key].setAttribute('role', 'status');
        })
        // Handle no results.
        let noresults = document.getElementsByClassName('gs-no-results-result');
        Object.keys(noresults).forEach(key => {
          noresults[key].setAttribute('role', 'status');
        })

        // Convert title <div> to <h3> for semantic usability.
        let titles = document.querySelectorAll('div.gsc-thumbnail-inside > div.gs-title');
        Object.keys(titles).forEach(key => {
          let header = document.createElement("h3");
          let inner = titles[key].innerHTML;
          header.innerHTML = inner;
          header.classList.add('gs-title');
          titles[key].replaceWith(header);
        })

      })

    }
  };
})(jQuery, Drupal);
