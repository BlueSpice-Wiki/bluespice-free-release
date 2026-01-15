( function ( mw ) {
	mw.ext.forms.formElement.Password = function () {};

	OO.inheritClass( mw.ext.forms.formElement.Password, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Password.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.Password.parent.prototype.getElementConfigInternal.call( this );

		config.strength = {
			type: 'dropdown',
			name: 'strength',
			label: mw.message( 'mwstake-formengine-label-input-password-strength' ).text(),
			options: [
				{ data: 'weak' },
				{ data: 'medium' },
				{ data: 'strong' }
			]
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Password.prototype.getType = function () {
		return 'password';
	};

	mw.ext.forms.formElement.Password.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.PasswordView,
			edit: mw.ext.forms.widget.edit.Pasword
		};
	};

	mw.ext.forms.registry.Type.register( 'password', new mw.ext.forms.formElement.Password() );

}( mediaWiki ) );
