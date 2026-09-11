#!/bin/bash

set -e

# Install
# Install node
# npm -y install -D @playwright/test@latest
# npx -y playwright install --with-deps

# Verify Drupal bootstrap status
bootstrap=$(lando drush  status --field=bootstrap)
if [ "$bootstrap" != "Successful" ]; then
  echo "Bootstrap failed. Exiting..."
  exit 1
fi
echo "Drupal bootstrap successful."

# Create site manager and content editor users, and assign roles
site_manager=$(lando drush  user:information site-manager --field=uid 2>/dev/null) || true;
site_manager_perms=$(lando drush php:eval 'echo implode(",",Drupal\utexas\Permissions::$manager);')
content_editor=$(lando drush user:information content-editor --field=uid 2>/dev/null) || true;
content_editor_perms=$(lando drush  php:eval 'echo implode(",",Drupal\utexas\Permissions::$editor);')
if [ -z "$site_manager" ]; then
  echo "Creating site manager user..."
  lando drush  user:create site-manager --mail='site-manager@example.com' --password='test'
  lando drush  user:role:add 'utexas_site_manager' site-manager
  lando drush  user:role:add 'utexas_content_editor' site-manager
  lando drush  role:perm:add 'utexas_site_manager' "$site_manager_perms" || true;
  # TODO: add perms
  # administer content types
  # administer image styles
  # administer block types
  # administer block_content fields
  # administer media fields
  # administer media display
  # administer media form display
fi
if [ -z "$content_editor" ]; then
  echo "Creating content editor user..."
  lando drush  user:create content-editor --mail='content-editor@example.com' --password='test'
  lando drush  user:role:add 'utexas_content_editor' content-editor
  lando drush  role:perm:add 'utexas_content_editor' "$content_editor_perms" || true;
fi

# Enable modules needed for functional testing
echo "Enabling modules for testing..."
lando drush en utexas_devel -y
lando drush en views_ui -y
lando drush en field_ui -y

# Suppress image token output for testing
lando drush cset image.settings suppress_itok_output 1 -y > /dev/null
lando drush cr

# Run Playwright tests
npx playwright test
