( function ( mw ) {
	mw.ext.forms.formElement.SelectFile = function () {};

	OO.inheritClass( mw.ext.forms.formElement.SelectFile, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.SelectFile.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.SelectFile.parent.prototype.getElementConfigInternal.call( this );
		config.showDropTarget = {
			type: 'checkbox',
			value: true,
			name: 'showDropTarget',
			label: mw.message( 'mwstake-formengine-label-show-drop-target' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.SelectFile.prototype.getType = function () {
		return 'select_file';
	};

	mw.ext.forms.formElement.SelectFile.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.SingleImageView,
			edit: mw.ext.forms.widget.edit.SelectFileInput
		};
	};

	mw.ext.forms.registry.Type.register( 'select_file', new mw.ext.forms.formElement.SelectFile() );
}( mediaWiki, jQuery ) );
