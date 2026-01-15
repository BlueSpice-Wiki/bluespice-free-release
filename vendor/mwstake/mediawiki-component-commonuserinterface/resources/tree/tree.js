( function ( $, d ) {
	$( d ).on( 'click', '.mws-tree-expander', function ( e ) {
		stopPropagation( e );

		controls = $( this ).attr( 'aria-controls' ); // eslint-disable-line no-implicit-globals, no-undef
		toggleTreeItem( this, controls ); // eslint-disable-line no-undef

	} );

	$( d ).on( 'keydown', '.mws-tree li', function ( e ) {
		const $list = $( this ).parent( 'ul' );
		const nodes = $list.children();
		const index = nodes.index( this );

		// up
		if ( e.keyCode === 38 ) {
			stopPropagation( e );

			if ( index > 0 ) {
				const $listItem = $( nodes[ index - 1 ] );
				const $focusableElementContainer = $listItem.find( '> div' ).first();
				const focusableElement = $focusableElementContainer.children().first();
				focusableElement.focus();
			} else {
				const $parentTreeItem = $list.parents( 'li' ).first().find( '> div' ).first();
				if ( $parentTreeItem ) {
					const $focusableElement = $parentTreeItem.children().first();
					$focusableElement.trigger( 'focus' );
				}
			}
		}

		// down
		if ( e.keyCode === 40 ) {
			stopPropagation( e );

			if ( $( this ).hasClass( 'expanded' ) ) {
				const $childList = $( this ).find( '>  ul' ).first();
				const $listItem = $childList.children().first();
				const $focusableElementContainer = $listItem.find( '> div' ).first();
				const focusableElement = $focusableElementContainer.children().first();
				focusableElement.focus();
			} else if ( index < ( nodes.length - 1 ) ) {
				const $listItem = $( nodes[ index + 1 ] );
				const $focusableElementContainer = $listItem.find( '> div' ).first();
				const focusableElement = $focusableElementContainer.children().first();
				focusableElement.focus();
			} else {
				const $parentItem = $( this ).parents( 'li' ).first();
				const $parentList = $parentItem.parent( 'ul' );
				const parentNodes = $parentList.children();
				const parentIndex = parentNodes.index( $parentItem );
				if ( parentIndex < parentNodes.length ) {
					const $listItem = $( parentNodes[ parentIndex + 1 ] );
					const $focusableElementContainer = $listItem.find( '> div' ).first();
					const focusableElement = $focusableElementContainer.children().first();
					focusableElement.focus();
				}
			}
		}

		// left
		if ( e.keyCode === 37 ) {
			stopPropagation( e );
			// does not work for some reason
			if ( this.previousElementSibling ) {
				this.previousElementSibling.focus();
			}
		}

		// right
		if ( e.keyCode === 39 ) {
			stopPropagation( e );
			if ( this.nextElementSibling ) {
				this.nextElementSibling.focus();
			}
		}
	} );

	$( d ).on( 'keydown', '.mws-tree-expander', function ( e ) {
		controls = $( this ).attr( 'aria-controls' ); // eslint-disable-line no-implicit-globals, no-undef

		if ( e.keyCode === 13 ) {
			stopPropagation( e );
			toggleTreeItem( this, controls ); // eslint-disable-line no-undef
		}

		// left
		if ( e.keyCode === 37 ) {
			stopPropagation( e );
			if ( isExpanded( this ) ) {
				collapseTreeItem( this, controls ); // eslint-disable-line no-undef
			} else if ( this.previousElementSibling ) {
				this.previousElementSibling.focus();
			}
		}

		// right
		if ( e.keyCode === 39 ) {
			stopPropagation( e );
			if ( !isExpanded( this ) ) {

				expandTreeItem( this, controls ); // eslint-disable-line no-undef
			} else if ( this.nextElementSibling ) {
				this.nextElementSibling.focus();
			}
		}
	} );

	function toggleTreeItem( element, controls ) {
		if ( $( element ).attr( 'aria-expanded' ) === 'true' ) {
			collapseTreeItem( element, controls );
		} else if ( $( element ).attr( 'aria-expanded' ) === 'false' ) {
			expandTreeItem( element, controls );
		}
	}

	function collapseTreeItem( element, controls ) {
		$( '#' + controls ).removeClass( 'show' );
		$( '#' + controls ).attr( 'aria-expanded', 'false' );
		$( element ).attr( 'aria-expanded', 'false' );
		$( element ).removeClass( 'expanded' );
		$( element ).addClass( 'collapsed' );
		$( element ).parents( 'li' ).first().removeClass( 'expanded' );
	}

	function expandTreeItem( element, controls ) {
		$( '#' + controls ).addClass( 'show' );
		$( '#' + controls ).attr( 'aria-expanded', 'true' );
		$( element ).attr( 'aria-expanded', 'true' );
		$( element ).removeClass( 'collapsed' );
		$( element ).addClass( 'expanded' );
		$( element ).parents( 'li' ).first().addClass( 'expanded' );
	}

	function isExpanded( element ) {
		if ( $( element ).attr( 'aria-expanded' ) === 'true' ) {
			return true;
		}

		return false;
	}

	function stopPropagation( element ) {
		element.preventDefault();
		element.stopPropagation();
	}

}( jQuery, document ) );
