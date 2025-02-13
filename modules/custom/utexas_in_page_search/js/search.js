/**
 * @file
 * Low-fi in-page search.
 *
 */

Drupal.behaviors.inPageSearch = {
  attach: ((context, drupalSettings) => {
    function inPageSearch() {
      if (drupalSettings.in_page_search == null) {
        return false;
      }
      const selector =
      "#" +
      drupalSettings.in_page_search.target +
       " "
       + drupalSettings.in_page_search.delimiter;
      const container = document.querySelectorAll(selector);
      if (container == null) {
        return false;
      }
      const keyword = document.getElementById("inPageSearchInput");
      if (keyword.value == null) {
        return false;
      }
      const noResults = document.getElementById("inPageSearchNoResults");
      let noResultsCount = 0;
      for (let item of container) {
        if (item.matches(drupalSettings.in_page_search.delimiter)) {
          if (!item.getHTML().toLowerCase().includes(keyword.value.toLowerCase())) {
            item.style.display = "none";
            noResultsCount++;
          }
          else {
            item.style.display = "";
          }
        }
      }
      if (noResultsCount === container.length) {
        noResults.style.display = "block";
      }
      else {
        noResults.style.display = "none";
      }
    }
    window.onload = (() => {
      const searchBox = document.getElementById("inPageSearchBox");
      if (searchBox !== null) {
        searchBox.addEventListener("keyup", (event => {
          if (event.key !== 13) {
            inPageSearch();
          }
        }))();
        searchBox.addEventListener("keypress", (event => {
          if (event.key == 13) {
            inPageSearch();
          }
        }))();
        const searchField = document.getElementById("inPageSearchInput");
        const params = new URLSearchParams(document.location.search);
        const keyword = params.get("keyword");
        if (keyword) {
          searchField.value = keyword;
          inPageSearch();
        }
        const searchResetButton = document.getElementById("inPageSearchResetButton");
        searchResetButton.addEventListener("click", (() => {
          window.location = window.location.pathname;
        }))();
      }
    })();
  })(),
};
