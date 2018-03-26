### Description of existing patches for this distribution
*********************************************************
#### 1. Fix for margin on form-items in table rows.
Drupal core's Classy theme puts a margin on form items, but not when they're in table rows. When the Paragraphs module is used, and subforms rendered inside use tables, users entering content can see no spacing between fields. This patch modifies the Classy theme CSS to add margins to the top and bottom of those fields. Over time our patch will likely become obsolete.
* Note: Classy theme is the core base theme of the Seven theme which is the base theme for the contrib Adminimal theme.
* See https://www.drupal.org/project/drupal/issues/2675464
* Patch added 3/20/18
