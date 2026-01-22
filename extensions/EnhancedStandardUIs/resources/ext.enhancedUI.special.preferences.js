( function ( mw, $ ) {

	$( () => {
		require( './panel/PreferencesPanel.js' );
		/* eslint-disable-next-line no-jquery/no-global-selector */
		const $preferencesCnt = $( '#enhanced-preferences' );

		const preferencesJson = $preferencesCnt.attr( 'data-prefs' );
		const preferences = JSON.parse( preferencesJson );
		const sectionJson = $preferencesCnt.attr( 'data-sections' );
		const modulesJson = $preferencesCnt.attr( 'data-modules' );
		const modules = JSON.parse( modulesJson );
		const sections = JSON.parse( sectionJson );
		const isMobile = $( window ).width() < 767;

		// eslint-disable-next-line es-x/no-object-values
		mw.loader.using( Object.values( modules ) ).done( () => {
			const preferencesPanel = new ext.enhancedUI.panel.PreferencesPanel( {
				sections: sections,
				preferences: preferences,
				mobile: isMobile
			} );
			$( $preferencesCnt ).append( preferencesPanel.$element );
		} );
	} );

}( mediaWiki, jQuery ) );
