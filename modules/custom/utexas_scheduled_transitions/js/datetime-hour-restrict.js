/**
 * @file
 * Client-side behavior to restrict datetime inputs to top-of-hour.
 */

(function(Drupal, $) {
  "use strict";

  Drupal.behaviors.datetimeHourRestrict = {
    attach(context) {
      /**
       * Restrict time input to top of the hour (00 minutes).
       *
       * The time input format is HH:mm or HH:mm:ss.
       * Normalizes to HH:00 or HH:00:00 respectively.
       *
       * @param {jQuery} $input - The time input jQuery object.
       */
      const restrictToHour = $input => {
        const value = $input.val();

        if (!value) {
          return;
        }

        // Time format is HH:mm or HH:mm:ss. Match it and extract the hour.
        const match = value.match(/^(\d{2}):\d{2}(?::\d{2})?$/);

        if (match) {
          // Reconstruct with :00 for minutes (and :00 for seconds if present).
          // Format: HH:mm or HH:mm:ss → HH:00 or HH:00:00
          const restrictedValue =
            value.includes(":") && value.split(":").length === 3
              ? `${match[1]}:00:00`
              : `${match[1]}:00`;

          if (value !== restrictedValue) {
            $input.val(restrictedValue);
            // Trigger change event so form knows the value changed.
            $input.trigger("change");
          }
        }
      };

      // Look for the time inputs that should be restricted.
      // Match: on[time] (add form) and date[time] (reschedule form).
      const $timeInput = $(
        'input[name="on[time]"], input[name="date[time]"]',
        context
      );

      if ($timeInput.length === 0) {
        return;
      }

      $timeInput.each(function() {
        const $input = $(this);

        // Set step attribute to 3600 (1 hour).
        $input.attr("step", "3600");
        $input.attr("data-hour-only", "true");

        // Bind events for real-time normalization.
        $input.on("change", function() {
          restrictToHour($input);
        });

        $input.on("blur", function() {
          restrictToHour($input);
        });

        $input.on("input", function() {
          restrictToHour($input);
        });

        $input.on("keyup", function() {
          restrictToHour($input);
        });

        $input.on("paste", function() {
          const self = this;
          setTimeout(function() {
            restrictToHour($(self));
          }, 50);
        });
      });
    }
  };
})(Drupal, jQuery);
