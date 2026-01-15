bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload = function ( target, apiconfig ) {
	bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.parent.call( this, target, apiconfig );
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload, mw.ForeignStructuredUpload );

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.prototype.finishStashUpload = function ( data ) {
	data = data || {};
	const ignorewarnings = data.ignorewarnings || false;

	if ( !this.stashPromise ) {
		return $.Deferred().reject( 'This upload has not been stashed, please upload it to the stash first.' );
	}

	return this.stashPromise.then( ( finishStash ) => {
		this.setState( mw.Upload.State.UPLOADING );

		return finishStash( {
			watchlist: ( this.getWatchlist() ) ? 1 : undefined,
			comment: this.getComment(),
			filename: this.getFilename(),
			text: this.getText(),
			ignorewarnings: ignorewarnings
		} ).then( ( result ) => this.returnUpload( result ), ( errorCode, result ) => {
			if ( result && result.upload && result.upload.warnings ) {
				if ( ignorewarnings ) {
					return this.returnUpload( result );
				}
				this.setState( mw.Upload.State.WARNING, result );
			} else {
				this.setState( mw.Upload.State.ERROR, result );
			}
			return $.Deferred().reject( errorCode, result );
		} );
	} );
};

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.prototype.returnUpload = function ( result ) {
	this.setState( mw.Upload.State.UPLOADED );

	this.imageinfo = result.upload.imageinfo;
	return result;
};

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.prototype.uploadToStash = function ( ignorewarnings ) {
	ignorewarnings = ignorewarnings || false;

	if ( !this.getFile() ) {
		return $.Deferred().reject( 'No file to upload. Call setFile to add one.' );
	}

	if ( !this.getFilename() ) {
		this.setFilenameFromFile();
	}

	this.setState( mw.Upload.State.UPLOADING );

	this.stashPromise = this.api.chunkedUploadToStash( this.getFile(), {
		filename: this.getFilename(),
		ignorewarnings: ignorewarnings
	} ).then( ( finishStash ) => {
		this.setState( mw.Upload.State.STASHED );
		return finishStash;
	}, ( errorCode, result ) => {
		if ( result && result.upload && result.upload.warnings ) {
			this.setState( mw.Upload.State.WARNING, result );
		} else {
			this.setState( mw.Upload.State.ERROR, result );
		}
		return $.Deferred().reject( errorCode, result );
	} );

	return this.stashPromise;
};

Object.assign( mw.Api.prototype, {
	chunkedUploadToStash: function ( file, data, chunkSize, chunkRetries ) {
		if ( !data.filename ) {
			throw new Error( 'Filename not included in file data.' );
		}

		const promise = this.chunkedUpload(
			file,
			Object.assign( { stash: true }, data ),
			chunkSize,
			chunkRetries
		);

		return this.finishUploadToStash( promise, data );
	}
} );
