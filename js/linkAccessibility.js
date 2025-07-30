(($, Drupal) => {
  "use strict";

  /**
   * @file
   *
   * Progressive enhancements to make links more accessible.
   */
  /**
   * If a link has target="_blank"/"new", append an aria-label.
   * @param {object} el The link element.
   */
  function modifyLink(el) {
    const classes = el.getAttribute("class");
    const target = el.getAttribute("target");
    let label = el.getAttribute("aria-label") ?? el.innerText;
    let labelAppendage = "";
    if (classes) {
      if (classes.includes("ut-cta-link--external")) {
        labelAppendage += "; external link";
      }
      if (classes.includes("ut-cta-link--lock")) {
        labelAppendage += "; restricted link";
      }
    }
    if (target === "_blank" || target === "new") {
      labelAppendage += "; opens in new window";
    }
    if (labelAppendage) {
      label += labelAppendage;
      el.setAttribute("aria-label", label);
    }
  }

  Drupal.behaviors.utexasLinkAccessibility = {
    attach() {
      $("body a").each(function handleLink() {
        modifyLink(this);
      });
    }
  };
})(jQuery, Drupal);
