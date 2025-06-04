$( document ).on( 'click', '#ca-delete', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.DeleteDialog' ] ).done( () => {
		const msg = require( './deleteDialogMsg.json' );

		const diag = new StandardDialogs.ui.DeleteDialog( {
			id: 'standarddialogs-dlg-delete',
			pageName: mw.config.get( 'wgRelevantPageName' ),
			msg: msg
		} );
		diag.on( 'actioncompleted', () => {
			window.location.reload();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );

$( document ).on( 'click', '#ca-move', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.MoveDialog' ] ).done( () => {
		const diag = new StandardDialogs.ui.MoveDialog( {
			id: 'standarddialogs-dlg-move',
			pageName: mw.config.get( 'wgRelevantPageName' )
		} );
		diag.on( 'actioncompleted', ( newTitle ) => {
			window.location.href = newTitle.getUrl();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );

$( document ).on( 'click', '#ca-protect', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.ProtectDialog' ] ).done( () => {
		const diag = new StandardDialogs.ui.ProtectDialog( {
			id: 'standarddialogs-dlg-protect',
			pageName: mw.config.get( 'wgRelevantPageName' )
		} );
		diag.on( 'actioncompleted', () => {
			window.location.reload();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );

$( document ).on( 'click', '#ca-purge', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.RefreshDialog' ] ).done( () => {
		const diag = new StandardDialogs.ui.RefreshDialog( {
			id: 'standarddialogs-dlg-refresh',
			pageName: mw.config.get( 'wgRelevantPageName' )
		} );
		diag.on( 'actioncompleted', () => {
			window.location.reload();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );

$( document ).on( 'click', '#ca-copy', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.DuplicateDialog' ] ).done( () => {
		const diag = new StandardDialogs.ui.DuplicateDialog( {
			id: 'standarddialogs-dlg-copy',
			pageName: mw.config.get( 'wgRelevantPageName' )
		} );
		diag.on( 'actioncompleted', ( newTitle ) => {
			window.location.href = newTitle.getUrl();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );

window.registryPageInformation = new OO.Registry();
$( document ).on( 'click', '#t-info', ( e ) => {
	const rlModules = require( './pageInfoPanelModuleRegistry.json' );

	mw.loader.using( 'ext.standardDialogs.ui.PageInformationDialog' ).done( () => {
		mw.loader.using( rlModules ).done( () => {
			const diag = new StandardDialogs.ui.PageInformationDialog( {
				id: 'standarddialogs-dlg-pageinformation',
				pageName: mw.config.get( 'wgRelevantPageName' ),
				panelRegistry: registryPageInformation // eslint-disable-line no-undef
			} );
			diag.show();
		} );
	} );
	e.defaultPrevented = true;
	return false;
} );

$( document ).on( 'input', '#standarddialogs-dlg-new-page-tf-target input', ( e ) => {
	const params = {
			action: 'query',
			list: 'search',
			srsearch: e.target.value,
			format: 'json'
		},
		api = new mw.Api();

	api.get( params ).done( ( data ) => {
		if ( data.query.search[ 0 ] !== undefined ) {
			$( '.oo-ui-inputWidget-input' ).removeAttr( 'aria-label' );
		} else {
			$( '.oo-ui-inputWidget-input' ).attr( 'aria-label', mw.message( 'standarddialogs-new-page-not-exist-label' ).plain() );
		}
	} );
} );

$( document ).on( 'click', '#ca-new-page, #new-content', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.NewPageDialog' ] ).done( () => {
		const diag = new StandardDialogs.ui.NewPageDialog( {
			id: 'standarddialogs-dlg-new-page',
			pageName: mw.config.get( 'wgRelevantPageName' )
		} );
		diag.on( 'actioncompleted', ( newTitle ) => {
			window.location.href = newTitle.getUrl( { action: 'edit' } );
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );

$( document ).on( 'click', '#ca-new-subpage', ( e ) => {
	mw.loader.using( [ 'ext.standardDialogs.ui.NewSubpageDialog' ] ).done( () => {
		const diag = new StandardDialogs.ui.NewSubpageDialog( {
			id: 'standarddialogs-dlg-new-subpage',
			pageName: mw.config.get( 'wgRelevantPageName' )
		} );
		diag.on( 'actioncompleted', ( newTitle ) => {
			window.location.href = newTitle.getUrl();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );
