( ( $ ) => {

	$( () => {
		const $container = $( '#bs-readers-special-readers-page-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.readers.ui.panel.SpecialReadersPage();

		$container.append( panel.$element );
	} );

} )( jQuery );
