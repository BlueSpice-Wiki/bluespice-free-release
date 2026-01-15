$( () => {
	$( '#fs-sp-loading' ).remove();
	$( '#fs-container' ).append(
		new flexiskin.ui.Configurator( {
			skin: mw.config.get( 'wgFlexiSkin' )
		}
		).$element );

	if ( $( document ).find( '#fs-skeleton-cnt' ) ) {
		$( '#fs-skeleton-cnt' ).empty();
	}
} );
