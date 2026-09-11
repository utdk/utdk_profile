#!/bin/bash

set -e

# Verify Drupal bootstrap status

bootstrap=$(lando drush  status --field=bootstrap)
if [ "$bootstrap" != "Successful" ]; then
  echo "Bootstrap failed. Exiting..."
  exit 1
fi
echo "Drupal bootstrap successful."

# Tests

verify-modules-enabled() {
  local modules=(
    'utexas_block_social_links'
    'utexas_content_type_flex_page'
    'utexas_role_content_editor'
    'utexas_role_site_manager'
    'block'
  )
  for module in "${modules[@]}"; do
    local enabled=$(lando drush  pm:list --status=enabled --type=module --filter=name="$module" --format=string)
    if [ -z "$enabled" ]; then
      echo "ERROR: Module $module is not enabled"
      return 1
    fi
  done
  return 0
}

verify-modules-disabled() {
  local modules=(
    'utexas_devel'
  )
  for module in "${modules[@]}"; do
    local disabled=$(lando drush  pm:list --status=disabled --type=module --filter=name="$module" --format=string)
    if [ -z "$disabled" ]; then
      echo "Module $module is not disabled"
      return 1
    fi
  done
  return 0
}

verify-active-theme() {
  local default_theme=$(lando drush  config:get system.theme default | grep speedway)
  if [ -z "$default_theme" ]; then
    echo "Active default theme is not speedway"
    return 1
  fi
  return 0
}

verify-flex-html-ckeditor-toolbar() {
  local ckeditor_toolbar_actual=$(lando drush  config:get editor.editor.flex_html settings.toolbar.items --format=string | tr -d '[:space:]')
  local ckeditor_toolbar_expected=$(echo 'bold italic strikethrough horizontalLine removeFormat undo redo | link | bulletedList numberedList outdent indent alignment | insertTable drupalMedia urlembed blockQuote heading style | specialCharacters subscript superscript underline sourceEditing -' | tr -d '[:space:]')
  if [ "$ckeditor_toolbar_actual" != "$ckeditor_toolbar_expected" ]; then
    echo "Flex HTML CKEditor toolbar does not match the expected configuration."
    return 1
  fi
  return 0
}

verify-flex-html-allowed-html() {
  local allowed_html_expected="<a href hreflang class id name role title aria-controls aria-haspopup aria-label aria-expanded aria-selected data-* media rel target> <abbr title class id role> <address class id role> <article class id role> <aside class id role> <audio class id role autoplay buffered controls loop muted preload src volume> <blockquote class id role> <br class id role> <button type class id role aria-label aria-expanded aria-controls aria-haspopup data-* title> <caption class id role> <cite title class id role> <code class id role> <col class id role> <colgroup class id role> <del class id role> <details class id role> <dl class id role> <dt class id role> <dd class id role> <div role class id aria-label aria-labelledby aria-hidden data-* tabindex> <drupal-url data-*> <drupal-media data-* alt title> <em class id role> <figure class id role> <figcaption class id role> <footer class id role> <header class id role> <hr class id role> <h1 class id role> <h2 class id role> <h3 class id role> <h4 class id role> <h5 class id role> <h6 class id role> <img alt height width align class id role src data-* title> <i class id role> <li role class id aria-controls aria-current data-*> <mark class id role> <nav class id role aria-label> <ol class id role aria-labelledby start type> <p class id role> <pre class id role> <q class id cite> <rowspan class id role> <s class id role> <section class id role> <small class id role> <span class id role aria-hidden> <source src type> <strike class id role> <strong class id role> <sub class id role> <summary class id role> <sup class id role> <table border class id role title> <tbody class id role> <td class id role colspan rowspan headers title> <tfoot class id role> <th colspan rowspan headers scope class id role> <thead class id role> <time class id role> <tr class id role> <track src sclang label default> <u class id role> <ul class id role background bgcolor aria-labelledby> <video width height controls autoplay buffered loop muted playsinline poster preload src>"
  local allowed_html_actual=$(lando drush  config:get filter.format.flex_html filters.filter_html.settings.allowed_html --format=string)
  if [ "$allowed_html_actual" != "$allowed_html_expected" ]; then
    echo "Flex HTML allowed HTML does not match the expected configuration."
    return 1
  fi
  return 0
}

verify-flex-html-filters-disabled() {
  local filters=("filters.filter_autop.status")
  for filter in "${filters[@]}"; do
    local filter_status=$(lando drush  config:get filter.format.flex_html "$filter" --format=string  2>/dev/null)
    if [ -n "$filter_status" ]; then
      echo "Filter $filter is enabled"
      return 1
    fi
  done
  return 0
}

