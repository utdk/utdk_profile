/**
 * @file
 * Adds accessibility enhancements to Google search results.
 */

(function utexasGoogleSearchAccessibility(Drupal) {
  "use strict";

  Drupal.behaviors.utexasGoogleSearchAccessibility = {
    attach() {
      window.addEventListener("load", function setGoogleSearchAriaRoles() {
        const results = document.getElementsByClassName("gsc-result-info");
        Object.keys(results).forEach(key => {
          results[key].setAttribute("role", "status");
        });
        // Handle no results.
        const noresults = document.getElementsByClassName(
          "gs-no-results-result"
        );
        Object.keys(noresults).forEach(key => {
          noresults[key].setAttribute("role", "status");
        });

        // Convert title <div> to <h3> for semantic usability.
        const titles = document.querySelectorAll(
          "div.gsc-thumbnail-inside > div.gs-title"
        );
        Object.keys(titles).forEach(key => {
          const header = document.createElement("h3");
          const inner = titles[key].innerHTML;
          header.innerHTML = inner;
          header.classList.add("gs-title");
          titles[key].replaceWith(header);
        });
      });
    }
  };
})(Drupal);
