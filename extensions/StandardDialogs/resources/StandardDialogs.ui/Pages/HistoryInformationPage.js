StandardDialogs.ui.HistoryInformationPage = function HistoryInformationPage( name, config ) {
	StandardDialogs.ui.HistoryInformationPage.super.call( this, name, config );
};

OO.inheritClass( StandardDialogs.ui.HistoryInformationPage, StandardDialogs.ui.BasePage );

StandardDialogs.ui.HistoryInformationPage.prototype.setupOutlineItem = function () {
	StandardDialogs.ui.HistoryInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

	if ( this.outlineItem ) {
		this.outlineItem.setLabel( mw.message( 'standarddialogs-page-info-history' ).plain() );
	}
};

StandardDialogs.ui.HistoryInformationPage.prototype.setup = function () {
	const me = this;
	const dfdData = this.getData();
	$.when( dfdData ).done( () => {
		if ( me.pageInfo !== undefined ) {
			const fieldLayout = new OO.ui.FieldsetLayout();
			const contentTable = $( '<table>' );
			contentTable.addClass( 'wikitable page-information' );

			for ( const p in me.pageInfo ) {
				const contrib = me.getEditors( p );
				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-contributors' ).plain() ),
						$( '<td>' ).append( contrib ) ) );

				const dateTime = new Date( me.pageInfo[ p ].revisions[ 0 ].timestamp );
				dateTime.toLocaleTimeString();

				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-last-edit' ).plain() ),
						$( '<td>' ).text( dateTime ) ) );

				const user = new OOJSPlus.ui.widget.UserWidget( {
					user_name: me.pageInfo[ p ].revisions[ 0 ].user, // eslint-disable-line camelcase
					showImage: true,
					showLink: true,
					showRawUsername: false
				} );
				contentTable.append(
					$( '<tr>' ).append(
						$( '<th>' ).text( mw.message( 'standarddialogs-page-info-page-last-editor' ).plain() ),
						$( '<td>' ).append( user.$element ) ) );
			}
			fieldLayout.$element.append( contentTable );

			me.$element.append( fieldLayout.$element );
		}
	} );
};

StandardDialogs.ui.HistoryInformationPage.prototype.getEditors = function ( id ) {
	const editors = $( '<ul>' );
	editors.addClass( 'editors-list' );
	if ( this.pageInfo[ id ].contributors ) {
		for ( const c in this.pageInfo[ id ].contributors ) {
			const userWidget = new OOJSPlus.ui.widget.UserWidget( {
				user_name: this.pageInfo[ id ].contributors[ c ].name, // eslint-disable-line camelcase
				showImage: true,
				showLink: true,
				showRawUsername: false
			} );
			editors.append(
				$( '<li>' )
					.append( userWidget.$element )
			);
		}
	}
	return editors;
};

StandardDialogs.ui.HistoryInformationPage.prototype.getData = function () {
	const me = this;
	const dfd = new $.Deferred();

	const mwApi = new mw.Api();
	mwApi.postWithToken( 'csrf', {
		action: 'query',
		titles: me.pageName,
		format: 'json',
		prop: 'contributors|info|revisions'

	} ).fail( function () {
		dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
	} )
		.done( function ( resp ) {
			me.pageInfo = resp.query.pages;
			dfd.resolve( arguments );
		} );
	return dfd.promise();
};

// register
registryPageInformation.register( 'history_infos', StandardDialogs.ui.HistoryInformationPage ); // eslint-disable-line no-undef
