( ( mw, bs ) => {
	bs.util.registerNamespace( 'bs.readers.info' );

	bs.readers.info.ReadersInformationPage = function ReadersInformationPage( name, config ) {
		this.readerGrid = null;
		bs.readers.info.ReadersInformationPage.super.call( this, name, config );
	};

	OO.inheritClass( bs.readers.info.ReadersInformationPage, StandardDialogs.ui.BasePage ); // eslint-disable-line no-undef

	bs.readers.info.ReadersInformationPage.prototype.setupOutlineItem = function () {
		bs.readers.info.ReadersInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

		if ( this.outlineItem ) {
			this.outlineItem.setLabel( mw.message( 'bs-readers-info-dialog' ).text() );
		}
	};

	bs.readers.info.ReadersInformationPage.prototype.setup = function () {
		return;
	};

	bs.readers.info.ReadersInformationPage.prototype.onInfoPanelSelect = async function () {
		if ( !this.readerGrid ) {
			const rights = await mw.user.getRights();
			if ( !rights.includes( 'viewreaders' ) ) {
				const message = mw.message( 'bs-readers-permission-error' ).text();
				this.readerGrid = new OO.ui.LabelWidget( {
					label: message
				} );
				this.$element.append( this.readerGrid.$element );
				return;
			}

			await mw.loader.using( [ 'ext.oOJSPlus.data', 'oojs-ui.styles.icons-user' ] );

			let readersStore;

			try {
				const api = new mw.Api();
				const response = await api.get( {
					action: 'query',
					titles: this.pageName,
					prop: 'info'
				} );

				const pageId = Object.keys( response.query.pages )[ 0 ];

				readersStore = new OOJSPlus.ui.data.store.RemoteStore( {
					action: 'bs-readers-page-readers-store',
					pageSize: 10
				} );
				readersStore.filter( new OOJSPlus.ui.data.filter.String( {
					value: pageId,
					operator: 'eq',
					type: 'string'
				} ), 'readers_page_id' );
			} catch ( error ) {}

			this.readerGrid = new OOJSPlus.ui.data.GridWidget( {
				columns: {
					readers_user_name: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-readers-info-dialog-column-readers' ).text(),
						type: 'user',
						showImage: true
					}
				},
				store: readersStore
			} );
			this.$element.append( this.readerGrid.$element );
		}
	};

	if ( mw.config.get( 'bsViewReadersRight' ) ) {
		registryPageInformation.register( 'readers_infos', bs.readers.info.ReadersInformationPage ); // eslint-disable-line no-undef
	}

} )( mediaWiki, blueSpice );
