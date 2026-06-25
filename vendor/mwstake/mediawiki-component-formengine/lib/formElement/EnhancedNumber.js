( function ( mw ) {
	mw.ext.forms.formElement.EnhancedNumber = function () {};

	OO.inheritClass( mw.ext.forms.formElement.EnhancedNumber, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.EnhancedNumber.prototype.getElementConfig = function () {
		const defaults = mw.ext.forms.formElement.Title.parent.prototype.getElementConfigInternal.call( this );
		return this.returnConfig( defaults );
	};

	mw.ext.forms.formElement.EnhancedNumber.prototype.getType = function () {
		return 'number';
	};

	mw.ext.forms.formElement.EnhancedNumber.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: mw.ext.forms.widget.edit.NumberOrWikitextInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'enhanced-number', new mw.ext.forms.formElement.EnhancedNumber() );
}( mediaWiki ) );
