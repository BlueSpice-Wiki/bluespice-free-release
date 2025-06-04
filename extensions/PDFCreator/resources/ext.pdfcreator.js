'use strict';

( function () {
	const tabs = OO.ui.infuse( $( '.pdf-creator-template-tab' ) );
	if ( tabs ) {
		$( '#pdf-creator-skeleton-cnt' ).css( 'display', 'none' );
		$( '#pdf-creator-template-cnt' ).removeAttr( 'style' );
	}

}() );
