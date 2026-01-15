'use strict';

( function () {
	const $container = $( '#pdfcreator-overview' );
	if ( $container.length === 0 ) {
		return;
	}
	const hasEditRight = $container.data( 'edit' );
	require( './ui/panel/TemplatesPanel.js' );
	const panel = new ext.pdfcreator.ui.panel.TemplatesPanel( {
		expanded: false,
		editRight: hasEditRight
	} );

	$container.append( panel.$element );
}() );
