import { Plugin } from 'ckeditor5/src/core';
import { ButtonView, ContextualBalloon, clickOutsideHandler } from 'ckeditor5/src/ui';
import icon from '../theme/icons/qualtrics.svg';
import QualtricsForm from './qualtricsform';
import '../theme/css/form.css';

/**
 * The Qualtrics UI plugin.
 *
 * @extends module:core/plugin~Plugin
 */
export default class Qualtrics extends Plugin {
	static get requires() {
		return [ContextualBalloon];
	}

	init() {
		const editor = this.editor;
		// Create the balloon and the form view.
		this._balloon = editor.plugins.get(ContextualBalloon);
		this.formView = this._createFormView();
		editor.ui.componentFactory.add('qualtrics', () => {
			const button = new ButtonView();
			button.label = 'Qualtrics';
			button.tooltip = true;
			button.withText = true;
			button.icon = icon;

			// Show the UI on button click.
			this.listenTo(button, 'execute', () => {
				this._showUI();
			});

			return button;
		});
	}

	_createFormView() {
		const editor = this.editor;
		const form = new QualtricsForm(editor.locale);

		// Insert content after clicking the "Save" button.
		this.listenTo(form, 'submit', () => {
			if (form.isValid()) {
			  this._generateOutput(form, editor);
			  this._hideUI();
			}
		});

		// Hide the form view after clicking the "Cancel" button.
		this.listenTo(form, 'cancel', () => {
			this._hideUI();
		});

		// Hide the form view when clicking outside the balloon.
		clickOutsideHandler({
			emitter: form,
			activator: () => this._balloon.visibleView === form,
			contextElements: [this._balloon.view.element],
			callback: () => this._hideUI()
		});
		return form;
	}

	_generateOutput(form, editor) {
		// Grab values from input fields.
		const url = form.urlInputView.fieldView.element.value;
		const title = form.titleInputView.fieldView.element.value;
		const height = parseInt(form.heightInputView.fieldView.element.value);

		var shortcode = '[embed]' + url;
		if (height) {
			shortcode += ' | height:' + String(height);
		}
		if (title) {
			shortcode += ' | title:' + title;
		}
		shortcode += '[/embed]';
		const htmlDP = editor.data.processor;
		const viewFragment = htmlDP.toView("<p>" + shortcode + "</p>");
		const modelFragment = editor.data.toModel(viewFragment);
		editor.model.change(writer => {
			editor.model.insertContent(modelFragment);
		});
	}

	_showUI() {
		this._balloon.add({
			view: this.formView,
			position: this._getBalloonPositionData()
		});

		this.formView.focus();
	}

	_hideUI() {
		// Clear the input field values and reset the form.
		this.formView.urlInputView.fieldView.value = '';
		this.formView.heightInputView.fieldView.value = '';
		this.formView.titleInputView.fieldView.value = '';
		this.formView.element.reset();
		this._balloon.remove(this.formView);
		// Focus the editing view after inserting the shortcode
		// so the user can start typing the content
		// right away and keep the editor focused.
		this.editor.editing.view.focus();
	}

	_getBalloonPositionData() {
		const view = this.editor.editing.view;
		const viewDocument = view.document;
		let target = null;
		// Set a target position by converting view selection range to DOM
		target = () => view.domConverter.viewRangeToDom(viewDocument.selection.getFirstRange());

		return {
			target
		};
	}
}
