( function ( $, mw ) {
	const $content = $( '#content' ), // eslint-disable-line no-jquery/no-global-selector

		widget = new OO.ui.MessageWidget( {
			id: 'footerlinks-notice',
			type: 'notice',
			label: mw.message( 'menueditor-footerlinks-banner-text' ).plain()
		} );

	widget.$element.css( 'margin-bottom', '20px' );
	$content.prepend( widget.$element );
}( jQuery, mediaWiki ) );
