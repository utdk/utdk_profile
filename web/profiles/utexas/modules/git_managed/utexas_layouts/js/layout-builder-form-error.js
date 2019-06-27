  (function ($, Drupal) {
    'use strict';
    Drupal.behaviors.layoutBuilderFormError = {
      attach: function () {
        if ($("#layout-builder-modal form.layout-builder-configure-block div[role = 'alert']").length) {   
          var errorMessage = $("#layout-builder-modal div[data-drupal-messages]").html();
          $("#layout-builder-modal").append('<div id="error-message-bottom">' + errorMessage + '</div>');
        }
        else {
          $("div#error-message-bottom").remove();
        }
      }
    };
  })(jQuery, Drupal);
