ext.menueditor.ui.data.tree.Tree = function ( cfg ) {
	this.editable = cfg.editable || false;
	if ( !this.editable ) {
		cfg.allowAdditions = false;
		cfg.allowDeletions = false;
		cfg.fixed = true;
	} else {
		cfg.$containTo = null;
	}

	ext.menueditor.ui.data.tree.Tree.parent.call( this, cfg );
};

OO.inheritClass( ext.menueditor.ui.data.tree.Tree, OOJSPlus.ui.data.Tree );

ext.menueditor.ui.data.tree.Tree.prototype.createItemWidget = function ( item, lvl, isLeaf ) {
	let classname = ext.menueditor.registry.node.registry[ item.type ];
	if ( !classname ) {
		// eslint-disable-next-line no-console
		console.error( 'Node of type ' + item.type + ' is not registered' );
		throw new Error();
	}

	classname = ext.menueditor.util.callbackFromString( classname );
	const maxLevels = this.getMaxLevels();
	// eslint-disable-next-line new-cap
	return new classname( {
		name: this.randomName( item.type ),
		level: lvl,
		tree: this,
		nodeData: item,
		leaf: isLeaf,
		allowAdditions: this.allowAdditions && ( maxLevels ? lvl + 1 < maxLevels : true ) && classname.static.canHaveChildren,
		allowEdits: this.editable,
		allowDeletions: this.allowDeletions
	} );
};

ext.menueditor.ui.data.tree.Tree.prototype.randomName = function ( type ) {
	return type + Math.floor( Math.random() * 99999 );
};

ext.menueditor.ui.data.tree.Tree.prototype.editNode = function ( node ) {
	this.openNodeDialog( node ).closed.then( ( data ) => {
		if ( data && data.action === 'done' ) {
			this.onNodeEdited( data.data, data.node );
		}
	} );
};

ext.menueditor.ui.data.tree.Tree.prototype.openNodeDialog = function ( node, cfg ) {
	const windowManager = new OO.ui.WindowManager();
	$( document.body ).append( windowManager.$element );

	const dialog = new ext.menueditor.ui.dialog.NodeDialog( Object.assign( { size: 'large' }, cfg || {} ), node );
	windowManager.addWindows( [ dialog ] );
	return windowManager.openWindow( dialog );
};

ext.menueditor.ui.data.tree.Tree.prototype.getDataFromUser = function ( parentName ) {
	const dfd = $.Deferred();

	const parent = this.getItem( parentName );
	let lvl = 0;
	if ( parent ) {
		lvl = parent.getLevel() + 1;
	}

	this.openNodeDialog( null, {
		allowedNodes: this.getPossibleNodesForLevel( lvl ),
		size: 'large',
		tree: this
	} ).closed.then( ( data ) => {
		if ( data && data.action === 'done' ) {
			dfd.resolve( data.data );
		}
	} );
	return dfd.promise();
};

ext.menueditor.ui.data.tree.Tree.prototype.onDragStart = function ( item, $target, e, ui ) {
	// Here we disable certain levels based on type of the node being dragged
	this.$itemsContainer.find( 'ul.tree-sortable' ).each( ( i, el ) => {
		const lvl = $( el ).data( 'level' ),
			allowed = this.getPossibleNodesForLevel( lvl );
		if (
			( allowed.length > 0 && allowed.indexOf( item.getNodeData().type ) === -1 ) ||
			( this.getMaxLevels() && lvl >= this.getMaxLevels() ) ||
			( !this.isLeaf( item.getName() ) && !this.allowedNestedDrag( item ) )
		) {
			$( el ).sortable( 'disable' );
		} else {
			$( ui.sender ).sortable( 'refresh' );
			$( el ).sortable( 'refresh' );
			$target.sortable( 'refresh' );
		}
	} );
};

ext.menueditor.ui.data.tree.Tree.prototype.allowedNestedDrag = function ( item ) {
	// Level of deepest child (root = 0) + 1 because max levels start at 1
	// (2 levels means root(0) + 1 sublevel)
	return this.getMaxNestedLevelForItem( item ) + 1 <= this.getMaxLevels();
};

ext.menueditor.ui.data.tree.Tree.prototype.getMaxNestedLevelForItem = function ( item ) {
	const children = item.getChildren();
	let level = item.getLevel();
	for ( let i = 0; i < children.length; i++ ) {
		level = this.getMaxNestedLevelForItem( children[ i ] );
	}
	return level;
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.tree.Tree.prototype.onDragStop = function ( item, $target, e, ui ) {
	this.$itemsContainer.find( 'ul.ui-sortable-disabled' ).sortable( 'enable' );
};

ext.menueditor.ui.data.tree.Tree.prototype.onNodeEdited = function ( data, node ) {
	node.updateData( data );
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.tree.Tree.prototype.getPossibleNodesForLevel = function ( lvl ) {
	return [];
};

ext.menueditor.ui.data.tree.Tree.prototype.getMaxLevels = function () {
	return false;
};

ext.menueditor.ui.data.tree.Tree.prototype.getNodes = function () {
	const nodes = ext.menueditor.ui.data.tree.Tree.parent.prototype.getNodes.call( this );
	return nodes.map( ( e ) => Object.assign( e.getNodeData(), { level: e.getLevel() + 1 } ) );
};
