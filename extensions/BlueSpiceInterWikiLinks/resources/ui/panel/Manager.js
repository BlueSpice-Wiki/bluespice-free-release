bs.util.registerNamespace( 'ext.bluespice.interwikilinks.ui.panel' );

ext.bluespice.interwikilinks.ui.panel.Manager = function ( cfg ) {
	cfg = cfg || {};

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-interwiki-store',
		pageSize: 25
	} );
	this.editable = cfg.editable || false;

	this.gridCfg = this.setupGridConfig();
	cfg.grid = this.gridCfg;

	ext.bluespice.interwikilinks.ui.panel.Manager.parent.call( this, cfg );
	if ( !this.editable ) {
		this.setAbilities( [] );
	}
};

OO.inheritClass( ext.bluespice.interwikilinks.ui.panel.Manager, OOJSPlus.ui.panel.ManagerGrid );

ext.bluespice.interwikilinks.ui.panel.Manager.prototype.setupGridConfig = function () {
	const gridCfg = {
		multiSelect: false,
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			iw_prefix: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-interwikilinks-headerprefix' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' }
			},
			iw_url: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-interwikilinks-headerurl' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' }
			},
			edit: {
				headerText: mw.message( 'oojsplus-toolbar-edit' ).text(),
				title: mw.message( 'oojsplus-toolbar-edit' ).text(),
				type: 'action',
				actionId: 'edit',
				icon: 'edit',
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30,
				disabled: ( row ) => !this.editable || row.editable === false
			},
			delete: {
				headerText: mw.message( 'oojsplus-toolbar-delete' ).text(),
				title: mw.message( 'oojsplus-toolbar-delete' ).text(),
				type: 'action',
				actionId: 'delete',
				icon: 'trash',
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30,
				disabled: ( row ) => !this.editable || row.editable === false
			}
		},
		store: this.store,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();
					const $table = $( '<table>' );

					const $thead = $( '<thead>' )
						.append( $( '<tr>' )
							.append( $( '<th>' ).text( mw.message( 'bs-interwikilinks-headerprefix' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-interwikilinks-headerurl' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];

							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.iw_prefix ) )
								.append( $( '<td>' ).text( record.iw_url ) )
							);
						}
					}

					$table.append( $thead, $tbody );

					deferred.resolve( `<table>${ $table.html() }</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	return gridCfg;
};

ext.bluespice.interwikilinks.ui.panel.Manager.prototype.getToolbarActions = function () {
	return [
		this.getAddAction( {
			icon: 'add',
			title: mw.message( 'bs-interwikilinks-titleaddinterwikilink' ).plain(),
			displayBothIconAndLabel: true
		} )
	];
};

OOJSPlus.ui.panel.ManagerGrid.prototype.getInitialAbilities = function () {
	// Override to set abilities on load without any selection
	return {
		add: this.editable
	};
};

ext.bluespice.interwikilinks.ui.panel.Manager.prototype.onAction = async function ( action, row ) {
	if ( action === 'add' ) {
		this.showInterwikilinksDialog( {
			title: mw.message( 'bs-interwikilinks-titleaddinterwikilink' ).plain()
		} );
	}
	if ( action === 'edit' ) {
		this.showInterwikilinksDialog(
			{
				title: mw.message( 'bs-interwikilinks-titleeditinterwikilink' ).plain(),
				prefix: row.iw_prefix,
				url: row.iw_url,
				oldPrefix: row.iw_prefix
			}
		);
	}
	if ( action === 'delete' ) {
		bs.util.confirm(
			'GMremove',
			{
				title: mw.message( 'bs-interwikilinks-titledeleteinterwikilink' ).plain(),
				text: mw.message( 'bs-interwikilinks-confirmdeleteinterwikilink' ).plain()
			},
			{
				ok: () => {
					this.onRemoveIWLOk( row.iw_prefix );
				}
			}
		);
	}
};

ext.bluespice.interwikilinks.ui.panel.Manager.prototype.showInterwikilinksDialog = async function ( data ) {
	const interWikiLinksPage = new bs.interwikilinks.ui.page.InterWikiLinksPage( {
		data: data
	} );

	const dialog = new OOJSPlus.ui.dialog.BookletDialog( {
		pages: [ interWikiLinksPage ]
	} );

	const result = await dialog.show().closed;
	if ( result && result.success ) {
		this.store.reload();
	}
};

ext.bluespice.interwikilinks.ui.panel.Manager.prototype.onRemoveIWLOk = async function ( iwPrefix ) {
	const result = await bs.api.tasks.execSilent(
		'interwikilinks',
		'removeInterWikiLink',
		{
			prefix: iwPrefix
		}
	);

	if ( result.success ) {
		this.store.reload();
	}
};
