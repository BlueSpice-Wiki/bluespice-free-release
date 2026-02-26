( function ( mw ) {
	mw.ext.forms.formElement.EnhancedTitle = function () {};

	OO.inheritClass( mw.ext.forms.formElement.EnhancedTitle, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.EnhancedTitle.prototype.getElementConfig = function () {
		const defaults = mw.ext.forms.formElement.Title.parent.prototype.getElementConfigInternal.call( this );
		return this.returnConfig( defaults );
	};

	mw.ext.forms.formElement.EnhancedTitle.prototype.getType = function () {
		return 'title';
	};

	mw.ext.forms.formElement.EnhancedTitle.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: mw.ext.forms.widget.edit.TitleOrWikitextInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'enhanced-title', new mw.ext.forms.formElement.EnhancedTitle() );
}( mediaWiki ) );
