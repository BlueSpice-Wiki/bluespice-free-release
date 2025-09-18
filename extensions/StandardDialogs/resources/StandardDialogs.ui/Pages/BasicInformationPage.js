StandardDialogs.ui.BasicInformationPage = function BasicInformationPage( name, config ) {
	StandardDialogs.ui.BasicInformationPage.super.call( this, name, config );
};

OO.inheritClass( StandardDialogs.ui.BasicInformationPage, StandardDialogs.ui.BasePage );

StandardDialogs.ui.BasicInformationPage.prototype.setupOutlineItem = function () {
	StandardDialogs.ui.BasicInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

	if ( this.outlineItem ) {
		this.outlineItem.setLabel( mw.message( 'standarddialogs-page-info-general' ).plain() );
	}
};

StandardDialogs.ui.BasicInformationPage.prototype.setup = function () {
	const me = this;
	const dfdData = this.getData();
	$.when( dfdData ).done( () => {
		if ( me.pageInfo !== undefined ) {
			const fieldLayout = new OO.ui.FieldsetLayout();
			const contentTable = $( '<table>' );
			contentTable.addClass( 'wikitable page-information' );

			for ( const p in me.pageInfo ) {
				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-title' ).plain() ),
						$( '<td>' ).text( me.pageInfo[ p ].title ) ) );

				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-length' ).plain() ),
						$( '<td>' ).text( me.pageInfo[ p ].length ) ) );

				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-id' ).plain() ),
						$( '<td>' ).text( me.pageInfo[ p ].pageid ) ) );

				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-language' ).plain() ),
						$( '<td>' ).text( me.pageInfo[ p ].pagelanguage ) ) );
				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-model' ).plain() ),
						$( '<td>' ).text( me.pageInfo[ p ].contentmodel ) ) );
				if ( me.pageInfo[ p ].watchers ) {
					contentTable.append(
						$( '<tr>' ).append(
							$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-watch' ).plain() ),
							$( '<td>' ).text( me.pageInfo[ p ].watchers ) ) );
				}
				const redirects = me.getRedirectLinks( p );
				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-redirects' ).plain() ),
						$( '<td>' ).append( redirects ) ) );
			}
			fieldLayout.$element.append( contentTable );

			me.$element.append( fieldLayout.$element );
		}
	} );
};

StandardDialogs.ui.BasicInformationPage.prototype.getData = function () {
	const me = this;
	const dfd = new $.Deferred();

	const mwApi = new mw.Api();
	mwApi.postWithToken( 'csrf', {
		action: 'query',
		titles: me.pageName,
		format: 'json',
		prop: 'info|redirects|pageviews',
		inprop: 'watchers|visitingwatchers'
	} ).fail( function () {
		dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
	} )
		.done( function ( resp ) {
			me.pageInfo = resp.query.pages;
			dfd.resolve( arguments );
		} );
	return dfd.promise();
};

StandardDialogs.ui.BasicInformationPage.prototype.getRedirectLinks = function ( id ) {
	const links = $( '<ul>' );
	if ( this.pageInfo[ id ].redirects ) {
		for ( const key in this.pageInfo[ id ].redirects ) {
			const redirect = this.pageInfo[ id ].redirects[ key ];
			const title = mw.Title.newFromText( redirect.title, redirect.ns );
			links.append(
				$( '<li>' )
					.append( $( '<a>' )
						.attr( {
							href: title.getUrl(),
							title: title.getMainText()
						} )
						.text( title.getMainText() ) ) );
		}
		return links;
	}
	return '0';
};

// register
registryPageInformation.register( 'basic_infos', StandardDialogs.ui.BasicInformationPage ); // eslint-disable-line no-undef
