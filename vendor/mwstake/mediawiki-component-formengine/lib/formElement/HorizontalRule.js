( function ( mw ) {
	mw.ext.forms.formElement.HorizontalRule = function () {};

	OO.inheritClass( mw.ext.forms.formElement.HorizontalRule, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.HorizontalRule.prototype.getElementConfig = function () {
		return this.returnConfig( {} );
	};

	mw.ext.forms.formElement.HorizontalRule.prototype.getType = function () {
		return 'hr';
	};

	mw.ext.forms.formElement.HorizontalRule.prototype.getWidgets = function () {
		return mw.ext.forms.widget.view.HorizontalRule;
	};

	mw.ext.forms.registry.Type.register( 'hr', new mw.ext.forms.formElement.HorizontalRule() );
}( mediaWiki ) );
