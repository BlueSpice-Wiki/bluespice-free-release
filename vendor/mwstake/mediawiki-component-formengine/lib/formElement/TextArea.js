( function ( mw ) {
	mw.ext.forms.formElement.TextArea = function () {};

	OO.inheritClass( mw.ext.forms.formElement.TextArea, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.TextArea.prototype.getElementConfig = function () {
		const defaults = mw.ext.forms.formElement.TextArea.parent.prototype.getElementConfigInternal.call( this );
		defaults.rows = {
			type: 'number',
			name: 'rows',
			label: mw.message( 'mwstake-formengine-label-number-of-rows' ).text(),
			value: 2,
			widget_data: {
				tab: 'styling'
			}
		};
		defaults.placeholder = {
			type: 'text',
			name: 'placeholder',
			label: mw.message( 'mwstake-formengine-label-placeholder' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};
		return this.returnConfig( defaults );
	};

	mw.ext.forms.formElement.TextArea.prototype.getType = function () {
		return 'textarea';
	};

	mw.ext.forms.formElement.TextArea.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: OO.ui.MultilineTextInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'textarea', new mw.ext.forms.formElement.TextArea() );
}( mediaWiki ) );
