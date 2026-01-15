( function ( mw ) {
	mw.ext.forms.formElement.FormElement = function () {};

	OO.initClass( mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.FormElement.prototype.getElementConfig = function () {
		return this.returnConfig( this.getElementConfigInternal() );
	};

	mw.ext.forms.formElement.FormElement.prototype.getElementConfigInternal = function () {
		return { // eslint-disable-line mediawiki/class-doc
			// Its not a very good idea to allow changing types
			// at least not without rigid rules
			/* type: {
				type: 'dropdown',
				required: true,
				options: mw.ext.forms.widget.formElement.FormElementGroup.static.getElementPickerOptions(),
				widget_dropdown: {
					$overlay: true
				},
				name: 'type',
				label: mw.message( 'mwstake-formengine-type' ).text(),
				widget_data: {
					tab: 'main'
				}
			}, */
			name: {
				type: 'text',
				required: true,
				name: 'name',
				label: mw.message( 'mwstake-formengine-label-name' ).text(),
				widget_data: {
					tab: 'main'
				}
			},
			label: {
				type: 'text',
				name: 'label',
				label: mw.message( 'mwstake-formengine-label-label' ).text(),
				widget_data: {
					tab: 'main'
				}
			},
			help: {
				type: 'text',
				name: 'help',
				label: mw.message( 'mwstake-formengine-label-help' ).text(),
				widget_data: {
					tab: 'main'
				}
			},
			noLayout: {
				type: 'checkbox',
				name: 'noLayout',
				label: mw.message( 'mwstake-formengine-label-no-layout' ).text(),
				widget_data: {
					tab: 'main'
				}
			},
			classes: {
				type: 'multiplier',
				name: 'widget_classes',
				label: 'CSS Classes',
				returnType: 'simple_array',
				returnKey: 'class',
				base: [ {
					type: 'text',
					name: 'class',
					noLayout: true
				} ],
				widget_data: {
					tab: 'styling'
				}
			},
			showOn: {
				name: 'showOn',
				type: 'checkbox_multiselect',
				widget_classes: [ 'inline-cbx' ],
				label: mw.message( 'mwstake-formengine-label-show-on' ).text(),
				value: [ 'create', 'edit', 'view' ],
				options: [
					{
						data: 'create',
						label: mw.message( 'mwstake-formengine-label-create' ).text()
					}, {
						data: 'edit',
						label: mw.message( 'mwstake-formengine-label-edit' ).text()
					}, {
						data: 'view',
						label: mw.message( 'mwstake-formengine-label-view' ).text()
					}
				],
				widget_data: {
					tab: 'main'
				}
			},
			editableOn: {
				name: 'editableOn',
				type: 'checkbox_multiselect',
				widget_classes: [ 'inline-cbx' ],
				label: 'Editable on',
				value: [ 'create', 'edit' ],
				options: [
					{
						data: 'create',
						label: mw.message( 'mwstake-formengine-label-create' ).text()
					}, {
						data: 'edit',
						label: mw.message( 'mwstake-formengine-label-edit' ).text()
					}
				],
				widget_data: {
					tab: 'main'
				}
			},
			style: {
				type: 'textarea',
				name: 'style',
				label: mw.message( 'mwstake-formengine-label-style' ).text(),
				widget_data: {
					tab: 'styling'
				}
			},
			lbl_custom_props: {
				type: 'label',
				widget_label: mw.message( 'mwstake-formengine-label-custom-widget-properties' ).text(),
				name: 'lbl_custom_props'
			},
			widgetCustomProps: {
				type: 'custom_form_props',
				name: 'widgetCustomProps',
				noLayout: true
			},
			hr_custom_props: {
				type: 'hr',
				noLayout: true
			},
			lbl_listeners: {
				type: 'label',
				widget_label: mw.message( 'mwstake-formengine-label-listeners' ).text(),
				name: 'lbl_listeners'
			},
			widget_listeners: {
				type: 'listeners_widget',
				required: true,
				name: 'widget_listeners',
				noLayout: true
			},
			hr_listeners: {
				type: 'hr',
				noLayout: true
			}
		};
	};

	mw.ext.forms.formElement.FormElement.prototype.getSubitemConfig = function () {
		return false; // Does not support subitems
	};

	mw.ext.forms.formElement.FormElement.prototype.getGroup = function () {
		return 'other';
	};

	mw.ext.forms.formElement.FormElement.prototype.returnConfig = function ( config ) {
		let values = Object.values( config );
		values = values.sort();
		// Name first (if input) listeners last
		values = values.sort( function ( a, b ) {
			if ( !a.hasOwnProperty( 'name' ) ) {
				return 0;
			}
			if ( a.name === 'name' && this instanceof mw.ext.forms.formElement.InputFormElement ) {
				return -1;
			}
			/* if ( a.name === 'listeners' ) {
				return 1;
			}
			if ( a.name === 'widgetCustomProps' ) {
				return 1;
			} */
			return 0;
		} );
		return values;
	};

	mw.ext.forms.formElement.FormElement.prototype.getType = function () {
		// STUB
		return '_default';
	};

	mw.ext.forms.formElement.FormElement.prototype.getDisplayName = function () {
		// * mwstake-formengine-type-text
		// * mwstake-formengine-type-button
		// * mwstake-formengine-type-checkbox
		// * mwstake-formengine-type-checkbox_multiselect
		// * mwstake-formengine-type-dropdown
		// * mwstake-formengine-type-icon
		// * mwstake-formengine-type-indicator
		// * mwstake-formengine-type-label
		// * mwstake-formengine-type-multi_step
		// * mwstake-formengine-type-number
		// * mwstake-formengine-type-progress_bar
		// * mwstake-formengine-type-radio
		// * mwstake-formengine-type-radio_multiselect
		// * mwstake-formengine-type-section_label
		// * mwstake-formengine-type-select_file
		// * mwstake-formengine-type-textarea
		// * mwstake-formengine-type-layout_horizontal
		// * mwstake-formengine-type-layout_booklet
		// * mwstake-formengine-type-layout_index
		// * mwstake-formengine-type-form_import
		// * mwstake-formengine-type-multiplier
		// * mwstake-formengine-type-menutag_multiselect
		// * mwstake-formengine-type-category_multiselect
		// * mwstake-formengine-type-layout_fieldset
		// * mwstake-formengine-type-title
		return mw.message( 'mwstake-formengine-type-' + this.getType() ).text();
	};

	mw.ext.forms.formElement.FormElement.prototype.isInput = function () {
		return this instanceof mw.ext.forms.formElement.InputFormElement;
	};

	mw.ext.forms.formElement.FormElement.prototype.getWidgets = function () {
		return null;
	};

	mw.ext.forms.formElement.FormElement.prototype.isSystemElement = function () {
		return false;
	};

	mw.ext.forms.formElement.FormElement.prototype.isHidden = function () {
		return false;
	};

	mw.ext.forms.formElement.FormElement.prototype.getIcon = function () {
		return '';
	};

	/**
	 * True for any element that can only be a subitem of another element that accepts it
	 *
	 * @return {boolean}
	 */
	mw.ext.forms.formElement.FormElement.prototype.isSubitem = function () {
		return false;
	};

	mw.ext.forms.formElement.FormElement.prototype.getDefaultValue = function () {
		return {};
	};

}( mediaWiki ) );
