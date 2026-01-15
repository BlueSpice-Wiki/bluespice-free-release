bs.util.registerNamespace( 'ext.bluespice.readers.ui.panel' );

ext.bluespice.readers.ui.panel.SpecialReadersUser = function ( cfg ) {
	ext.bluespice.readers.ui.panel.SpecialReadersUser.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-readers-data-store',
		pageSize: 25,
		query: mw.config.get( 'bsReadersUserID' )
	} );

	this.setup();
};

OO.inheritClass( ext.bluespice.readers.ui.panel.SpecialReadersUser, OO.ui.PanelLayout );

ext.bluespice.readers.ui.panel.SpecialReadersUser.prototype.setup = function () {
	this.gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( this.gridCfg );
	this.$element.append( this.grid.$element );
};

ext.bluespice.readers.ui.panel.SpecialReadersUser.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			pv_page_title: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-page' ).text(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => new OO.ui.HtmlSnippet( mw.html.element(
					'a',
					{
						href: mw.util.getUrl( value )
					},
					value
				) )
			},
			pv_ts: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-readers-header-ts' ).text(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
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
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-page' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-readers-header-ts' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.pv_page_title ) )
								.append( $( '<td>' ).text( record.pv_ts ) )
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
