( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.ProgressBar = function() {};

	OO.inheritClass( mw.ext.forms.formElement.ProgressBar, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.ProgressBar.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.ProgressBar.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		config.noLayout.hidden = true;
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.ProgressBar.prototype.getType = function() {
		return "progress_bar";
	};

	mw.ext.forms.formElement.ProgressBar.prototype.getWidgets = function() {
		return mw.ext.forms.widget.edit.ProgressBar;
	};

	mw.ext.forms.registry.Type.register( "progress_bar", new mw.ext.forms.formElement.ProgressBar() );
} )( mediaWiki, jQuery );
