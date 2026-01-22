if ( mw.config.get( 'wgCurRevisionId' ) < 1 ) {
	return;
}
$( () => {
	window.mws.wire.listen( window.mws.wire.getCurrentPageChannel(), ( payload ) => {
		if ( !payload.action || payload.action !== 'revisionSaved' ) {
			return;
		}
		bs.alerts.add(
			'bs-articleinfo-newrevision-info',
			mw.message( payload.message, payload.page ).parse(), // eslint-disable-line mediawiki/msg-doc
			bs.alerts.TYPE_INFO
		);
	} );
} );
