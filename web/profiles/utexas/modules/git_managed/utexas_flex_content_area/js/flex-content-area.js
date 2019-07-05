(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.flexContentAreaVideo = {
    attach: function () {
      $('.ut-flex-content-area').each(function () {
        var containerWidth = $(this).find('.image-wrapper').width();
        var ratio = $(this).find('.image-wrapper').attr('data-ratio');
        var newHeight = ratio * containerWidth;
        var wrapper = $(this).find('.image-wrapper');
        wrapper.height(newHeight + "px");
      });
    }
  };
})(jQuery, Drupal);