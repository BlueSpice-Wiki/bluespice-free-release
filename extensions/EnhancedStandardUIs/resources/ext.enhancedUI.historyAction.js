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
}( mediaWiki, jQuery ) );