verify-flex-html-filters-enabled() {
  local filters=("filters.media_embed.status" "filters.filter_url.status" "filters.filter_iframe_title.status" "filters.filter_htmlcorrector.status" "filters.linkit.status" "filters.filter_responsive_tables_filter.status" "filters.filter_pathologic.status" "filters.filter_url.status" "filters.filter_qualtrics.status")
  for filter in "${filters[@]}"; do
    local filter_status=$(lando drush  config:get filter.format.flex_html "$filter" --format=string  2>/dev/null)
    if [ "$filter_status" != "1" ]; then
      echo "Filter $filter is not enabled"
      return 1
    fi
  done
  return 0
}

verify-flex-html-is-first() {
  local first_filter=$(lando drush  config:get "filters.filter_autop.status" --format=string)
  if [ "$first_filter" != "1" ]; then
    echo "Flex HTML filter is not the first filter."
    return 1
  fi
  return 0
}

verify-text-formats() {
  local restricted=$(lando drush  config:get filter.format.restricted_html format --format=string)
  local full_html=$(lando drush  config:get editor.editor.full_html format --format=string)
  local basic_html=$(lando drush  config:get filter.format.basic_html format --format=string)
  if [ "$restricted" != "restricted_html" ]; then
    echo "Restricted HTML format is not configured correctly."
    return 1
  elif [ "$full_html" != "full_html" ]; then
    echo "Full HTML format is not configured correctly."
    return 1
  elif [ "$basic_html" != "basic_html" ]; then
    echo "Basic HTML format is not configured correctly."
    return 1
  fi
  return 0
}

verify-text-formats-content-editor-access() {
  # Flex html
  local access=$(lando drush  config:get filter.format.flex_html roles.content_editor --format=string)
  if [ "$access" != "1" ]; then
    echo "Content editor does not have access to Flex HTML format."
    return 1
  fi

  # Full HTML
  local access=$(lando drush  config:get editor.editor.full_html roles.content_editor --format=string)
  if [ "$access" == "1" ]; then
    echo "Content editor does have access to full HTML format."
    return 1
  fi
  return 0
}

verify-default-language() {
   local language=$(lando drush  config:get system.site langcode --format=string)
   local default_language=$(lando drush  config:get system.site default_langcode --format=string)
   if [ "$language" != "en" ]; then
     echo "Language code is not set to english."
     return 1
   elif [ "$default_language" != "en" ]; then
     echo "Default language code is not english."
     return 1
   fi
   return 0
}

verify-default-metatags() {
  local metatags=(
    "canonical_url=[current-page:url]"
    "og_title=[current-page:title]"
    "og_type=website"
    "og_updated_time=[node:changed:custom:c]"
    "og_url=[current-page:url]"
    "title=[current-page:title] | [site:name]"
    "twitter_cards_type=summary"
    "twitter_cards_title=[current-page:title]"
  )
  for entry in "${metatags[@]}"; do
    local key="${entry%%=*}"
    local expected_value="${entry#*=}"
    local value=$(lando drush  config:get metatag.metatag_defaults.global tags."$key" --format=string)
    if [ "$value" != "$expected_value" ]; then
      echo "Metatag $key is not configured correctly. Expected: $expected_value, Got: $value"
      return 1
    fi
  done
  return 0
}

verify-locked-fields() {
  locked_field_storage=(
    'block_content.field_utexas_sl_social_links',
    'block_content.field_utexas_call_to_action_link',
    'node.field_flex_page_metatags',
    'block_content.field_block_featured_highlight',
    'block_content.field_block_fca',
    'block_content.field_block_hero',
    'block_content.field_utexas_flex_list_items',
    'block_content.field_block_il',
    'media.field_media_file',
    'media.field_media_oembed_video',
    'media.field_utexas_media_image',
    'block_content.field_block_pca',
    'block_content.field_block_pl',
    'block_content.field_block_pu',
    'block_content.field_block_ql',
    'block_content.field_block_resources',
  )
  for field_storage in "${locked_field_storage[@]}"; do
    local locked=$(lando drush  config:get "field.storage.$field_storage" locked --format=string)
    if [ -n "$locked" ]; then
      echo "Field $field_storage is locked."
      return 1
    fi
  done
  return 0
}

# Run tests

echo "Starting base installation tests..."

echo "Verifying modules enabled..."
verify-modules-enabled

echo "Verifying modules disabled..."
verify-modules-disabled

echo "Verifying default theme..."
verify-active-theme

echo "Verifying Flex HTML CKEditor toolbar..."
verify-flex-html-ckeditor-toolbar

echo "Verifying Flex HTML allowed HTML..."
verify-flex-html-allowed-html

echo "Verifying Flex HTML disabled filter..."
verify-flex-html-filters-disabled

echo "Verifying Flex HTML enabled filter..."
verify-flex-html-filters-enabled

# echo "Verifying Flex HTML is first filter..."
# verify-flex-html-is-first

echo "Verifying text formats..."
verify-text-formats

#echo "Verifying text formats content editor access..."
#verify-text-formats-content-editor-access

echo "Verifying default language..."
verify-default-language

echo "Verifying default metatags..."
verify-default-metatags

echo "Verifying locked fields..."
verify-locked-fields

echo "Base installation tests completed..."
