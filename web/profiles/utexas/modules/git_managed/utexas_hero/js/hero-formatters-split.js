(function ($, Drupal) {
  "use strict";

  /**
   * Creates the custom selectors DOM objects and append them to a form item.
   * @param {string} entity_type The entity type which could be either "node"
   *     or "block". This will define to which item the DOM is appended.
   */
  function createSelectors(entity_type) {
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
    // Pick a selector to append selectors based on entity type.
    var entity_type_dom = ((entity_type === "node") ? ".js-form-item-settings-formatter-type" : ".js-form-item-settings-view-mode");

    $(entity_type_dom).each(function (index) {
      if ($(this).find('select option[value=utexas_hero_1_left]') !== null) {
        // Validate custom selectors exist in sidebar, and add them if they don't.
        if ($("#edit-hero-style").length === 0) {
          $(entity_type_dom).after(anchor_position_selector)
            .after(hero_style_selector);
          // Once custom selectors are in, hide official formatter/view mode field.
          $(entity_type_dom).hide();
        }
      }
    });
  }

  /**
   * Converts the "default" formatter value to "utexas_hero" if entity type.
   * is of the type "node".
   * @param {string} hero_style The hero style that we will validate/convert.
   * @param {string} entity_type The entity type which could be either "node"
   *     or "block". This will determine if the hero_style should be converted.
   * @return {string} The hero style machine name to be used for the formatter.
   */
  function validateNodeEntityAndConvertDefaultToUtexasHero(hero_style,
  entity_type) {
    return ((entity_type === "node" && hero_style === "default") ? "utexas_hero" : hero_style);
  }

  /**
   * Disable the anchor position selector for styles that don't require it.
   * Also, massage the hero_style value if anchor is set to "center".
   * @param {string} hero_style The current picked hero style.
   * @param {string} anchor The anchor value to be validated/converted
   * @return {string} The massaged anchor value which if set to center will
   *    return an empty string.
   */
  function validateAnchor(hero_style, anchor) {
    // Don't add suffix if using default or style 4. And hide the form item.
    if (hero_style === "default" || hero_style === "utexas_hero" || hero_style === "utexas_hero_4") {
      anchor = "";
      $("#edit-anchor-position").prop("disabled", true);
    } else {
      $("#edit-anchor-position").removeAttr("disabled");
    }
    // Don't add suffix if this is center.
    anchor = ((anchor === "_center") ? "" : anchor);
    return anchor;
  }

  /**
   * Update all of the selector values/status based on certain criteria.
   * @param {string} entity_type The entity type which could be "node" or
   *    "block".
   * @param {string} formatter_type This variable defines the HTML "name"
   *    attribute that belongs to the original formatter/view mode selector.
   * @param {string} anchor (Optional) The anchor value which could be "center",
   *    "left" or "right". Also used to validate the selector visibility.
   * @param {string} hero_style (Optional) The hero style that will be massaged
   *    and define the official formatter/view mode value. Will also be used to
   *    validate and massage the hero_style value.
   */
  function updateSelectors(entity_type, formatter_type,
  anchor="", hero_style="") {
    // If no hero style passed as argument, grab the current value.
    hero_style = ((hero_style === "") ? $("select[name='hero_style'] option:selected").val() : hero_style);
    // If no anchor passed as argument, grab the current value
    anchor = ((anchor === "") ? "_" + $( "select[name='anchor_position'] option:selected").val() : anchor);
    // Massage hero style value.
    hero_style = validateNodeEntityAndConvertDefaultToUtexasHero(hero_style, entity_type);
    // Massage anchor value.
    anchor = validateAnchor(hero_style, anchor);
    // Set official formatter/view mode value.
    $("select[name='" + formatter_type + "']").val(hero_style + anchor);
  }

  /**
   * Validate if the entity type in the layout sidebar is a valid type by
   * looking up for certain DOM elements, and define the entity_type value
   * based on the result.
   * @return {string} The entity_type value which will define if the custom
   *    selectors should be created or not.
   */
  function initializeLibraryWhenValidEntityType() {
    var entity_type;
    // Check if the layout sidebar has a formatter or view mode, if not, exit.
    if ($("#drupal-off-canvas").has(".js-form-item-settings-formatter-type").length || $("#layout-builder-modal").has(".js-form-item-settings-formatter-type").length) {
      entity_type = "node";
    } 
    else if ($("#drupal-off-canvas").has(".js-form-item-settings-view-mode").length || $("#layout-builder-modal").has(".js-form-item-settings-view-mode").length) {
      entity_type = "block";
    } else {
      entity_type = "invalid";
    }
    return entity_type;
  }

  /**
   * Grabs the default value formatter/view mode value, splits the string into
   * two variables, one for the hero style, and one for the anchor that is set.
   * @param {string} default_style Prevalidated variable which should contain
   *    a value similar to "utexas_hero_X_[anchor]" where X is a number that
   *    defines the style number to select in the custom hero style selector.
   *    And an optional anchor that defines the value of the anchor selector.
   * @return {string} The default hero style value that can now be set and used.
   */
  function splitDefaultStyleAndSetAnchorValue(default_style) {
    default_style = default_style.split("utexas_hero_");
    if (default_style.length > 1) {
      var style_and_anchor = default_style[1].split("_");
      default_style = "utexas_hero_" + style_and_anchor[0];
      var anchor = ((style_and_anchor[1] !== undefined) ? style_and_anchor[1] : "center");
      $("select[name='anchor_position']").val(anchor);
      return default_style;
    }
    else {
      return default_style[0];
    }
  }

  // Initializing Drupal behavior to create custom selectors.
  Drupal.behaviors.utexasHeroCustomSelectors = {
    attach: function (context, settings) {
      // Initializing variable names based on entity type.
      var entity_type = initializeLibraryWhenValidEntityType();
      // If what entity_type is not node or block, don't proceed.
      if (entity_type === "invalid"){
        return false;
      }
      // Pick a selector to append selectors based on entity type.
      var entity_type_dom = ((entity_type === "node") ? ".js-form-item-settings-formatter-type" : ".js-form-item-settings-view-mode");
      $(entity_type_dom).each(function (index) {
        if ($(this).find('select option[value=utexas_hero_1_left]').length === null) {
          return false;
        }
      });
      // Setting formatter/view mode formatter type based on entity type.
      var formatter_type = ((entity_type === "node") ? "settings[formatter][type]" : "settings[view_mode]");
      // Creating selectors if not already present.
      createSelectors(entity_type);
      // Initializing variables required by selector operations.
      var default_style = $("select[name='" + formatter_type + "']").val();
      // Convert any entity type default value into "default".
      default_style = ((default_style === undefined || default_style === "full" || default_style === "utexas_hero") ? "default" : default_style);
      // If not the default style, retrieve hero style and set anchor value.
      if (default_style !== "default") {
        default_style = splitDefaultStyleAndSetAnchorValue(default_style);
      }
      // Setting default values to view mode/formatter and hero_style fields.
      var formatter_default_style =
      validateNodeEntityAndConvertDefaultToUtexasHero(default_style,
      entity_type);
      $("select[name='" + formatter_type + "']").val(formatter_default_style);
      $("select[name='hero_style']").val(default_style);
      // Select `utexas_hero_x` view mode based on Hero Styles select option.
      $("select[name='hero_style']", context).change(function() {
        var hero_style = "";
        $("select[name='hero_style'] option:selected").each(function() {
          hero_style = $(this).val();
        });
        updateSelectors(entity_type, formatter_type, "", hero_style);
      })
      .trigger("change");
      // Select view mode based on suffix `left`, `right` or `center`.
      $("select[name='anchor_position']", context).change(function() {
        var anchor;
        $("select[name='anchor_position'] option:selected").each(function() {
          anchor = "_" + $(this).val();
        });
        updateSelectors(entity_type, formatter_type, anchor, "");
      })
      .trigger("change");
    }
  };

})(jQuery, Drupal);
