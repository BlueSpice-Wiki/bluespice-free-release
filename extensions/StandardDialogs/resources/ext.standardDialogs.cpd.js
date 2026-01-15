window.registryPageInformation = new OO.Registry();
$( document ).on( 'click', ' .cpd-action-info', ( e ) => {
	if ( !e.currentTarget.dataset.title ) {
		return;
	}
	e.stopImmediatePropagation();
	const rlModules = require( './pageInfoPanelModuleRegistry.json' );
	const title = e.currentTarget.dataset.title;
	mw.loader.using( 'ext.standardDialogs.ui.PageInformationDialog' ).done( () => {
		mw.loader.using( rlModules ).done( () => {
			const diag = new StandardDialogs.ui.PageInformationDialog( {
				pageName: title,
				panelRegistry: registryPageInformation // eslint-disable-line no-undef
			} );
			diag.show();
		} );
	} );
	e.defaultPrevented = true;
	return false;
} );
