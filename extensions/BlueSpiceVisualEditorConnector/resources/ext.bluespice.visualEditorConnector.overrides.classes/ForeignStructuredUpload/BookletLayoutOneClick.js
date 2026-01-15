bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick = function BsVecUiForeignStructuredUploadBookletLayoutOneClick( config ) {
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick, bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple );

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.prototype.setPage = function ( bookletpagename ) {
	if ( bookletpagename === 'info' ) {
		return;
	}
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.prototype.setPage.apply( this, [ bookletpagename ] );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.prototype.setFilename = function ( filename ) {
	// Generate random name based on orig name
	const nameBits = filename.split( '.' );
	const extension = nameBits.pop();
	nameBits.push( Date.now() );
	nameBits.push( extension );
	const randomName = nameBits.join( '.' );
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.prototype.setFilename.apply( this, [ randomName ] );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.prototype.uploadFile = function () {
	const dfd = $.Deferred();
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.prototype.uploadFile.call( this ).done( () => {
		this.saveFile().then( () => {
			dfd.resolve();
		}, ( error ) => {
			dfd.reject( error );
		} );
	} ).fail( () => {
		dfd.reject();
	} );
	return dfd;
};
