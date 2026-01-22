mw.hook( 've.activationComplete' ).add( () => {
	const target = ve.init.target;

	registerInlineTextInspectors( target );
} );

function registerInlineTextInspectors( target ) {
	if ( target.getSurface().getMode() === 'visual' ) {
		const $popupTarget = $( '<div>' ).addClass( 'veplus-popup-cnt-inline-inspector' );
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '.ve-init-target' ).append( $popupTarget );
		const inspectorPopup = new ext.visualEditorPlus.ui.InlineTextInspector( {
			$overlay: true,
			$container: $popupTarget
		} );
		inspectorPopup.setTarget( target );
		$popupTarget.append( inspectorPopup.$element );

		for ( const key in ext.visualEditorPlus.registry.inlineTextInspectors.registry ) {
			const cls = ext.visualEditorPlus.registry.inlineTextInspectors.registry[ key ];
			const instance = new cls( inspectorPopup, {} ); // eslint-disable-line new-cap
			if ( instance instanceof ext.visualEditorPlus.ui.InlineTextInspectorElement ) {
				inspectorPopup.addInspector( key, instance );
			}
		}
		inspectorPopup.appendInspectors();
	} else {
		document.querySelectorAll( '.veplus-popup-cnt-inline-inspector' ).forEach( ( el ) => el.remove() );
	}
}
