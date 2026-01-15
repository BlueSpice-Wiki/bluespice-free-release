( function ( mw ) {
	mw.ext.forms.formElement.SectionLabel = function () {};

	OO.inheritClass( mw.ext.forms.formElement.SectionLabel, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.SectionLabel.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.SectionLabel.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		config.noLayout.hidden = true;

		delete ( config.label );
		config.title = {
			type: 'text',
			name: 'title',
			label: mw.message( 'mwstake-formengine-label-title' ).text(),
			widget_data: {
				tab: 'main'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.SectionLabel.prototype.getType = function () {
		return 'section_label';
	};

	mw.ext.forms.formElement.SectionLabel.prototype.getWidgets = function () {
		return mw.ext.forms.widget.view.SectionLabel;
	};

	mw.ext.forms.formElement.SectionLabel.prototype.getDefaultValue = function () {
		return {
			title: mw.msg( 'mwstake-formengine-type-section_label' )
		};
	};

	mw.ext.forms.registry.Type.register( 'section_label', new mw.ext.forms.formElement.SectionLabel() );
}( mediaWiki ) );
