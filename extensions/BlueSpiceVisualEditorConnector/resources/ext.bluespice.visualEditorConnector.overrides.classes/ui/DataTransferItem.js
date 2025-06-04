/**
 * Override normal VE DataTranferItem (for files)
 *
 * @param {DataTransferItem} item Native data transfer item
 * @param {string} [htmlStringData] HTML string representation of data transfer
 * @return {bs.vec.ui.DataTransferItem}
 */
ve.ui.DataTransferItem.static.newFromItem = function ( item, htmlStringData ) {
	return new bs.vec.ui.DataTransferItem(
		item.kind, item.type,
		{ item: item, htmlStringData: htmlStringData }, item.getAsFile().name
	);
};

bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.DataTransferItem = function ( kind, type, data, name ) {
	bs.vec.ui.DataTransferItem.parent.call( this, kind, type, data, name );
};

OO.inheritClass( bs.vec.ui.DataTransferItem, ve.ui.DataTransferItem );

bs.vec.ui.DataTransferItem.prototype.getAsFile = function () {
	if ( this.data.item ) {
		const blob = this.data.item.getAsFile();
		if ( blob instanceof File ) {
			return this.getCustomNameFile( blob );

		}
		return blob;
	}

	return bs.vec.ui.DataTransferItem.parent.prototype.getAsFile();
};

bs.vec.ui.DataTransferItem.prototype.getCustomNameFile = function ( blob ) {
	return new File(
		[ blob ],
		this.getFormattedFileName(),
		{ type: blob.type }
	);
};

bs.vec.ui.DataTransferItem.prototype.getFormattedFileName = function () {
	let filename = bs.vec.config.get( 'PasteFilename' );
	if ( !filename ) {
		return this.name;
	}

	filename = this.substitute( filename );

	const extension = this.getExtension();
	return [ filename, extension ].join( '.' );
};

bs.vec.ui.DataTransferItem.prototype.substitute = function ( filename ) {
	const regex = /{{(.*?)}}/g,
		matches = Array.from( filename.matchAll( regex ), ( m ) => ( { fullMatch: m[ 0 ], group: m[ 1 ] } ) );

	matches.forEach( ( match ) => {
		const key = match.group;
		switch ( key ) {
			case 'pagename':
				filename = filename.replace( match.fullMatch, this.getEscapedPageName() );
				break;
			case 'timestamp':
				filename = filename.replace( match.fullMatch, Date.now() );
				break;
			case 'random':
				filename = filename.replace(
					match.fullMatch,
					Math.floor( Math.random() * ( 999999 - 999 + 1 ) ) + 999
				);
				break;
		}
	} );

	return filename;
};

bs.vec.ui.DataTransferItem.prototype.getEscapedPageName = function () {
	const pagename = mw.config.get( 'wgPageName' );
	return pagename.replace( ':', '-' ).replace( '/', '-' );
};
