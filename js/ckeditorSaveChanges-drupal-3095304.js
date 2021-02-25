/**
 * Fixes issue when attempting to save while in CKEditor source mode.
 * This implementation is copied verbatim from https://www.drupal.org/project/drupal/issues/3095304#comment-13745451
 * Once the issue is resolved in Drupal core, this should be removed.
 */
var origBeforeSubmit = Drupal.Ajax.prototype.beforeSubmit;
Drupal.Ajax.prototype.beforeSubmit = function (formValues, element, options) {
  if (typeof (CKEDITOR) !== 'undefined' && CKEDITOR.instances) {
    const instances = Object.values(CKEDITOR.instances);
    instances.forEach(editor => {
      formValues.forEach(formField => {
        // Get field name from the id in the editor so that it covers all
        // fields using ckeditor.
        let element = document.querySelector(`#${editor.name}`)
        if (element) {
          let fieldName = element.getAttribute('name');
          if (formField.name === fieldName && editor.mode === 'source') {
            formField.value = editor.getData();
          }
        }
      });
    });
  }
  origBeforeSubmit.call(formValues, element, options);
};
