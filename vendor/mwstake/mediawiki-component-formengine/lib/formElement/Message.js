( function ( mw ) {
	mw.ext.forms.formElement.Message = function () {};

	OO.inheritClass( mw.ext.forms.formElement.Message, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.Message.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.Message.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		config.label.name = 'title';
		config.icon = {
			type: 'text',
			name: 'icon',
			label: mw.message( 'mwstake-formengine-label-icon' ).text(),
			widget_data: {
				tab: 'main'
			}
		};
		config.widget_type = {
			type: 'dropdown',
			name: 'widget_type',
			options: [
				{ data: 'notice' },
				{ data: 'error' },
				{ data: 'warning' },
				{ data: 'success' }
			],
			label: mw.message( 'mwstake-formengine-label-icon' ).text(),
			widget_data: {
				tab: 'main'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Message.prototype.getType = function () {
		return 'message';
	};

	mw.ext.forms.formElement.Message.prototype.getWidgets = function () {
		return mw.ext.forms.widget.view.MessageView;
	};

	mw.ext.forms.formElement.Message.prototype.getDefaultValue = function () {
		return {
			title: mw.msg( 'mwstake-formengine-type-message' )
		};
	};

	mw.ext.forms.registry.Type.register( 'message', new mw.ext.forms.formElement.Message() );
}( mediaWiki ) );
