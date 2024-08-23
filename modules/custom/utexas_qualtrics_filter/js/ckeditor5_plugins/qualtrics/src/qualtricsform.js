import {
  View,
  LabeledFieldView,
  createLabeledInputText,
  ButtonView,
  submitHandler
} from 'ckeditor5/src/ui';
import { icons } from 'ckeditor5/src/core';

export default class QualtricsForm extends View {
  constructor(locale) {
    super(locale);

    // Register form inputs.
    this.urlInputView = this._createInput('URL', 'e.g. https://utexas.qualtrics.com/1234');
    this.heightInputView = this._createInput('Height', 'In pixels (e.g., 500). Leave blank for default.');
    this.titleInputView = this._createInput('Title', 'Not visible; used for screen readers. Defaults to "Qualtrics Form"');
    this.saveButtonView = this._createButton('Insert', icons.check, 'ck-button-save');
    // Submit type of the button will trigger the submit event on entire form when clicked
    // (see submitHandler() in render() below).
    this.saveButtonView.type = 'submit';
    this.cancelButtonView = this._createButton('Cancel', icons.cancel, 'ck-button-cancel');

    // Delegate ButtonView#execute to FormView#cancel
    this.cancelButtonView.delegate('execute').to(this, 'cancel');

    this.childViews = this.createCollection([
      this.urlInputView,
      this.heightInputView,
      this.titleInputView,
      this.saveButtonView,
      this.cancelButtonView
    ]);

    this.setTemplate({
      tag: 'form',
      attributes: {
        class: ['ck', 'ck-qualtrics-form'],
        tabindex: '-1'
      },
      children: this.childViews
    });
  }

  render() {
    super.render();
    // Submit the form when the user clicks the save button
    // or presses Enter in the input.
    submitHandler({
      view: this
    });
  }

  focus() {
    this.childViews.first.focus();
  }

  _createInput(label, description) {
    const labeledInput = new LabeledFieldView(this.locale, createLabeledInputText);
    labeledInput.label = label;
    labeledInput.infoText = description;
    return labeledInput;
  }

  _createButton(label, icon, className) {
    const button = new ButtonView();
    button.set({
      label,
      icon,
      tooltip: true,
      class: className
    });
    return button;
  }

  /**
   * Validates the form and returns `false` when some fields are invalid.
   *
   * @returns {Boolean}
   */
  isValid() {
    // Reset error messages before proceeding.
    this.urlInputView.errorText = null;
    this.heightInputView.errorText = null;

    if (!isValidHttpUrl(this.urlInputView.fieldView.element.value)) {
      this.urlInputView.errorText = 'The URL must be a valid Qualtrics link.';
      return false
    }
    if (isNaN(this.heightInputView.fieldView.element.value)) {
      this.heightInputView.errorText = 'The height must be a number.';
      return false;
    }
    return true;
  }

}

/**
 * Is the string a URL?
 */
function isValidHttpUrl(string) {
  let url;
  if (!string.includes('utexas.qualtrics.com')) {
    return false;
  }
  try {
    url = new URL(string);
  } catch (_) {
    return false;
  }
  return url.protocol === "http:" || url.protocol === "https:";
}
