if ( mw.config.get( 'bsSaferEditIsEditMode' ) ) {
	_activateEditMode();
}

// This is an exception, only time edit mode is inited on client-side
// It would be nicer to implement extendable client-side system for detecting
// edit mode - but for only this its not worth it
mw.hook( 've.activationComplete' ).add( () => {
	_activateEditMode();
} );

function _activateEditMode() { // eslint-disable-line no-underscore-dangle
	new bs.saferEdit.Save( { // eslint-disable-line no-new
		section: mw.config.get( 'bsSaferEditEditSection' ),
		interval: mw.config.get( 'bsgSaferEditInterval' ) * 1000
	} );
}

if ( mw.config.get( 'bsSaferEditDisplayWarning' ) ) {
	new bs.saferEdit.Warning( { // eslint-disable-line no-new
		section: mw.config.get( 'bsSaferEditEditSection' ),
		interval: mw.config.get( 'bsgSaferEditInterval' ) * 1000
	} );
}
