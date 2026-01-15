StandardDialogs.ui.PropertiesInformationPage = function PropertiesInformationPage( name, config ) {
	StandardDialogs.ui.PropertiesInformationPage.super.call( this, name, config );
};

OO.inheritClass( StandardDialogs.ui.PropertiesInformationPage, StandardDialogs.ui.BasePage );

StandardDialogs.ui.PropertiesInformationPage.prototype.setupOutlineItem = function () {
	StandardDialogs.ui.PropertiesInformationPage.super.prototype
		.setupOutlineItem.apply( this, arguments );

	if ( this.outlineItem ) {
		this.outlineItem.setLabel( mw.message( 'standarddialogs-page-info-properties' ).plain() );
	}
};

StandardDialogs.ui.PropertiesInformationPage.prototype.setup = function () {
	const me = this;
	me.pageInfo = {};
	const dfdData = this.getData();
	$.when( dfdData ).done( () => {
		if ( me.pageInfo !== undefined ) {
			const fieldLayout = new OO.ui.FieldsetLayout();
			const contentTable = $( '<table>' );
			contentTable.addClass( 'wikitable page-information' );

			const templates = me.getTemplates();
			contentTable.append(
				$( '<tr>' ).append(
					$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-templates' ).plain() ),
					$( '<td>' ).append( templates ) ) );

			const categories = me.getCategories();
			contentTable.append(
				$( '<tr>' ).append(
					$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-categories' ).plain() ),
					$( '<td>' ).append( categories ) ) );
			const links = me.getInternalLinks();
			contentTable.append(
				$( '<tr>' ).append(
					$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-internal' ).plain() ),
					$( '<td>' ).append( links ) ) );

			const images = me.getImageLinks();
			contentTable.append(
				$( '<tr>' ).append(
					$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-images' ).plain() ),
					$( '<td>' ).append( images ) ) );

			const extLinks = me.getExternalLinks();
			contentTable.append(
				$( '<tr>' ).append(
					$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-external' ).plain() ),
					$( '<td>' ).append( extLinks ) ) );

			fieldLayout.$element.append( contentTable );

			me.$element.append( fieldLayout.$element );
		}
	} );
};

StandardDialogs.ui.PropertiesInformationPage.prototype.getData = function () {
	const me = this;
	const dfd = new $.Deferred();

	me.pageInfo.templates = [];
	const dfdTemplates = me.doApiCall( 'templates', 'tl', '' );
	me.pageInfo.categories = [];
	const dfdCategories = me.doApiCall( 'categories', 'cl', '' );
	me.pageInfo.links = [];
	const dfdLinks = me.doApiCall( 'links', 'pl', '' );
	me.pageInfo.images = [];
	const dfdImages = me.doApiCall( 'images', 'im', '' );
	me.pageInfo.extlinks = [];
	const dfdExtLinks = me.doApiCall( 'extlinks', 'el', '' );
	$.when( dfdTemplates, dfdCategories, dfdLinks, dfdImages, dfdExtLinks ).done( () => {
		const dfds = [];
		dfds.push( dfdTemplates );
		dfds.push( dfdCategories );
		dfds.push( dfdLinks );
		dfds.push( dfdImages );
		dfds.push( dfdExtLinks );

		$.when.apply( me, dfds ).done( function () {
			dfd.resolve.apply( this, arguments );
		} );
	} );

	return dfd.promise();
};

StandardDialogs.ui.PropertiesInformationPage.prototype.doApiCall = function ( prop, continueProp, continueVal ) {
	const me = this;
	const dfd = new $.Deferred();
	const mwApi = new mw.Api();
	const params = {
		action: 'query',
		titles: me.pageName,
		format: 'json',
		prop: prop
	};
	const paramProperty = continueProp + 'continue';
	if ( continueVal !== '' ) {
		params[ paramProperty ] = continueVal;
	}
	mwApi.postWithToken( 'csrf', params ).fail( function () {
		dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
	} )
		.done( ( resp ) => {
			for ( const page in resp.query.pages ) {
				if ( resp.query.pages[ page ][ prop ] ) {
					me.pageInfo[ prop ].push( resp.query.pages[ page ][ prop ] );
				}
			}

			if ( resp.continue ) {
				if ( resp.continue.continue ) {
					const recursiveCall = me.doApiCall(
						prop,
						continueProp,
						resp.continue[ paramProperty ]
					);
					recursiveCall.done( () => {
						dfd.resolve( resp );
					} );
				}
			} else {
				dfd.resolve( resp );
			}
		} );
	return dfd.promise();
};

