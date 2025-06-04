bs.util.registerNamespace( 'bs.contextMenu' );
bs.contextMenu.ContextMenu = function ( cfg ) {
	cfg.$overlay = true;
	cfg.padded = false;
	cfg.autoClose = true;
	cfg.autoFlip = true;
	cfg.anchor = false;
	cfg.align = 'forwards';
	cfg.width = 200;
	bs.contextMenu.ContextMenu.parent.call( this, cfg );
	this.initialized = false;
	this.hasItems = true;

	this.forTitle = cfg.forTitle || '';
	this.$element.addClass( 'bs-context-menu' );
	this.$element.attr( 'aria-busy', 'true' );
};

OO.inheritClass( bs.contextMenu.ContextMenu, OO.ui.PopupWidget );

bs.contextMenu.ContextMenu.prototype.initialize = function () {
	if ( this.initialized ) {
		return;
	}
	this.initialized = true;
	let items = [];
	bs.api.tasks.exec(
		'contextmenu',
		'getMenuItems',
		{
			title: this.forTitle
		},
		{
			success: function ( response ) {
				if ( response.payload_count === 0 ) {
					return;
				}
				items = response.payload.items;
				items.push( {
					label: mw.message( 'bs-contextmenu-open-in-new-tab' ).plain(),
					callback: function () {
						const title = mw.Title.newFromText( this.forTitle );
						if ( title ) {
							// Open url in new tab
							window.open( title.getUrl() );
							this.toggle( false );
						}
					}.bind( this ),
					position: 60,
					icon: 'newWindow'
				} );
				$( document ).trigger( 'BSContextMenuGetItems', [ this.$floatableContainer, items, this.forTitle ] );

				// Sort items by "primary" and then by position
				items.sort( ( a, b ) => {
					if ( a.primary && !b.primary ) {
						return -1;
					}
					if ( !a.primary && b.primary ) {
						return 1;
					}
					return ( a.position || 0 ) - ( b.position || 0 );
				} );
				// Check "overrides". If item overrides another item, remove the other item
				for ( let i = 0; i < items.length; i++ ) {
					const item = items[ i ];
					if ( item.overrides ) {
						for ( let j = 0; j < items.length; j++ ) {
							if ( items[ j ].id === item.overrides ) {
								items.splice( j, 1 );
								break;
							}
						}
					}
				}
				for ( let i = 0; i < items.length; i++ ) {
					const item = items[ i ];
					if ( item instanceof OO.ui.Widget ) {
						this.$body.append( item.$element );
						continue;
					}
					const btn = new OO.ui.ButtonWidget( {
						href: item.href || '',
						label: item.label || item.text,
						icon: item.icon,
						flags: item.flags || [],
						framed: false,
						classes: [ 'bs-context-menu-item' ]
					} );
					if ( item.callback ) {
						btn.connect( this, { click: item.callback } );
					}
					this.$body.append( btn.$element );
				}
				this.$element.attr( 'aria-busy', 'false' );
			}.bind( this ),
			failure: function () {
				this.showNoItems();
				this.hasItems = false;
				this.$element.attr( 'aria-busy', 'false' );
			}.bind( this )
		}
	);
};

bs.contextMenu.ContextMenu.prototype.showNoItems = function () {
	this.$body.append(
		new OO.ui.LabelWidget( {
			classes: [ 'bs-context-menu-no-items' ],
			label: mw.message( 'bs-contextmenu-no-items' ).plain()
		} ).$element
	);
};
