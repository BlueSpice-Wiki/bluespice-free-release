( function ( mw, $ ) {

	$( () => {
		require( './panel/AllPagesPanel.js' );
		/* eslint-disable-next-line no-jquery/no-global-selector */
		const $allPagesCnt = $( '#enhanced-ui-allpages-cnt' );
		const isMobile = $( window ).width() < 767;

		let namespaceId = 0;
		if ( mw.util.getParamValue( 'namespace' ) ) {
			namespaceId = parseInt( mw.util.getParamValue( 'namespace' ) );
		}
		const allPagesPanel = new ext.enhancedUI.panel.AllPagesPanel( {
			mobileView: isMobile,
			namespaceId: namespaceId
		} );
		$allPagesCnt.append( allPagesPanel.$element );
	} );
}( mediaWiki, jQuery ) );
