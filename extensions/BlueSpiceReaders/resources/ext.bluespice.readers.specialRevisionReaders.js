( ( $ ) => {

	$( () => {
		const $container = $( '#bs-readers-special-revisionreaders-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.readers.ui.panel.SpecialRevisionReaders();

		$container.append( panel.$element );
	} );

} )( jQuery );
