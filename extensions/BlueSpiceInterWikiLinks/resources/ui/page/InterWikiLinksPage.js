bs.util.registerNamespace( 'bs.interwikilinks.ui.page' );

bs.interwikilinks.ui.page.InterWikiLinksPage = function ( cfg ) {
	cfg = cfg || {};
	this.title = cfg.data.title || mw.message( 'bs-interwikilinks-titleaddinterwikilink' ).text();
	this.oldPrefix = cfg.data.oldPrefix || '';

	bs.interwikilinks.ui.page.InterWikiLinksPage.parent.call( this, 'interwikilinks', cfg );
};

OO.inheritClass( bs.interwikilinks.ui.page.InterWikiLinksPage, OOJSPlus.ui.booklet.DialogBookletPage );

bs.interwikilinks.ui.page.InterWikiLinksPage.prototype.getItems = function () {
	this.prefix = new OO.ui.TextInputWidget( {
		required: true
	} );
	this.url = new OO.ui.TextInputWidget( {
		required: true
	} );

	this.prefixLayout = new OO.ui.FieldLayout( this.prefix, {
		label: mw.message( 'bs-interwikilinks-labelprefix' ).text(),
		align: 'top'
	} );
	this.urlLayout = new OO.ui.FieldLayout( this.url, {
		label: mw.message( 'bs-interwikilinks-labelurl' ).text(),
		align: 'top'
	} );

	return [
		this.prefixLayout,
		this.urlLayout
	];
};

bs.interwikilinks.ui.page.InterWikiLinksPage.prototype.setData = function ( data ) {
	if ( data.hasOwnProperty( 'prefix' ) ) { // eslint-disable-line no-prototype-builtins
		this.prefix.setValue( data.prefix );
	}
	if ( data.hasOwnProperty( 'url' ) ) { // eslint-disable-line no-prototype-builtins
		this.url.setValue( data.url );
	}
};

bs.interwikilinks.ui.page.InterWikiLinksPage.prototype.getTitle = function () {
	return this.title;
};

bs.interwikilinks.ui.page.InterWikiLinksPage.prototype.getActionKeys = function () {
	return [ 'cancel', 'done' ];
};

bs.interwikilinks.ui.page.InterWikiLinksPage.prototype.getAbilities = function () {
	return { done: true, cancel: true };
};

bs.interwikilinks.ui.page.InterWikiLinksPage.prototype.onAction = function ( action ) {
	const dfd = $.Deferred();
	this.urlLayout.setErrors( [] );
	this.prefixLayout.setErrors( [] );

	if ( action === 'done' ) {
		bs.api.tasks.execSilent(
			'interwikilinks',
			'editInterWikiLink',
			{
				prefix: this.prefix.getValue(),
				url: this.url.getValue(),
				oldPrefix: this.oldPrefix
			}, {
				success: () => {
					dfd.resolve( { action: 'close', data: { success: true } } );
				},
				failure: ( response ) => {
					if ( response.errors[ 0 ].id === 'iwediturl' ) {
						this.url.setValidityFlag( false );
						this.urlLayout.setErrors( [ response.errors[ 0 ].message ] );
						dfd.resolve( {} );
					} else if ( response.errors[ 0 ].id === 'iweditprefix' ) {
						this.prefix.setValidityFlag( false );
						this.prefixLayout.setErrors( [ response.errors[ 0 ].message ] );
						dfd.resolve( {} );
					} else {
						dfd.reject( response.errors[ 0 ].message );
					}
				}
			}
		);
	} else {
		return bs.interwikilinks.ui.page.InterWikiLinksPage.parent.prototype.onAction.call( this, action );
	}

	return dfd.promise();
};
