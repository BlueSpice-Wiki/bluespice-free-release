flexiskin.ui.plugin.Plugin = function () {
	this.controls = this.provideControls();
};

OO.initClass( flexiskin.ui.plugin.Plugin );

flexiskin.ui.plugin.Plugin.prototype.getControls = function () {
	return this.controls;
};

flexiskin.ui.plugin.Plugin.prototype.provideControls = function () {
	// STUB - has to be overriden by subclasses
	return {};
};

flexiskin.ui.plugin.Plugin.prototype.getPluginLabel = function () {
	return '';
};

flexiskin.ui.plugin.Plugin.prototype.getFlatList = function () {
	this.labels = {};
	this.actionCallbacks = {};

	const flat = {};

	return Object.assign( flat, this.parseFlat( this.controls ) );
};

flexiskin.ui.plugin.Plugin.prototype.parseFlat = function ( controls, groups ) {
	groups = groups || [];
	let result = {};
	if ( $.type( controls ) !== 'object' ) { // eslint-disable-line no-jquery/no-type
		return result;
	}

	if ( controls.hasOwnProperty( 'items' ) ) {
		result = $.extend( true, {}, result, this.getItemsForObject( controls.items, groups ) );
	} else {
		for ( const id in controls ) {
			const subGroups = groups.concat( [] );
			if ( !controls.hasOwnProperty( id ) ) {
				continue;
			}
			if ( $.type( controls[ id ] ) !== 'object' ) { // eslint-disable-line no-jquery/no-type
				continue;
			}

			if ( controls[ id ].hasOwnProperty( 'items' ) ) {
				subGroups.push( id );
				result = $.extend( true, {}, result, this.getItemsForObject( controls[ id ].items, subGroups ) );
			} else if ( controls[ id ] instanceof OO.ui.Widget ) {
				result = $.extend( true, {}, result, this.getWidgetForItem( controls[ id ], subGroups, id ) );
			} else if ( controls[ id ].hasOwnProperty( 'widget' ) ) {
				this.addData( controls[ id ], id );
				result = $.extend( true, {}, result, this.getWidgetForItem( controls[ id ].widget, subGroups, id ) );
			} else {
				subGroups.push( id );
				result = $.extend( true, {}, result, this.parseFlat( controls[ id ], subGroups ) );
			}
		}
	}

	return result;
};

flexiskin.ui.plugin.Plugin.prototype.getItemsForObject = function ( controls, groups ) {
	let result = {};
	for ( const itemId in controls ) {
		if ( !controls.hasOwnProperty( itemId ) ) {
			continue;
		}

		if ( controls[ itemId ] instanceof OO.ui.Widget ) {
			result = Object.assign( result, this.getWidgetForItem( controls[ itemId ], groups, itemId ) );
			continue;
		}

		if ( $.type( controls[ itemId ] ) !== 'object' ) { // eslint-disable-line no-jquery/no-type
			// Don't know what this is
			continue;
		}
		if ( controls[ itemId ].hasOwnProperty( 'widget' ) ) {
			this.addData( controls[ itemId ], itemId );
			result = Object.assign( result, this.getWidgetForItem( controls[ itemId ].widget, groups, itemId ) );
			continue;
		}
		groups.push( itemId );
		result = Object.assign( result, this.parseFlat( controls[ itemId ], groups ) );
	}

	return result;
};

flexiskin.ui.plugin.Plugin.prototype.addData = function ( item, id ) {
	if ( item.hasOwnProperty( 'label' ) ) {
		const label = item.label;
		if ( label ) {
			this.labels[ id ] = label;
		}
	}

	if ( item.hasOwnProperty( 'actionCallback' ) ) {
		this.actionCallbacks[ id ] = item.actionCallback;
	}
};

flexiskin.ui.plugin.Plugin.prototype.getWidgetForItem = function ( widget, groups, itemId ) {
	const result = {}, widgetData = {};
	if ( this.labels.hasOwnProperty( itemId ) ) {
		widgetData.label = this.labels[ itemId ];
	}

	if ( this.actionCallbacks.hasOwnProperty( itemId ) ) {
		widgetData.actionCallback = this.actionCallbacks[ itemId ];
	}

	widget.setData( widgetData );
	result[ this.makeId( groups, itemId ) ] = widget;
	return result;
};

flexiskin.ui.plugin.Plugin.prototype.makeId = function ( groups, id ) {
	if ( groups.length === 0 ) {
		return id;
	}
	return groups.join( '/' ) + '/' + id;
};
