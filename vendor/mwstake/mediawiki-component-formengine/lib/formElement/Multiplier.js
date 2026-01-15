( function ( mw ) {
	mw.ext.forms.formElement.Multiplier = function () {};

	OO.inheritClass( mw.ext.forms.formElement.Multiplier, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Multiplier.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.Multiplier.parent.prototype.getElementConfigInternal.call( this );
		config.addInitially = {
			type: 'checkbox',
			name: 'addInitially',
			value: true,
			label: mw.message( 'mwstake-formengine-label-add-first' ).text()
		};
		config.max = {
			type: 'number',
			name: 'max',
			min: 1,
			max: 100,
			value: 5,
			label: mw.message( 'mwstake-formengine-label-max-number-to-add' ).text()
		};
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Multiplier.prototype.isSystemElement = function () {
		return true;
	};

	mw.ext.forms.formElement.Multiplier.prototype.getType = function () {
		return 'multiplier';
	};

	mw.ext.forms.formElement.Multiplier.prototype.getWidgets = function () {
		return {
			edit: mw.ext.forms.widget.edit.Multiplier,
			view: mw.ext.forms.widget.view.Multiplier
		};
	};

	mw.ext.forms.formElement.Multiplier.prototype.getSubitemConfig = function () {
		return {
			propName: 'base',
			accepts: [ 'input', 'static' ]
		};
	};

	mw.ext.forms.registry.Type.register( 'multiplier', new mw.ext.forms.formElement.Multiplier() );

}( mediaWiki ) );
