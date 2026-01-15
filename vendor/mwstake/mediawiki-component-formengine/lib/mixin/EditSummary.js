( function ( mw ) {
	mw.ext.forms.mixin.EditSummary = function () {
		if ( !this.enableEditSummary ) {
			return;
		}
		let placeholder = mw.message( 'mwstake-formengine-edit-summary-placeholder' ).text();

		if ( typeof this.enableEditSummary === 'object' ) {
			if ( !this.enableEditSummary.hasOwnProperty( 'showSummaryInput' ) ||
				this.enableEditSummary.showSummaryInput === false ) {
				this.enableEditSummary = false;
				return;
			}
			if ( this.enableEditSummary.hasOwnProperty( 'showOnCreate' ) &&
				this.enableEditSummary.showOnCreate === false &&
				this.action === 'create' ) {
				this.enableEditSummary = false;
				return;
			}
			if ( this.enableEditSummary.hasOwnProperty( 'placeholder' ) ) {
				placeholder = this.enableEditSummary.placeholder;
			}

			this.enableEditSummary = true;
		}

		this.summaryInput = new OO.ui.TextInputWidget( {
			maxLength: 255,
			placeholder: placeholder
		} );

		this.summaryInput.$element.insertBefore( this.buttonsLayout.$element.find( '#formSubmit' ) );
	};

	OO.initClass( mw.ext.forms.mixin.EditSummary );

}( mediaWiki ) );
