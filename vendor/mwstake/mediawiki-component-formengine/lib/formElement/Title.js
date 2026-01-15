( function ( mw ) {
	mw.ext.forms.formElement.Title = function () {};

	OO.inheritClass( mw.ext.forms.formElement.Title, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Title.prototype.getElementConfig = function () {
		const defaults = mw.ext.forms.formElement.Title.parent.prototype.getElementConfigInternal.call( this );
		return this.returnConfig( defaults );
	};

	mw.ext.forms.formElement.Title.prototype.getType = function () {
		return 'title';
	};

	mw.ext.forms.formElement.Title.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: mw.widgets.TitleInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'title', new mw.ext.forms.formElement.Title() );
}( mediaWiki ) );
