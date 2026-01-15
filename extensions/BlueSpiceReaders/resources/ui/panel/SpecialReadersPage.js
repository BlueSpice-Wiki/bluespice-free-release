bs.util.registerNamespace( 'ext.bluespice.readers.ui.panel' );

ext.bluespice.readers.ui.panel.SpecialReadersPage = function ( cfg ) {
	ext.bluespice.readers.ui.panel.SpecialReadersPage.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-readers-users-store',
		pageSize: 25,
		query: mw.config.get( 'bsReadersTitle' )
	} );

	this.setup();
};

OO.inheritClass( ext.bluespice.readers.ui.panel.SpecialReadersPage, OO.ui.PanelLayout );

ext.bluespice.readers.ui.panel.SpecialReadersPage.prototype.setup = function () {
	this.gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( this.gridCfg );
	this.$element.append( this.grid.$element );
};

ext.bluespice.readers.ui.panel.SpecialReadersPage.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			user_name: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-username' ).text(),
				type: 'user',
				showImage: true,
				sortable: true,
				filter: { type: 'user' }
			},
			user_ts: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-ts' ).text(),
				type: 'text',
				sortable: true,
				filter: { type: 'date' },
				valueParser: ( value ) => bs.util.convertMWTimestampToISO( value )
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
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-username' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-ts' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.user_name ) )
								.append( $( '<td>' ).text( record.user_ts ) )
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
