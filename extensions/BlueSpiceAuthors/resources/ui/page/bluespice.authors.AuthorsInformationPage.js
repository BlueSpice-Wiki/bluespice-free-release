( ( mw, bs ) => {
	bs.util.registerNamespace( 'bs.authors.info' );

	bs.authors.info.AuthorsInformationPage = function AuthorsInformationPage( name, config ) {
		this.authorGrid = null;
		bs.authors.info.AuthorsInformationPage.super.call( this, name, config );
	};

	OO.inheritClass( bs.authors.info.AuthorsInformationPage, StandardDialogs.ui.BasePage ); // eslint-disable-line no-undef

	bs.authors.info.AuthorsInformationPage.prototype.setupOutlineItem = function () {
		bs.authors.info.AuthorsInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

		if ( this.outlineItem ) {
			this.outlineItem.setLabel( mw.message( 'bs-authors-info-dialog' ).plain() );
		}
	};

	bs.authors.info.AuthorsInformationPage.prototype.setup = function () {
		return;
	};

	bs.authors.info.AuthorsInformationPage.prototype.onInfoPanelSelect = async function () {
		if ( !this.authorGrid ) {
			await mw.loader.using( [ 'ext.oOJSPlus.data', 'oojs-ui.styles.icons-user' ] );

			let authorsData;

			try {
				const api = new mw.Api();
				const response = await api.get( {
					action: 'query',
					titles: this.pageName,
					prop: 'info'
				} );

				const pageId = Object.keys( response.query.pages )[ 0 ];

				const data = await bs.api.store.getData( 'pageauthors', {
					context: {
						wgArticleId: pageId
					}
				} );

				authorsData = data.results || [];
			} catch ( error ) {}

			this.authorGrid = new OOJSPlus.ui.data.GridWidget( {
				columns: {
					user_name: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-authors-info-dialog-grid-column-author' ).text(),
						type: 'user',
						showImage: true
					},
					author_type: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-authors-info-dialog-grid-column-type' ).text(),
						type: 'text'
					}
				},
				data: authorsData
			} );
			this.$element.append( this.authorGrid.$element );
		}
	};

	registryPageInformation.register( 'authors_infos', bs.authors.info.AuthorsInformationPage ); // eslint-disable-line no-undef

} )( mediaWiki, blueSpice );
