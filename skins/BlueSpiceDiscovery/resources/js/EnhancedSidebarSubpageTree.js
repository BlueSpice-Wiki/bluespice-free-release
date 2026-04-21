$( () => {

	function setExpandedState( expand, key, name ) {
		const expandedItems = localStorage.getItem( 'expanded-navigation-tree' );
		let expandedItemsList = {};
		if ( expandedItems !== null ) {
			expandedItemsList = JSON.parse( expandedItems );
		}
		if ( expand ) {
			if ( expandedItemsList.hasOwnProperty( key ) ) {
				const index = expandedItemsList[ key ].indexOf( name );
				if ( index === -1 ) {
					expandedItemsList[ key ].push( name );
				}
			} else {
				expandedItemsList[ key ] = [];
				expandedItemsList[ key ].push( name );
			}
		} else {
			if ( expandedItemsList.hasOwnProperty( key ) ) {
				const index = expandedItemsList[ key ].indexOf( name );
				if ( index > -1 ) {
					expandedItemsList[ key ].splice( index, 1 );
					for ( let item = expandedItemsList[ key ].length - 1; item >= 0; item-- ) {
						if ( expandedItemsList[ key ][ item ].startsWith( name ) ) {
							const itemIndex = expandedItemsList[ key ].indexOf( expandedItemsList[ key ][ item ] );
							if ( itemIndex > -1 ) {
								expandedItemsList[ key ].splice( itemIndex, 1 );
							}
						}
					}
				}
				if ( expandedItemsList[ key ].length === 0 ) {
					delete ( expandedItemsList[ key ] );
				}
			}
		}
		if ( Object.keys( expandedItemsList ).length === 0 ) {
			localStorage.removeItem( 'expanded-navigation-tree' );
		} else {
			localStorage.setItem( 'expanded-navigation-tree', JSON.stringify( expandedItemsList ) );
		}
	}

	$( '.mws-tree-item[data-root] .mws-tree-expander' ).on( 'click', ( function () {
		const $parent = $( $( this ).parent()[ 0 ] ).parent()[ 0 ];
		const rootValue = $( $parent ).data( 'root' );
		if ( rootValue === '' ) {
			return;
		}

		const $subElement = $( $parent ).find( '> div.oojsplus-data-navigation-tree' );
		const key = $( $parent ).attr( 'id' );
		const depth = $( $parent ).data( 'depth' );
		if ( $subElement.length === 0 ) {
			const skeleton = new OOJSPlus.ui.widget.SkeletonWidget( {
				variant: 'list',
				rows: 3,
				visible: true
			} );
			$( $parent ).append( skeleton.$element );
			$( $parent ).attr( 'aria-busy', true );
			const expandPaths = [];

			const expandedItems = localStorage.getItem( 'expanded-navigation-tree' );
			if ( expandedItems !== null ) {
				const expandedItemsArray = JSON.parse( expandedItems );
				if ( !expandedItemsArray.hasOwnProperty( key ) ) {
					expandedItemsArray[ key ] = [];
				}
				for ( let i = 0; i < expandedItemsArray[ key ].length; i++ ) {
					expandPaths.push( expandedItemsArray[ key ][ i ] );
				}
			}

			const subpageStore = new OOJSPlus.ui.data.store.NavigationTreeStore( {
				path: 'mws/v1/title-tree-store'
			} );
			subpageStore.getExpandedPath( rootValue, expandPaths ).done( ( data ) => {
				const pageTree = new OOJSPlus.ui.data.NavigationTree( {
					style: {
						IconExpand: 'next',
						IconCollapse: 'expand'
					},
					data: data,
					allowDeletions: false,
					allowAdditions: false,
					store: subpageStore,
					includeRedirect: false,
					localStorageKey: key,
					stateful: true,
					maxLevel: depth - 1
				} );
				const $treeList = $( pageTree.$element ).find( 'ul' );
				$( $treeList ).attr( 'role', 'group' );
				$( $parent ).append( pageTree.$element );
				skeleton.hide();
				$( $parent ).removeAttr( 'aria-busy' );
				setExpandedState( true, key, rootValue );
			} );
		} else {
			let setToExpand = false;
			if ( $( $parent ).hasClass( 'expanded' ) ) {
				$( $subElement ).hide();
			} else {
				$( $subElement ).show();
				setToExpand = true;
			}
			setExpandedState( setToExpand, key, rootValue );
		}
	} ) );

	$( '.mws-tree-item[data-root]' ).each( function () {
		const rootValue = $( this ).data( 'root' );
		if ( rootValue === '' ) {
			return;
		}

		const expandedItems = localStorage.getItem( 'expanded-navigation-tree' );
		if ( !expandedItems ) {
			return;
		}
		const expandedItemsList = JSON.parse( expandedItems );
		const key = $( this ).attr( 'id' );
		if ( !expandedItemsList.hasOwnProperty( key ) ) {
			return;
		}
		const index = expandedItemsList[ key ].indexOf( rootValue );
		if ( index < 0 ) {
			return;
		}
		const depth = $( this ).data( 'depth' );
		const $expander = $( this ).find( '.mws-tree-expander' );
		if ( depth <= 1 ) {
			$( $expander ).hide();
			return;
		}

		$( $expander ).trigger( 'click' );
	} );

} );
