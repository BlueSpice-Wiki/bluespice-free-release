( function ( mw, $ ) {
	$( () => {
		const $c = $( '#menuEditor-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( !$c.length ) {
			return;
		}

		const data = $c.data();
		let revId = mw.config.get( 'wgRevisionId' );
		if ( mw.util.getParamValue( 'oldid' ) ) {
			revId = mw.util.getParamValue( 'oldid' );
		}
		ext.menueditor.init.getPanelForPage(
			mw.config.get( 'wgPageName' ), data.menuKey, revId, data.mode, {
				defaultData: $c.data( 'default' )
			}
		).done( function ( panel ) {
			panel.connect( this, {
				saveFail: function ( error ) {
					$.prepend( new OO.ui.MessageWidget( {
						type: 'error',
						text: error
					} ).$element );
				},
				saveSuccess: function () {
					window.location = mw.util.getUrl( mw.config.get( 'wgPageName' ) );
				},
				cancel: function () {
					OO.ui.confirm( mw.message( 'menueditor-ui-cancel-confirmation' ).text() )
						.done( ( confirmed ) => {
							if ( confirmed ) {
								window.location = mw.util.getUrl( mw.config.get( 'wgPageName' ) );
							}
						} );
				}
			} );
			$c.html( panel.$element );
		} ).fail( ( e ) => {
			console.error( e ); // eslint-disable-line no-console
		} );
	} );

	const toolbarOffsetJson = require( './addToolbarOffset.json' );
	const toolbarOffsetHeight = toolbarOffsetJson.MenuEditorToolbarOffset;

	$( window ).on( 'scroll', function () {
		const windowTop = $( this ).scrollTop();
		const $toolbar = $( '.menueditor-toolbar' ); // eslint-disable-line no-jquery/no-global-selector
		const contentWidth = getContentWidth();
		if ( windowTop > toolbarOffsetHeight ) {
			$toolbar.addClass( 'floating' );
			$toolbar.css( 'top', toolbarOffsetHeight );
			$toolbar.css( 'width', contentWidth );
		} else {
			$toolbar.removeAttr( 'style' );
			// eslint-disable-next-line no-jquery/no-class-state
			if ( $toolbar.hasClass( 'floating' ) ) {
				$toolbar.removeClass( 'floating' );
			}
		}
	} );

	function getContentWidth() {
		return $( '#mw-content-text' ).innerWidth(); // eslint-disable-line no-jquery/no-global-selector
	}
}( mediaWiki, jQuery ) );
