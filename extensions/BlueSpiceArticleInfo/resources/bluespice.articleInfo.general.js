( function ( mw, $, bs ) {
	if ( mw.config.get( 'wgCurRevisionId' ) < 1 ) {
		return;
	}

	const checkRevisionInterval =
		mw.config.get( 'bsgArticleInfoCheckRevisionInterval' ) * 1000;

	if ( checkRevisionInterval < 1000 ) {
		return;
	}

	BSPing.registerListener(
		'ArticleInfo',
		checkRevisionInterval,
		[ 'checkRevision', mw.config.get( 'wgAction' ) ],
		_checkRevisionListener
	);

	function _checkRevisionListener( result, Listener ) { // eslint-disable-line no-underscore-dangle, no-unused-vars
		if ( result.success !== true ) {
			return;
		}
		if ( result.newRevision !== true ) {
			BSPing.registerListener(
				'ArticleInfo',
				checkRevisionInterval,
				[ 'checkRevision', mw.config.get( 'wgAction' ) ],
				_checkRevisionListener
			);
			return;
		}

		const $elem = $( '<div>' ).append( result.checkRevisionView );

		bs.alerts.add(
			'bs-articleinfo-newrevision-info',
			$elem,
			bs.alerts.TYPE_INFO
		);
	}
}( mediaWiki, jQuery, blueSpice ) );
