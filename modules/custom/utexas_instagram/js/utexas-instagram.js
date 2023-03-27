(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.utexasInstagram = {
    attach: function (context, settings) {

      $(once('instagramFeed', '.utexas-instagram-feed', context)).each(function () {
        var
          $item = $('.utexas-instagram-feed__list-item'),
          visible = 1,
          index = 0,
          endIndex = ($item.length / visible) - 3,
          mobileIndex = ($item.length / visible) - 1;

        const
          $backButton = $('.utexas-instagram-feed__controls-prev');
          $forwardButton = $('.utexas-instagram-feed__controls-next'),
          enabledClass = 'js-enabled';

        $forwardButton.on('keydown click', function (e) {
          // If key was pressed, ignore all but spacebar and enter/return.
          if (e.keyCode && (e.keyCode) !== 32 && (e.keyCode !== 13)) {
            return;
          }
          // Prevent default 'keydown' and 'click' behavior.
          e.preventDefault();

          if (index < endIndex) {
            index++;
            $item.animate({ 'left': '-=35%' });
          }
          if (index < endIndex) {
            $(this).addClass(enabledClass);
          } else {
            $(this).removeClass(enabledClass);
          }
          if (index > 0) {
            $backButton.addClass(enabledClass);
          } else {
            $backButton.removeClass(enabledClass);
          }
        });

        $backButton.on('keydown click', function (e) {
          // If key was pressed, ignore all but spacebar and enter/return.
          if (e.keyCode && (e.keyCode) !== 32 && (e.keyCode !== 13)) {
            return;
          }
          // Prevent default 'keydown' and 'click' behavior.
          e.preventDefault();

          if (index > 0) {
            index--;
            $item.animate({ 'left': '+=35%' });
          }
          if (index > 0) {
            $(this).addClass(enabledClass);
          } else {
            $(this).removeClass(enabledClass);
          }
          if (index < endIndex) {
            $forwardButton.addClass(enabledClass);
          } else {
            $forwardButton.removeClass(enabledClass);
          }
        });

        $item.on('swiped-left', function (e) {
          if ($(window).width() < 651 && index < mobileIndex) {
            index++;
            $item.animate({ 'left': '-=100%' });
          }
        });

        $item.on('swiped-right', function (e) {
          if ($(window).width() < 651 && index > 0) {
            index--;
            $item.animate({ 'left': '+=100%' });
          }
        });
      });
    }
  };

})(jQuery, Drupal, this, this.document);
