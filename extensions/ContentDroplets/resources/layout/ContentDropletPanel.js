ext.contentdroplets.ui.ContentDropletPanel = function ( cfg ) {
	cfg = Object.assign( {
		expanded: true,
		padded: true
	}, cfg );
	ext.contentdroplets.ui.ContentDropletPanel.parent.call( this, cfg );

	this.index = {};

	this.$element.addClass( 'droplet-dialog-panel' );
};

OO.inheritClass( ext.contentdroplets.ui.ContentDropletPanel, OO.ui.PanelLayout );

ext.contentdroplets.ui.ContentDropletPanel.prototype.init = function () {
	this.setupSearch();
	this.setupBooklet();
};

ext.contentdroplets.ui.ContentDropletPanel.prototype.setupSearch = function () {
	this.searchWidget = new OO.ui.SearchInputWidget( {
		padded: true,
		expanded: true,
		placeholder: mw.message( 'contentdroplets-droplet-dialog-search-placeholder-label' ).plain(),
		classes: [ 'droplet-search' ]
	} );
	this.searchWidget.connect( this, {
		change: 'onInput'
	} );

	this.$element.append( this.searchWidget.$element );
};

ext.contentdroplets.ui.ContentDropletPanel.prototype.setupBooklet = function () {
	this.bookletLayout = new ext.contentdroplets.ui.ContentDropletBooklet( {
		id: 'contentDroplet',
		outlined: true,
		expanded: true,
		padded: true
	} );
	this.bookletLayout.connect( this, {
		set: function () {
			const currentPage = this.bookletLayout.getCurrentPage(),
				droplet = currentPage.widgetsLayout.items[ 0 ];
			droplet.select();
		}
	} );
	this.pages = [];

	this.$element.append( this.bookletLayout.$element );

	ext.contentdroplets.getCategories().done( ( categories ) => {
		ext.contentdroplets.getDroplets().done( ( droplets ) => {

			for ( const key in categories ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( !categories.hasOwnProperty( key ) ) {
					continue;
				}
				this.createPage( key, categories[ key ], droplets );
			}
			this.indexDroplets( droplets );
			this.emit( 'dropletsAdded' );
		} );
	} );
};

ext.contentdroplets.ui.ContentDropletPanel.prototype.createPage =
	function ( categoryKey, categoryDesc, dropletSource ) {
		let page;
		dropletSource = this.sortAlphabetically( dropletSource );
		if ( categoryKey === '_all' ) {
			page = new ext.contentdroplets.ui.AllDropletsPage( categoryDesc.label, dropletSource );
		} else {
			page = new ext.contentdroplets.ui.ContentDropletPage(
				categoryKey, categoryDesc.label,
				this.dropletsFromSource( categoryDesc.droplets, dropletSource )
			);
		}
		page.connect( this, {
			dropletSelected: 'selectDroplet'
		} );
		this.bookletLayout.addPages( [ page ] );
	};

ext.contentdroplets.ui.ContentDropletPanel.prototype.selectDroplet =
	function ( key, droplet ) {
		if ( this.selectedDroplet ) {
			this.selectedDroplet.deselect();
		}
		this.selectedDroplet = droplet;
		this.emit( 'dropletSelected', this.selectedDroplet );
	};

ext.contentdroplets.ui.ContentDropletPanel.prototype.dropletsFromSource =
	function ( keys, source ) {
		const filtered = {};
		let sourceKey;
		for ( sourceKey in source ) {
			// eslint-disable-next-line no-prototype-builtins
			if ( !source.hasOwnProperty( sourceKey ) ) {
				continue;
			}
			if ( keys.indexOf( sourceKey ) !== -1 ) {
				filtered[ sourceKey ] = source[ sourceKey ];
			}
		}
		return filtered;
	};

ext.contentdroplets.ui.ContentDropletPanel.prototype.indexDroplets = function ( droplets ) {
	let key;
	for ( key in droplets ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !droplets.hasOwnProperty( key ) ) {
			continue;
		}
		this.index[ key ] = droplets[ key ].getSearchText();
	}
};

ext.contentdroplets.ui.ContentDropletPanel.prototype.onInput = function ( input ) {
	const toShow = [];
	const allPage = this.bookletLayout.getPage( '_all' );
	let key;
	input = input.toLocaleLowerCase().trim();

	if ( input ) {
		for ( key in this.index ) {
			// eslint-disable-next-line no-prototype-builtins
			if ( !this.index.hasOwnProperty( key ) ) {
				continue;
			}

			if ( this.index[ key ].includes( input ) ) {
				toShow.push( key );
			}
		}
		allPage.filter( toShow );
	} else {
		allPage.removeFilter();
	}
	this.bookletLayout.setPage( '_all' );
	this.searchWidget.focus();
};

ext.contentdroplets.ui.ContentDropletPanel.prototype.resetSearch = function () {
	const page = this.bookletLayout.getCurrentPageName();
	this.searchWidget.setValue( '' );
	this.bookletLayout.setPage( page );
};

ext.contentdroplets.ui.ContentDropletPanel.prototype.sortAlphabetically =
	function ( dropletSource ) {
		const ordered = {};
		Object.keys( dropletSource ).sort( ( a, b ) => {
			const nameA = dropletSource[ a ].name.toLowerCase(),
				nameB = dropletSource[ b ].name.toLowerCase();
			if ( nameA < nameB ) {
				// nameA comes before nameB in alphabetical order
				return -1;
			}
			if ( nameA > nameB ) {
				// nameA comes after nameB in alphabetical order
				return 1;
			}
			// names are equal
			return 0;
		} ).forEach( ( key ) => {
			ordered[ key ] = dropletSource[ key ];
		} );

		return ordered;
	};
