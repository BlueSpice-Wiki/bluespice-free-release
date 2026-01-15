( function ( mw, $, undefined ) {
	mw.ext.forms.mixin.FloatableButtons = function() {

		this.submitButton = new OO.ui.ButtonWidget( {
			label: this.buttons.hasOwnProperty( 'submit' ) ?
				this.buttons.submit.label : mw.message( 'mwstake-formengine-form-submit-label' ).text(),
			flags: [
				"progressive",
				"primary"
			],
			id: 'formSubmit'
		} );

		if ( this.enableProgressSave ) {
			this.submitButton.setLabel( this.buttons.hasOwnProperty( 'submit' ) ?
				this.buttons.submit.label : mw.message( 'mwstake-formengine-inclusion-form-submit-ps-enabled-label' ).text() );
			this.progressSaveButton = new OO.ui.ButtonWidget( {
				label: mw.message( 'mwstake-formengine-form-save-label' ).text(),
				flags: [
					"progressive",
					"primary"
				]
			} );
		}
		this.resetButton = new OO.ui.ButtonWidget( {
			label: this.buttons.hasOwnProperty( 'reset' ) ?
				this.buttons.submit.label : mw.message( 'mwstake-formengine-form-reset-label' ).text(),
			framed: false
		} );

		this.cancelButton = new OO.ui.ButtonWidget( {
			label: this.buttons.hasOwnProperty( 'cancel' ) ?
				this.buttons.submit.label : mw.message( 'mwstake-formengine-form-cancel-label' ).text(),
			framed: false
		} );

		var classes = [ 'ext-mw-forms-form-buttons' ];
		if ( this.buttonsFloat ) {
			classes.push( 'float-buttons' );
		}

		var items = [];
		if ( this.enableProgressSave ) {
			items.push( this.progressSaveButton );
		}
		if ( this.buttonsToShow.indexOf( 'submit' ) !== -1 ) {
			items.push( this.submitButton );
		}
		if ( this.buttonsToShow.indexOf( 'reset' ) !== -1 ) {
			items.push( this.resetButton );
		}
		if ( this.buttonsToShow.indexOf( 'cancel' ) !== -1 ) {
			items.push( this.cancelButton );
		}
		this.buttonsLayout = new OO.ui.HorizontalLayout( {
			classes: classes,
			items: items
		} );
		this.layout.addItems( [ this.buttonsLayout ] );

		if ( this.canCancel() ) {
			this.cancelButton.setDisabled( !this.editMode );
		}

	};

	OO.initClass( mw.ext.forms.mixin.FloatableButtons );

} )( mediaWiki, jQuery );
