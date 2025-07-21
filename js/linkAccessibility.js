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
    const newWindowtext = "Link opens in new window";
    const target = el.getAttribute("target");
    const text = el.innerText;
    if (target === "_blank" || target === "new") {
      let label = el.getAttribute("aria-label");
      if (label == null) {
        label = `${text}; ${newWindowtext}`;
      } else {
        label = `${label}; ${newWindowtext}`;
      }
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
