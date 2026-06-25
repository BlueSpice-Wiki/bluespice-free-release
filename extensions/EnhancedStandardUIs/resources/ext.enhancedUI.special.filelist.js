( function ( mw, $ ) {
	$( async () => {
		await mw.loader.using( [ 'mediawiki.user', 'ext.enhancedstandarduis.filelist.panel' ] );
		// TODO: this is not great for performance, can be part of the container dataset already
		const rights = await mw.user.getRights();

		/* eslint-disable-next-line no-jquery/no-global-selector */
		const $allFilesCnt = $( '#enhanced-ui-filelist-cnt' );
		const filesPanel = new ext.enhancedUI.panel.FilelistPanel( {
			rights: rights
		} );
		$( $allFilesCnt ).append( filesPanel.$element );
	} );

}( mediaWiki, jQuery ) );
