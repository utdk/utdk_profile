(function ($, Drupal) {
  "use strict";

  /**
   * @file
   *
   * Hero formatter split progressive enhancement definition.
   *
   * To facilitate the usage of more than 10 image styles/view modes for the
   * hero, we create 2 different HTML select elements: one for the style,
   * and one for anchor positioning. The combination of both decide
   * which of the original image styles gets picked. This choice then
   * modifies the now hidden original select element. Drupal then saves
   * things as it normally would. This is purely a user facing change,
   * not a data storage change.
   */

  /**
   * Define a Drupal behavior to create custom hero select elements.
   */
  Drupal.behaviors.utexasHeroCustomSelectors = {
    attach: function (context, settings) {
      // 1. Hero styles may be accessed via field formatter or view mode.
      // Determine which of these is active, or exit if neither.
      var form_mode = getFormMode();
      if (form_mode === null) {
        return;
      }
      // Determine original select HTML element, depending on the form mode.
      var form_mode_dom =
      (form_mode === "formatter" ? ".js-form-item-settings-formatter-type"
      : ".js-form-item-settings-view-mode");
      // If there is no hero default selector, we are not on a hero
      // configuration form.
      $(form_mode_dom).each(function() {
        if ($(form_mode_dom)
        .find("select option[value=utexas_hero_1_left]").length === null) {
          return;
        }
      });
      // Set the formatter type which differ between formatter and view mode.
      var formatter_type =
      (form_mode === "view_mode" ? "settings[" + form_mode + "]"
      : "settings[" + form_mode + "][type]");
      // Set jquery element for original hidden select element.
      var original_select_element = $("select[name='" + formatter_type + "']");
      var current_formatter_style = original_select_element.val();
      // Since formatter and view mode don't have a consistent value for the
      // default select element, we attempt to set the value to "default" if the
      // current style is indeed set to any of the default values.
      var default_style = ((current_formatter_style === undefined
      || current_formatter_style === "full"
      || current_formatter_style === "utexas_hero") ? "default"
      : current_formatter_style);
      // Access helper function to split the current formatter into custom
      // style and anchor position.
      var style_and_anchor = getStyleAndAnchorValue(default_style);

      // 2. Create custom select HTML elements if not already present.
      createSelectors(form_mode_dom);

      // 3. Set values of custom select HTML elements.
      // Update the value in the style custom select element.
      $("select[name='hero_style']")
      .val(default_style !== "default" ? style_and_anchor.style
      : default_style);
      // Update the value in the anchor custom select element.
      if (default_style !== "default") {
        $("select[name='anchor_position']").val(style_and_anchor.anchor);
      }
      // Convert default_style if "default" is set but we use a formatter form
      // mode.
      default_style = (form_mode === "formatter"
      && default_style === "default" ? "utexas_hero"
      : default_style);
      // Update the values in the original hidden select element.
      original_select_element.val(default_style);
      // Toggle anchor select element if current hero don't use anchor.
      toggleAnchorSelectElement(default_style);

      // 4. Watch for changes on the custom select elements, and keep
      // the original select element in sync.

      // Watch the hero style custom select element.
      $("select[name='hero_style']", context).change(function() {
        var hero_style = "";
        $("select[name='hero_style'] option:selected").each(function() {
          // Get the hero style and convert to utexas_hero if form mode is set
          // to formatter.
          hero_style = (form_mode === "formatter"
          && $("select[name='hero_style'] option:selected").val() === "default"
          ? "utexas_hero"
          : $("select[name='hero_style'] option:selected").val());
        });
        updateSelectors(original_select_element, "", hero_style);
      });

      // Watch the hero anchor custom select element.
      $("select[name='anchor_position']", context).change(function() {
        var anchor;
        $("select[name='anchor_position'] option:selected").each(function() {
          anchor = "_" + $("select[name='anchor_position'] option:selected")
          .val();
        });
        updateSelectors(original_select_element, anchor, "");
      });
    }
  };



  /**
   * Create the custom select HTML elements and appends them to the form.
   * @param {string} form_mode_dom The parent element where we create and set
   *     the new selectors into.
   */
  function createSelectors(form_mode_dom) {
    var hero_style_selector =
      `<div class="js-form-item form-item js-form-type-select
      form-item-hero-style js-form-item-hero-style">
        <label for="edit-hero-style">Select Hero Style</label>
        <select data-drupal-selector="edit-hero-style"
        aria-describedby="edit-hero-style-description" id="edit-hero-style"
        name="hero_style" class="form-select">
          <option value="default">
            Default: Large media with optional caption and credit line
          </option>
          <option value="utexas_hero_1">
            Style 1: Bold heading &amp; subheading on burnt orange background
          </option>
          <option value="utexas_hero_2">
            Style 2: Bold heading on dark background, anchored at base of media
          </option>
          <option value="utexas_hero_3">
            Style 3: White bottom pane with heading, subheading and burnt orange
            call to action
          </option>
          <option value="utexas_hero_4">
            Style 4: Centered image with dark bottom pane containing heading,
            subheading and call-to-action
          </option>
          <option value="utexas_hero_5">
            Style 5: Medium image, floated right, with large heading,
            subheading and burnt orange call-to-action
          </option>
        </select>
      </div>`
    ;
    var anchor_position_selector =
      `<div class="js-form-item form-item js-form-type-select
      form-item-anchor-position js-form-item-anchor-position">
        <label for="edit-anchor-position">Image anchor position</label>
        <select data-drupal-selector="edit-anchor-position"
        aria-describedby="edit-anchor-position-description"
        id="edit-anchor-position" name="anchor_position" class="form-select">
          <option value="center">Center</option>
          <option value="left">Left</option>
          <option value="right">Right</option>
        </select>
        <div id="edit-anchor-position-description" class="description">
          Set what part of the image should be the focal anchor.
        </div>
      </div>`
    ;
    // We loop through each element within the parent DOM.
    $(form_mode_dom).each(function() {
      // Check if hero style default selector is present.
      if ($(form_mode_dom)
      .find("select option[value=utexas_hero_1_left]").length) {
        // Validate custom selectors exist and create them if they don't.
        if ($("#edit-hero-style").length === 0) {
          $(form_mode_dom).after(anchor_position_selector)
          .after(hero_style_selector);
          // Hide the original selector after appending the custom ones.
          $(form_mode_dom).hide();
        }
      }
    });
  }

  /**
   * Update the values and state of the select elements.
   *
   * @param {string} original_select_element This variable defines the HTML
   *    original hidden select element.
   * @param {string} anchor (Optional) The anchor value which could be "center",
   *    "left" or "right". Also used to validate the selector visibility.
   * @param {string} hero_style (Optional) The hero style that will be massaged
   *    and define the official formatter/view mode value. Will also be used to
   *    validate and massage the hero_style value.
   */
  function updateSelectors(original_select_element, anchor="", hero_style="") {
    // If no hero style passed as argument, get the current value.
    hero_style = ((hero_style === "")
    ? $("select[name='hero_style'] option:selected").val()
    : hero_style);
    // If no anchor passed as argument, get the current value
    anchor = ((anchor === "")
    ? "_" + $( "select[name='anchor_position'] option:selected").val()
    : anchor);
    // Massage the custom anchor position value.
    toggleAnchorSelectElement(hero_style);
    var disabled_anchor = ($("#edit-anchor-position").prop("disabled") ? true
    : false);
    // Don't add suffix if anchor is center or anchor select is disabled.
    anchor = ((anchor === "_center" || disabled_anchor) ? ""
    : anchor);
    // Update original formatter select element value.
    original_select_element.val(hero_style + anchor);
  }

  /**
   * Return either formatter or view_mode depending on context the
   * utexas_hero field is being used.
   * This will vary between node, inline block, reusable block, etc.
   * @return {string} Returns the form_mode if valid, or null if not
   */
  function getFormMode() {
    var form_mode = null;
    // Check if the layout sidebar has a formatter or view mode, if not, exit.
    if ($("#drupal-off-canvas")
    .has(".js-form-item-settings-formatter-type").length
    || $("#layout-builder-modal")
    .has(".js-form-item-settings-formatter-type").length) {
      form_mode = "formatter";
    }
    else if ($("#drupal-off-canvas")
    .has(".js-form-item-settings-view-mode").length
    || $("#layout-builder-modal")
    .has(".js-form-item-settings-view-mode").length) {
      form_mode = "view_mode";
    }
    else if ($(".block-form").has(".js-form-item-settings-view-mode").length
    || $("#layout-builder-modal")
    .has(".js-form-item-settings-view-mode").length) {
      form_mode = "view_mode";
    }
    return form_mode;
  }

  /**
   * Return the current style and anchor values from the default Drupal
   * select element.
   *
   * @param {string} default_style Should contain
   *    a value similar to "utexas_hero_X_[anchor]" where X is a number that
   *    defines the style number to select in the custom hero style selector.
   *    And an optional anchor that defines the value of the anchor selector.
   * @return {array} The style and anchor settings.
   */
  function getStyleAndAnchorValue(default_style) {
    // Split and return an array.
    var default_style_split = default_style.split("utexas_hero_");
    // Will return the style and anchor if found, e.g. ['1', 'left'],
    // or 'utexas_hero'.
    var style_and_anchor = (default_style_split[1] !== undefined
    ? default_style_split[1].split("_")
    : "utexas_hero");
    var anchorDefined = style_and_anchor[1] !== undefined;
    return {
      "style" : "utexas_hero_" + style_and_anchor[0],
      "anchor" : (anchorDefined ? style_and_anchor[1]
      : "center"),
    };
  }

  /**
   * Toggle anchor select element state depending on selected hero style.
   *
   * @param {string} hero_style The current hero style.
   */
  function toggleAnchorSelectElement(hero_style) {
    // Disable anchor select element if using default or style 4.
    if (hero_style === "default" || hero_style === "utexas_hero"
    || hero_style === "utexas_hero_4") {
      $("#edit-anchor-position").prop("disabled", true);
    } else {
      $("#edit-anchor-position").removeAttr("disabled");
    }
  }

})(jQuery, Drupal);