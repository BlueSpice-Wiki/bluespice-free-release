( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Label = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Label, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.Label.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.Label.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		config.label.name = 'title';

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Label.prototype.getType = function() {
		return "label";
	};

	mw.ext.forms.formElement.Label.prototype.getWidgets = function() {
		return mw.ext.forms.widget.view.LabelView;
	};

	mw.ext.forms.formElement.Label.prototype.getDefaultValue = function() {
		return {
			title: mw.msg( 'mwstake-formengine-type-label' )
		};
	};

	mw.ext.forms.registry.Type.register( "label", new mw.ext.forms.formElement.Label() );
} )( mediaWiki, jQuery );