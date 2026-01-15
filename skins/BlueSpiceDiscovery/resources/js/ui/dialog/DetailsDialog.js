window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.dialog = bs.skin.dialog || {};
bs.skin.dialog.DetailsDialog = function ( cfg ) {
	cfg = cfg || {};
	this.links = cfg.links || [];
	bs.skin.dialog.DetailsDialog.super.call( this, cfg );
};
OO.inheritClass( bs.skin.dialog.DetailsDialog, OO.ui.ProcessDialog );
bs.skin.dialog.DetailsDialog.static.name = 'Skin.dialog.DetailsDialog';
bs.skin.dialog.DetailsDialog.static.title = mw.message(
	'bs-discovery-dialog-details-heading'
).text();
bs.skin.dialog.DetailsDialog.static.size = 'large';
bs.skin.dialog.DetailsDialog.static.actions = [
	{ action: 'cancel', label: mw.message( 'bs-discovery-dialog-details-action-cancel' ).text(), flags: [ 'safe', 'close' ] }
];
bs.skin.dialog.DetailsDialog.prototype.initialize = function () {
	bs.skin.dialog.DetailsDialog.super.prototype.initialize.call( this );
	const $group = $( '<div class="row row-cols-1 row-cols-md-2 g-4"></div>' ); // eslint-disable-line no-jquery/no-parse-html-literal
	const headerIdCount = 1;
	for ( const type in this.links ) {
		const headerId = 'bs-details-dlg-itm-' + headerIdCount;
		// The following messages are used here:
		// * bs-discovery-dialog-detailssectionheading-namespaces
		// * bs-discovery-dialog-detailssectionheading-views
		// * bs-discovery-dialog-detailssectionheading-actions
		// * bs-discovery-dialog-detailssectionheading-toolbox
		const header = mw.message( 'bs-discovery-dialog-detailssectionheading-' + type ).exists() ?
			mw.message( 'bs-discovery-dialog-detailssectionheading-' + type ).text() : type;
		let myskip = false;
		const $pnl = $( '<div class="card-wrapper"></div>' ); // eslint-disable-line no-jquery/no-parse-html-literal
		const $card = $( '<div class="card h-100"></div>' ); // eslint-disable-line no-jquery/no-parse-html-literal
		const $head = $( '<div id="' + headerId + '" class="card-header">' + header + '</div>' );
		const $ul = $( '<ul class="list-group mx-0" aria-labelledby="' + headerId + '"></ul>' );
		for ( let i = 0; i < this.links[ type ].length; i++ ) {
			if ( !this.links[ type ][ i ].text || this.links[ type ][ i ].text === '' ) {
				continue;
			}
			const $a = $( '<a class="list-group-item list-group-item-action">' + this.links[ type ][ i ].text + '</a>' );
			if ( this.links[ type ][ i ].id && this.links[ type ][ i ].id !== '' ) {
				if ( $( this.links[ type ][ i ].id ).length < 1 ) {
					$a.attr( 'id', this.links[ type ][ i ].id );
				} else {
					myskip = true;
				}
			}
			if ( this.links[ type ][ i ].title && this.links[ type ][ i ].title !== '' ) {
				$a.attr( 'title', this.links[ type ][ i ].title );
			}
			if ( this.links[ type ][ i ].href && this.links[ type ][ i ].href !== '' ) {
				$a.attr( 'href', this.links[ type ][ i ].href );
			} else if ( myskip ) {
				// its a hrefless id bound link that is meant to open a dialog
				// id's can not be assinged double, so we just skip them
				continue;
			}
			$a.on( 'click', () => {
				this.close( { action: 'cancel' } );
			} );
			$ul.append( $a );
		}
		const $content = $( '<div class="card-body"></div>' ); // eslint-disable-line no-jquery/no-parse-html-literal
		$content.append( $ul );
		$card.append( $head );
		$card.append( $content );
		$pnl.append( $card );
		$group.append( $pnl );
	}

	const panel = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true,
		$content: $group
	} );

	this.$body.append( panel.$element );
};

bs.skin.dialog.DetailsDialog.prototype.getActionProcess = function ( action ) {
	const dialog = this;
	if ( action ) {
		return new OO.ui.Process( () => {
			dialog.close( { action: action } );
		} );
	}
	return bs.skin.dialog.DetailsDialog.super.prototype.getActionProcess.call( this, action );
};
