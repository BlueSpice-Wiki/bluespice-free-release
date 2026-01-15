( function ( mw ) {
	mw.ext.forms.formElement.FieldsetLayout = function () {};

	OO.inheritClass( mw.ext.forms.formElement.FieldsetLayout, mw.ext.forms.formElement.FormLayoutElement );

	mw.ext.forms.formElement.FieldsetLayout.prototype.getElementConfigInternal = function () {
		const config = mw.ext.forms.formElement.BookletLayout.parent.prototype.getElementConfigInternal.call( this );
		config.icon = {
			type: 'text',
			name: 'icon',
			label: mw.message( 'mwstake-formengine-label-icon' ).text()
		};
		config.widget_label = {
			type: 'text',
			name: 'widget_label',
			label: mw.message( 'mwstake-formengine-label-label' ).text()
		};
		config.widget_help = {
			type: 'text',
			name: 'widget_help',
			label: mw.message( 'mwstake-formengine-label-help' ).text()
		};
		config.helpInline = {
			type: 'checkbox',
			value: true,
			name: 'helpInline',
			label: mw.message( 'mwstake-formengine-label-helpinline' ).text()
		};
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.FieldsetLayout.prototype.isSystemElement = function () {
		return true;
	};

	mw.ext.forms.formElement.FieldsetLayout.prototype.getType = function () {
		return 'layout_fieldset';
	};

	mw.ext.forms.formElement.FieldsetLayout.prototype.getWidgets = function () {
		return OO.ui.FieldsetLayout;
	};

	mw.ext.forms.formElement.FieldsetLayout.prototype.getSubitemConfig = function () {
		return {
			propName: 'widget_items',
			accepts: [ 'input', 'static' ]
		};
	};

	mw.ext.forms.registry.Type.register( 'layout_fieldset', new mw.ext.forms.formElement.FieldsetLayout() );

}( mediaWiki ) );
