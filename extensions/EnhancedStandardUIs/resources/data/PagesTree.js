ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.data = ext.enhancedUI.data || {};

require( './PagesTreeItem.js' );

ext.enhancedUI.data.PagesTree = function ( cfg ) {
	cfg = cfg || {};
	cfg.classes = [ 'enhanced-pages-tree' ];
	cfg.allowDeletions = false;
	cfg.allowAdditions = false;
	this.includeRedirect = cfg.includeRedirect || false;
	cfg.data = this.prepareData( cfg.pages );
	cfg.fixed = true;

	ext.enhancedUI.data.PagesTree.super.call( this, cfg );

	this.store = cfg.store;
};

OO.inheritClass( ext.enhancedUI.data.PagesTree, OOJSPlus.ui.data.Tree );

ext.enhancedUI.data.PagesTree.prototype.build = function ( data, lvl ) {
	const nodes = {};
	lvl = lvl || 0;
	for ( let i = 0; i < data.length; i++ ) {
		const item = data[ i ];
		let isLeaf = true;
		let expanded = false;

		// eslint-disable-next-line no-prototype-builtins
		if ( ( item.hasOwnProperty( 'leaf' ) && item.leaf === false ) &&
			// eslint-disable-next-line no-prototype-builtins
			( item.hasOwnProperty( 'children' ) && item.children.length > 0 )
		) {
			isLeaf = false;
			expanded = true;
		}
		const widget = this.createItemWidget( item, lvl, isLeaf,
			this.idGenerator.generate(), expanded );
		widget.connect( this, {
			selected: function ( element ) {
				this.setSelected( element );
			}
		} );

		// eslint-disable-next-line es-x/no-array-prototype-flat
		this.flat[ widget.getName() ] = widget;
		nodes[ widget.getName() ] = {
			widget: widget,
			children: !isLeaf ? this.build( item.children || [], lvl + 1 ) : {}
		};
	}

	return nodes;
};

ext.enhancedUI.data.PagesTree.prototype.createItemWidget = function (
	item, lvl, isLeaf, labelledby, expanded ) {
	return new ext.enhancedUI.data.PagesTreeItem( Object.assign( {}, {
		level: lvl,
		leaf: isLeaf,
		tree: this,
		labelledby: labelledby,
		expanded: expanded,
		style: this.style
	}, item ) );
};

ext.enhancedUI.data.PagesTree.prototype.expandNode = function ( name ) {
	const node = this.getItem( name );
	if ( !node ) {
		return;
	}

	const $element = node.$element.find( '> ul.tree-node-list' );
	if ( $( $element[ 0 ] ).children().length === 0 ) {
		this.store.getSubpages( node.elementId ).done( ( result ) => {
			const data = this.prepareData( result );
			const nodes = this.build( data, node.level + 1 );

			for ( const nodeElement in nodes ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( !nodes.hasOwnProperty( nodeElement ) ) {
					continue;
				}
				const $li = nodes[ nodeElement ].widget.$element;
				const $labelEl = $( $li ).find( '> div > .oojsplus-data-tree-label' );
				const itemId = $labelEl.attr( 'id' );
				$li.append( this.doDraw( nodes[ nodeElement ].children || {},
					nodes[ nodeElement ].widget, itemId, this.expanded ) );
				$( $element ).append( $li );
				this.reEvaluateParent( nodeElement );
				$( $element ).show();
			}
		} );
	} else {
		$( $element ).show();
	}
};

ext.enhancedUI.data.PagesTree.prototype.setIncludeRedirect = function ( redirect ) {
	this.includeRedirect = redirect;
};

ext.enhancedUI.data.PagesTree.prototype.prepareData = function ( pages ) {
	const data = [];
	for ( const i in pages ) {
		const title = pages[ i ].title.split( '/' );
		let label = title[ title.length - 1 ];
		const classes = [];
		if ( !this.includeRedirect && pages[ i ].redirect === true ) {
			continue;
		}
		if ( label === '' ) {
			label = mw.message( 'enhanced-standard-uis-allpages-untitled' ).text();
			classes.push( 'enhancedui-allpages-untitled-page' );
		}

		if ( !pages[ i ].allows_subpages ) {
			label = pages[ i ].title;
		}

		// eslint-disable-next-line mediawiki/class-doc
		const entry = {
			id: pages[ i ].id,
			title: pages[ i ].prefixed,
			name: pages[ i ].id,
			href: pages[ i ].url,
			leaf: pages[ i ].leaf,
			label: label,
			exists: pages[ i ].exists,
			redirect: pages[ i ].redirect,
			classes: classes
		};
		if ( pages[ i ].children.length > 0 ) {
			entry.children = this.prepareData( pages[ i ].children );
		}
		if ( pages[ i ].watch !== undefined ) {
			entry.watch = pages[ i ].watch;
		}
		data.push( entry );
	}
	return data;
};