StandardDialogs.ui.PropertiesInformationPage.prototype.getImageLinks = function () {
	const images = $( '<ul>' );

	if ( this.pageInfo.images.length === 0 ) {
		return $( '<span>' ).text(
			mw.message( 'standarddialogs-page-info-page-no-images-label' ).text() );
	}
	for ( const x in this.pageInfo.images ) {
		if ( this.pageInfo.images[ x ] ) {
			for ( let y = 0; y < this.pageInfo.images[ x ].length; y++ ) {
				const title = mw.Title.newFromText( this.pageInfo.images[ x ][ y ].title );
				images.append(
					$( '<li>' ).append( $( '<a>' )
						.attr( {
							href: title.getUrl(),
							title: title.getPrefixedText()
						} )
						.text( title.getMainText() ) ) );
			}
		}
	}
	return images;
};

StandardDialogs.ui.PropertiesInformationPage.prototype.getTemplates = function () {
	const templates = $( '<ul>' );
	if ( this.pageInfo.templates.length === 0 ) {
		return $( '<span>' ).text(
			mw.message( 'standarddialogs-page-info-page-no-templates-label' ).text() );
	}
	for ( const x in this.pageInfo.templates ) {
		if ( this.pageInfo.templates[ x ] ) {
			for ( let y = 0; y < this.pageInfo.templates[ x ].length; y++ ) {
				const title = mw.Title.newFromText( this.pageInfo.templates[ x ][ y ].title );
				templates.append(
					$( '<li>' )
						.append( $( '<a>' )
							.attr( {
								href: title.getUrl(),
								title: title.getPrefixedText()
							} )
							.text( title.getMainText() ) ) );
			}
		}
	}
	return templates;
};

StandardDialogs.ui.PropertiesInformationPage.prototype.getCategories = function () {
	const categories = $( '<ul>' );
	if ( this.pageInfo.categories.length === 0 ) {
		return $( '<span>' ).text(
			mw.message( 'standarddialogs-page-info-page-no-categories-label' ).text() );
	}
	for ( const x in this.pageInfo.categories ) {
		if ( this.pageInfo.categories[ x ] ) {
			for ( let y = 0; y < this.pageInfo.categories[ x ].length; y++ ) {
				const title = mw.Title.newFromText( this.pageInfo.categories[ x ][ y ].title );
				categories.append(
					$( '<li>' )
						.append( $( '<a>' )
							.attr( {
								href: title.getUrl(),
								title: title.getPrefixedText()
							} )
							.text( title.getMainText() ) ) );
			}
		}
	}
	return categories;
};

StandardDialogs.ui.PropertiesInformationPage.prototype.getInternalLinks = function () {
	const links = $( '<ul>' );
	if ( this.pageInfo.links.length === 0 ) {
		return $( '<span>' ).text(
			mw.message( 'standarddialogs-page-info-page-no-links-label' ).text() );
	}
	for ( const x in this.pageInfo.links ) {
		if ( this.pageInfo.links[ x ] ) {
			for ( let y = 0; y < this.pageInfo.links[ x ].length; y++ ) {
				const title = mw.Title.newFromText( this.pageInfo.links[ x ][ y ].title );
				links.append(
					$( '<li>' )
						.append( $( '<a>' )
							.attr( {
								href: title.getUrl(),
								title: title.getPrefixedText()
							} )
							.text( title.getMainText() ) ) );
			}
		}
	}
	return links;
};

StandardDialogs.ui.PropertiesInformationPage.prototype.getExternalLinks = function () {
	const links = $( '<ul>' );
	if ( this.pageInfo.extlinks.length === 0 ) {
		return $( '<span>' ).text(
			mw.message( 'standarddialogs-page-info-page-no-extlinks-label' ).text() );
	}
	for ( const x in this.pageInfo.extlinks ) {
		if ( this.pageInfo.extlinks[ x ] ) {
			for ( const key in this.pageInfo.extlinks[ x ] ) {
				links.append(
					$( '<li>' )
						.append( $( '<a>' )
							.attr( {
								href: Object.values( this.pageInfo.extlinks[ x ][ key ] )
							} )
							.text( Object.values( this.pageInfo.extlinks[ x ][ key ] ) ) ) );
			}
		}
	}
	return links;
};

// register
registryPageInformation.register( 'properties_infos', StandardDialogs.ui.PropertiesInformationPage ); // eslint-disable-line no-undef
