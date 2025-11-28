bs.util.registerNamespace( 'ext.bluespice.readers.ui.panel' );

ext.bluespice.readers.ui.panel.SpecialRevisionReaders = function ( cfg ) {
	ext.bluespice.readers.ui.panel.SpecialRevisionReaders.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-readers-revision-readers-store',
		pageSize: 25,
		filter: {
			readers_page_id: { // eslint-disable-line camelcase
				type: 'number',
				value: mw.config.get( 'bsRevisionReadersPageId' )
			}
		},
		sorter: {
			readers_ts: { // eslint-disable-line camelcase
				direction: 'DESC'
			}
		}
	} );

	this.setup();
};

OO.inheritClass( ext.bluespice.readers.ui.panel.SpecialRevisionReaders, OO.ui.PanelLayout );

ext.bluespice.readers.ui.panel.SpecialRevisionReaders.prototype.setup = function () {
	this.gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( this.gridCfg );
	this.$element.append( this.grid.$element );
};

ext.bluespice.readers.ui.panel.SpecialRevisionReaders.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			readers_user_name: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-read-by' ).text(),
				type: 'user',
				showImage: true,
				sortable: true,
				filter: { type: 'user' }
			},
			readers_rev_id: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-revision-id' ).text(),
				type: 'number',
				sortable: true,
				filter: { type: 'number' }
			},
			rev_datetime: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-revision-date' ).text(),
				type: 'date',
				sortable: true,
				filter: { type: 'date' }
			},
			readers_ts: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-read-on' ).text(),
				type: 'date',
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
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-read-by' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-revision-id' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-revision-date' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-read-on' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.readers_user_name ) )
								.append( $( '<td>' ).text( record.readers_rev_id ) )
								.append( $( '<td>' ).text( record.rev_datetime.replace( ',', ' -' ) ) ) // CSV comma delimiter
								.append( $( '<td>' ).text( record.readers_ts ) )
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
