( function ( mw, $ ) {

	$( () => {
		require( './panel/HistoryPanel.js' );
		mw.user.getRights().done( ( rights ) => {
			/* eslint-disable-next-line no-jquery/no-global-selector */
			const $historyCnt = $(
				'#enhanced-history-cnt[data-history]'
			);
			const cfg = {
				rights: rights,
				data: $historyCnt.data( 'history' )
			};
			const historyGrid = new ext.enhancedUI.panel.HistoryPanel( cfg );
			$historyCnt.append( historyGrid.$element );
		} );
	} );

	const toolbarOffsetJson = require( './addToolbarOffset.json' );
	const toolbarOffsetHeight = toolbarOffsetJson.offsetHeight;
	$( window ).on( 'scroll', function () {

		const windowTop = $( this ).scrollTop();
		const $toolbar = $( '.enhanced-history-toolbar' ); // eslint-disable-line no-jquery/no-global-selector
		const contentWidth = getContentWidth();

		if ( windowTop > toolbarOffsetHeight ) {
			$toolbar.css( 'top', toolbarOffsetHeight );
			$toolbar.css( 'position', 'fixed' );
			$toolbar.css( 'width', contentWidth );
			$toolbar.css( 'z-index', 5 );
		} else {
			$toolbar.removeAttr( 'style' );
		}
	} );

	function getContentWidth() {
		return $( '#mw-content-text' ).innerWidth(); // eslint-disable-line no-jquery/no-global-selector
	}
}( mediaWiki, jQuery ) );
