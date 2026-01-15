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

		const offsetJson = require( './addOffset.json' );
		const offsetHeight = offsetJson.offsetHeight;
		let floatingPaginator = false;
		const rightPos = getRightValue();
		const $paginator = $( '.enhanced-ui-allpages-panel-paginator' ); // eslint-disable-line no-jquery/no-global-selector
		const topValue = $( $paginator ).offset().top;

		$( window ).on( 'scroll', function () {
			const windowTop = $( this ).scrollTop();
			if ( windowTop > topValue ) {
				if ( !floatingPaginator ) {
					$paginator.css( 'top', offsetHeight );
					$paginator.css( 'position', 'fixed' );
					$paginator.css( 'right', rightPos );
					floatingPaginator = true;
				}
			} else {
				if ( floatingPaginator ) {
					$paginator.removeAttr( 'style' );
					floatingPaginator = false;
				}
			}
		} );

		$( '.oo-ui-outlineSelectWidget' ).on( 'focus', ( e ) => { // eslint-disable-line no-jquery/no-global-selector
			const target = e.target;
			if ( window.innerHeight < target.clientHeight ) {
				$( target ).css( 'top', offsetHeight );
				$( target ).css( 'position', 'fixed' );
				$( target ).css( 'width', $( target ).parent().width() );
				$( target ).css( 'height', 'calc( 100vh - ' + offsetHeight + 'px )' );
				$( target ).css( 'background-color', 'white' );
				$( target ).css( 'overflow', 'auto' );
				$( '.enhanced-ui-allpages-panel' ).css( 'min-height', target.clientHeight ); // eslint-disable-line no-jquery/no-global-selector
			}
		} );
		$( '.oo-ui-outlineSelectWidget' ).on( 'blur', ( e ) => { // eslint-disable-line no-jquery/no-global-selector
			$( e.target ).removeAttr( 'style' );
			$( '.enhanced-ui-allpages-panel' ).removeAttr( 'style' ); // eslint-disable-line no-jquery/no-global-selector
		} );
	} );

	function getRightValue() {
		// eslint-disable-next-line no-jquery/no-global-selector
		const $mainValue = $( '#mw-content-text' ).offset().left + $( '#mw-content-text' ).width();
		return $( document ).width() - $mainValue;
	}
}( mediaWiki, jQuery ) );
