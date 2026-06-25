ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.data = ext.enhancedUI.data || {};
ext.enhancedUI.data.store = ext.enhancedUI.data.store || {};

ext.enhancedUI.data.store.Store = function () {
	const cfg = {};
	cfg.path = 'mws/v1/title-tree-store';
	cfg.request = null;
	ext.enhancedUI.data.store.Store.parent.call( this, cfg );
	this.limit = 100;
	this.loadedCount = 0;
	this.sortkeyFilterActive = null;
};

OO.inheritClass( ext.enhancedUI.data.store.Store, OOJSPlus.ui.data.store.RemoteRestStore );

ext.enhancedUI.data.store.Store.prototype.doLoadData = function () {
	const dfd = $.Deferred(),
		data = {
			limit: this.limit,
			filter: this.getFiltersForRemote(),
			query: this.getQuery(),
			sort: this.getSortForRemote(),
			node: this.node,
			continue: this.continue ? JSON.stringify( this.continue ) : undefined
		};

	this.request = $.ajax( {
		method: 'GET',
		url: mw.util.wikiScript( 'rest' ) + '/' + this.path,
		data: data,
		contentType: 'application/json',
		dataType: 'json',
		beforeSend: function () {
			if ( this.request ) {
				this.request.abort();
			}
		}.bind( this )
	} ).done( ( response ) => {
		this.request = null;

		if ( response.hasOwnProperty( 'results' ) ) {
			if ( !data.node ) {
				this.loadedCount = this.loadedCount + response.results.length;
				this.emit( 'onBucketChange', response.buckets || {} );
				this.emit( 'metadataChange', {
					results: this.loadedCount,
					total: response.total,
					totalApproximate: response.total_approximate,
					continue: response.continue,
					pageSize: this.limit
				} );
			}
			this.total = response.total;
			dfd.resolve( this.indexData( response.results ) );
			return;
		}
		dfd.reject();
	} ).fail( ( jgXHR, type, status ) => {
		this.request = null;
		dfd.reject( { type: type, status: status } );
	} );

	return dfd.promise();
};

ext.enhancedUI.data.store.Store.prototype.loadNS = function ( nsId ) {
	this.filters = [ {
		operator: 'eq',
		value: nsId,
		property: 'namespace',
		type: 'numeric'
	} ];
	this.node = '';
	this.continue = [];
	this.limit = 100;
	this.loadedCount = 0;
	this.sortkeyFilterActive = null;
	return this.reload();
};

ext.enhancedUI.data.store.Store.prototype.loadForSortkey = function ( sortkey ) {
	for ( let i = 0; i < this.filters.length; i++ ) {
		if ( this.filters[ i ].property === 'sortkey' ) {
			this.filters.splice( i, 1 );
			break;
		}
	}
	this.filters.push( {
		operator: 'eq',
		value: sortkey,
		property: 'sortkey',
		type: 'string'
	} );
	this.node = '';
	this.continue = [];
	this.limit = 100;
	this.loadedCount = 0;
	this.sortkeyFilterActive = sortkey;
	return this.reload();
};

ext.enhancedUI.data.store.Store.prototype.removeSortkeyFilter = function () {
	for ( let i = 0; i < this.filters.length; i++ ) {
		if ( this.filters[ i ].property === 'sortkey' ) {
			this.filters.splice( i, 1 );
			break;
		}
	}
	this.node = '';
	this.continue = [];
	this.limit = 100;
	this.loadedCount = 0;
	this.sortkeyFilterActive = null;
	return this.reload();
};

ext.enhancedUI.data.store.Store.prototype.getFiltersForRemote = function () {
	if ( this.filters.length > 0 ) {
		return JSON.stringify( this.filters );
	}
};

ext.enhancedUI.data.store.Store.prototype.getSortForRemote = function () {
	return JSON.stringify( [ { property: 'sortkey', direction: 'ASC' }, { property: 'dbkey', direction: 'ASC' } ] );
};

ext.enhancedUI.data.store.Store.prototype.getSubpages = function ( pageName ) {
	this.node = pageName;
	this.filters = [];
	this.limit = 999;
	this.offset = 0;

	return this.reload();
};

ext.enhancedUI.data.store.Store.prototype.reload = function () {
	this.data = {};
	const loadPromise = this.load();
	loadPromise.done( ( data ) => {
		this.emit( 'reload', data );
	} );

	return loadPromise;
};

ext.enhancedUI.data.store.Store.prototype.loadPages = function ( nsId, search ) {
	this.filters = [
		{
			operator: 'eq',
			value: nsId,
			property: 'namespace',
			type: 'numeric'
		}
	];
	this.node = '';
	this.loadedCount = 0;
	this.sortkeyFilterActive = null;
	return this.query( search );
};
