( function ( mw, $ ) {

	$( () => {
		mw.user.getRights().done( ( rights ) => {
			mw.loader.using( [ 'ext.enhancedstandarduis.filelist.panel' ] ).done( () => {
				/* eslint-disable-next-line no-jquery/no-global-selector */
				const $allFilesCnt = $( '#enhanced-ui-filelist-cnt' );
				const filesPanel = new ext.enhancedUI.panel.FilelistPanel( {
					rights: rights
				} );
				$( $allFilesCnt ).append( filesPanel.$element );
			} );
		} );
	} );

}( mediaWiki, jQuery ) );
