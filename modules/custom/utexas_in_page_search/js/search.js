/**
 * @file
 * Low-fi in-page search.
 *
 */

Drupal.behaviors.inPageSearch = {
  attach: function (context, drupalSettings) {
    window.onload = function () {
      const searchBox = document.getElementById("inPageSearchBox");
      if (searchBox !== null) {
        searchBox.addEventListener('keyup', function (event) {
          if (event.key != 13) {
            inPageSearch();
          }
        });
        searchBox.addEventListener('keypress', function (event) {
          if (event.key == 13) {
            event.preventDefault();
            inPageSearch();
          }
        });
        const searchButton = document.getElementById("inPageSearchButton");
        searchButton.addEventListener('click', function (event) {
          event.preventDefault();
          inPageSearch();
        });
      }
    }
    function inPageSearch() {
      if (drupalSettings.in_page_search == null) {
        return false;
      }
      const selector = '#' + drupalSettings.in_page_search.target + ' ' + drupalSettings.in_page_search.delimiter;
      const container = document.querySelectorAll(selector);
      if (container == null) {
        return false;
      }
      const keyword = document.getElementById("inPageSearchInput");
      if (keyword.value == null) {
        return false;
      }
      for (var item of container) {
        if (item.matches(drupalSettings.in_page_search.delimiter)) {
          if (!item.getHTML().toLowerCase().includes(keyword.value.toLowerCase())) {
            item.style.display = 'none';
          }
          else {
            item.style.display = '';
          }
        }
      }
    }
  }
};
