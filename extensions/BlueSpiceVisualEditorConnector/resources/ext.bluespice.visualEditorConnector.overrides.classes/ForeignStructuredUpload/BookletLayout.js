bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.BookletLayout = function BsVecUiForeignStructuredUploadBookletLayout( config ) {
	bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.call( this, config );

	this.saveCalledInit = true;
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.BookletLayout, mw.ForeignStructuredUpload.BookletLayout );

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.initialize = function () {
	const categories = mw.config.get( 'wgCategories', [] );
	const insertCategoryEnabled = mw.config.get( 'bsgInsertCategoryUploadPanelIntegration', false );

	return bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.prototype.initialize.call( this ).then( () => {
		if ( categories.length && insertCategoryEnabled && this.categoriesWidget ) {
			this.categoriesWidget.setValue( categories );
		}
	} );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.getFilename = function () {
	const filename = bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.prototype.getFilename.call( this );

	const data = {
		filename: filename
	};
	this.emit( 'getfilename', this, data );

	return data.filename;
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.createUpload = function () {
	return new bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload( this.target, {
		parameters: {
			errorformat: 'html',
			errorlang: mw.config.get( 'wgUserLanguage' ),
			errorsuselocal: 1,
			formatversion: 2
		}
	} );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.renderUploadForm = function () {
	const form = bs.vec.ui.ForeignStructuredUpload.BookletLayout.super.prototype.renderUploadForm.apply( this );
	const items = form.getItems();
	const fieldlayouts = items[ 0 ].getItems();
	// Hide "upload-form-label-not-own-work-message-generic-local" text
	fieldlayouts[ 2 ].$element.hide();
	return form;
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.saveFile = function () {
	if ( this.saveCalledInit === false ) {
		this.uploadPromise = this.upload.uploadToStash( true );
	}
	const deferred = $.Deferred();

	this.upload.setFilename( this.getFilename() );
	this.upload.setText( this.getText() );

	this.uploadPromise.then( () => {
		this.upload.finishStashUpload( {
			ignorewarnings: this.saveCalledInit === false
		} ).then( () => {
			// Normalize page name and localise the 'File:' prefix
			const name = new mw.Title( 'File:' + this.upload.getFilename() ).toString();
			this.filenameUsageWidget.setValue( '[[' + name + ']]' );
			this.setPage( 'insert' );

			deferred.resolve();
			this.emit( 'fileSaved', this.upload.getImageInfo() );
		} ).catch( () => {
			this.getErrorMessageForStateDetails().then( ( errorMessage ) => {
				deferred.reject( errorMessage );
			} );
		} );
		this.saveCalledInit = false;
	} );

	return deferred.promise();
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.getErrorMessageForStateDetails = function () {
	const state = this.upload.getState(),
		stateDetails = this.upload.getStateDetails(),
		warnings = stateDetails.upload && stateDetails.upload.warnings;

	if ( state === mw.Upload.State.ERROR ) {
		const $error = ( new mw.Api() ).getErrorMessage( stateDetails );
		if ( stateDetails.exception === 'Request Entity Too Large' ) {
			if ( $error[ 0 ] ) {
				$error[ 0 ].textContent = mw.msg( 'bs-visualeditor-request-entity-too-large' );
			}
		}

		return $.Deferred().resolve( new OO.ui.Error(
			$error,
			{ recoverable: false }
		) );
	}

	if ( state === mw.Upload.State.WARNING ) {
		if ( warnings.exists !== undefined ) {
			return $.Deferred().resolve( new OO.ui.Error(
				$( '<p>' ).msg( 'fileexists', 'File:' + warnings.exists ),
				{ recoverable: true, warning: true }
			) );
		} else if ( warnings[ 'duplicate-archive' ] !== undefined ) {
			return $.Deferred().resolve( new OO.ui.Error(
				$( '<p>' ).msg( 'file-deleted-duplicate', 'File:' + warnings[ 'duplicate-archive' ] ),
				{ recoverable: true, warning: true }
			) );
		} else if ( warnings[ 'was-deleted' ] !== undefined ) {
			return $.Deferred().resolve( new OO.ui.Error(
				$( '<p>' ).msg( 'filewasdeleted', 'File:' + warnings[ 'was-deleted' ] ),
				{ recoverable: true, warning: true }
			) );
		}
	}
	return bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.prototype.getErrorMessageForStateDetails.call( this );
};
