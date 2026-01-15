( function ( mw, $ ) {
	mw.ext.forms.init = _init;

	function _init( $formContainer, showPicker ) {
		if ( $formContainer.length === 0 ) {
			return;
		}
		const form = $formContainer.data( 'form' );
		const data = $formContainer.data( 'data' );
		const formCreated = $formContainer.data( 'form-created' );
		const action = $formContainer.data( 'action' );

		if ( form ) {
			_makeForm( form );
		}

		function _makeForm( form ) { // eslint-disable-line no-shadow
			var form = new mw.ext.forms.widget.Form( { // eslint-disable-line no-var, no-redeclare
				definitionName: form,
				action: action,
				data: data,
				targetPage: $formContainer.data( 'target-title' ),
				createdOn: formCreated || ''
			} );
			$formContainer.find( '.mw-ext-forms-form' ).remove();
			$formContainer.append( form.$element );

			// BlueSpiceDiscovery skin special case
			if ( $( 'body' ).hasClass( 'base-bluespicediscovery' ) || $( 'body' ).hasClass( 'skin-bluespicediscovery' ) ) {
				form.connect( this, {
					initComplete: function () {
						const offset = $( '#main' ).offset().top + $( '#title-line' ).height();
						form.$element.children( '.form-instance-toolbar' ).css( 'top', offset );
					}
				} );
			}
		}
	}

	$( () => {
		$( '.forms-form-container' ).each( ( k, container ) => {
			const $formContainer = $( container );
			mw.ext.forms.init( $formContainer, true );
		} );
	} );

}( mediaWiki, jQuery ) );
