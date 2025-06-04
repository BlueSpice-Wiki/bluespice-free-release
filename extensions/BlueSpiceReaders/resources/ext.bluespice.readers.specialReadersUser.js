( ( $ ) => {

	$( () => {
		const $container = $( '#bs-readers-special-readers-user-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.readers.ui.panel.SpecialReadersUser();

		$container.append( panel.$element );
	} );

} )( jQuery );
