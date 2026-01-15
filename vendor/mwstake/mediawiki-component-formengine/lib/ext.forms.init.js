( function( mw, $, undefined ) {
	mw.ext.forms.init = _init;

	function _init( $formContainer, showPicker ) {
		if ( $formContainer.length === 0 ) {
			return;
		}
		var form = $formContainer.data( 'form' );
		var data = $formContainer.data( 'data' );
		var formCreated = $formContainer.data( 'form-created' );
		var action = $formContainer.data( 'action' );

		if ( form ) {
			_makeForm( form );
		}

		function _makeForm( form ) {
			var form = new mw.ext.forms.widget.Form( {
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
						var offset = $('#main').offset().top + $('#title-line').height();
						form.$element.children( '.form-instance-toolbar' ).css( 'top', offset );
					}
				} );
			}
		}
	}

	$( function() {
		$( '.forms-form-container' ).each( function( k, container ) {
			var $formContainer = $( container );
			mw.ext.forms.init( $formContainer, true );
		} );
	} );

} )( mediaWiki, jQuery );
