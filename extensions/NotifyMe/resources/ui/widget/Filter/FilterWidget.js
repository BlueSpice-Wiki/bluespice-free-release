ext.notifyme.ui.widget.FilterWidget = function ( cfg ) {
	cfg = cfg || {};

	ext.notifyme.ui.widget.FilterWidget.super.call( this, cfg );

	this.filterData = {};
	this.activeFilter = 'title-all';

	this.$element.addClass( 'notifications-ui-widget-FilterWidget' );
};

OO.inheritClass( ext.notifyme.ui.widget.FilterWidget, OO.ui.Widget );

ext.notifyme.ui.widget.FilterWidget.prototype.loadData = function ( filterData, activeFilter ) {
	this.filterData = filterData;
	this.activeFilter = activeFilter;

	this.updateContent();
};

ext.notifyme.ui.widget.FilterWidget.prototype.updateContent = function () {
	const $content = $( '<ul>' ).addClass( 'notifications-ui-widget-FilterWidget-filters-list' )
		.attr( 'tabindex', 0 );

	for ( const filter in this.filterData ) {
		let $listItem;

		const filterType = this.filterData[ filter ].type;

		if ( !this.filterData[ filter ].hasOwnProperty( 'items' ) ) {
			$listItem = this.generateListItem( this.filterData[ filter ], filterType );
		} else {
			$listItem = this.generateSublistTitle( this.filterData[ filter ] );
		}

		$content.append( $listItem );

		// If that's multiple level filter, with sub filters.
		// For example
		//      Single level filter:
		//          Everything - 99
		//      Multiple level filter:
		//          Namespaces
		//              Namespace1 - 8
		//              Namespace2 - 6
		if ( this.filterData[ filter ].hasOwnProperty( 'items' ) ) {
			// Add sub filters

			// As soon as we should not insert "<ul>" directly as child of "<ul>" (violates WCAG)
			// Then at first wrap it into "<li>"
			const $subListWrapper = $( '<li>' ).addClass( 'notifications-ui-widget-FilterWidget-sublist-wrapper' );
			const $subList = $( '<ul>' ).addClass( 'notifications-ui-widget-FilterWidget-sublist' );

			for ( const item in this.filterData[ filter ].items ) {
				const $subListItem = this.generateListItem( this.filterData[ filter ].items[ item ], filterType );
				$subListItem.addClass( 'notifications-ui-widget-FilterWidget-sublist-item' );

				$subList.append( $subListItem );
			}

			$subListWrapper.append( $subList );

			$content.append( $subListWrapper );
		}
	}

	$( document ).on( 'click', '.notifications-ui-widget-FilterWidget-item', ( e ) => {
		const $target = $( e.target );

		if ( $target.hasClass( 'notifications-ui-widget-FilterWidget-item' ) ) {
			this.selectItem( $target );
		} else {
			const $actualItem = $target.closest( '.notifications-ui-widget-FilterWidget-item' );
			if ( $actualItem.length ) {
				this.selectItem( $actualItem );
			}
		}

		e.stopPropagation();
	} );

	// Consider selecting necessary filter option using keyboard, with "Enter"
	$( document ).on( 'keyup', '.notifications-ui-widget-FilterWidget-item', ( e ) => {
		const $target = $( e.target );

		let $actualItem;

		if ( $target.hasClass( 'notifications-ui-widget-FilterWidget-item' ) ) {
			$actualItem = $target;
		} else {
			$actualItem = $target.closest( '.notifications-ui-widget-FilterWidget-item' );
			if ( $actualItem.length ) {
				this.selectItem( $actualItem );
			}
		}

		// Just to make sure
		// But actually this condition should always evaluate
		if ( document.activeElement === $actualItem[ 0 ] ) {
			// "Enter"
			if ( e.which === 13 ) {
				this.selectItem( $actualItem );
			}
		}

		e.stopPropagation();
	} );

	this.$element.empty().append( $content );
};

/**
 * @private
 * @param {Object} item
 * @param {string} filterType
 * @return {HTMLElement}
 */
ext.notifyme.ui.widget.FilterWidget.prototype.generateListItem = function ( item, filterType ) {
	const $listItem = $( '<li>' ).addClass( 'notifications-ui-widget-FilterWidget-item' )
		.attr( 'tabindex', 0 )
		.attr(
			'aria-label',
			mw.message( 'notifyme-notification-center-filter-item-aria-label', item.label ).text()
		);

	const $itemLabel = $( '<span>' )
		.addClass( 'notifications-ui-widget-FilterWidget-item-label' )
		.html( item.label );
	const $itemCount = $( '<span>' )
		.addClass( 'notifications-ui-widget-FilterWidget-item-count' )
		.html( item.count );

	$listItem.append(
		$itemLabel, $itemCount
	);

	const filterKey = filterType + '-' + item.key;

	if ( filterKey === this.activeFilter ) {
		$listItem.addClass( 'notifications-ui-widget-FilterWidget-item-active' );

		$listItem.attr( 'aria-current', 'true' );
	}

	$listItem.attr( 'data-key', filterKey );

	return $listItem;
};

/**
 * @private
 * @param {Object} item
 * @return {HTMLElement}
 */
ext.notifyme.ui.widget.FilterWidget.prototype.generateSublistTitle = function ( item ) {
	const $sublistTitle = $( '<li>' ).addClass( 'notifications-ui-widget-FilterWidget-sublist-title' );

	const $itemLabel = $( '<span>' )
		.addClass( 'notifications-ui-widget-FilterWidget-item-label' )
		.html( item.label );

	$sublistTitle.append( $itemLabel );

	return $sublistTitle;
};

ext.notifyme.ui.widget.FilterWidget.prototype.selectItem = function ( $item ) {
	const key = $item.attr( 'data-key' );

	this.emit( 'selectItem', key );
};
