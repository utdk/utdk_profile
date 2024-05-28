/**
 * @file
 * Provides Slick loader.
 */

 (function ($, Drupal, drupalSettings) {

    'use strict';

    /**
     * Attaches slick behavior to HTML element identified by CSS selector .slick.
     *
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.utexasHeroCarousel = {
      attach: function (context) {

        $('.utexas-hero-carousel', context).each(function(el) {
          var id = $(this).attr('id');
          var carouselOptions = drupalSettings['utexas_hero_carousel'][id];
          // We always set "autoplay" to true so that the play/pause button is
          // always visible.
          $(this).slick({
            autoplay: true,
            autoplaySpeed: parseFloat(carouselOptions['autoplaySpeed']) * 1000,
            dots: Boolean( parseInt(carouselOptions['dots'])),
            fade: Boolean( parseInt(carouselOptions['fade'])),
            slidesToScroll: parseInt(carouselOptions['slidesToScroll']),
            slidesToShow: parseInt(carouselOptions['slidesToShow']),
          });
          var layoutBuilderContainer = $(this).parents(".layout-builder__layout");
          // If the carousel does not have autoplay enabled, pause the carousel
          // and set the button display to "Play".
          if (carouselOptions['autoplay'] == 0 || layoutBuilderContainer.length > 0) {
            $(this).slick('slickSetOption', 'autoplay', true).slick('slickPause');
            $(this).find('.slick-pause-icon').attr('style', 'display: none');
            $(this).find('.slick-play-icon').removeAttr('style');
            $(this).find('.slick-pause-text').attr('style', 'display: none');
            $(this).find('.slick-play-text').removeAttr('style');
          }
        });


      }
    };

  })(jQuery, Drupal, drupalSettings);
