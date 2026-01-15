window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui = window.enhancedUpload.ui || {};
window.enhancedUpload.ui.dataTransferHandler = window.enhancedUpload.ui.dataTransferHandler || {};

/*
* @param {ve.ui.Surface} surface
* @param {ve.ui.DataTransferItem} item
*/
enhancedUpload.ui.dataTransferHandler.MediaTransferHandler = function EnUpMediaTransferHandler() {
	enhancedUpload.ui.dataTransferHandler.MediaTransferHandler.super.apply( this, arguments );

	// Properties
	this.file = this.item.getAsFile();
};

OO.inheritClass( enhancedUpload.ui.dataTransferHandler.MediaTransferHandler,
	ve.ui.MWMediaTransferHandler );

enhancedUpload.ui.dataTransferHandler.MediaTransferHandler.static.name = 'mediaTransfer';

enhancedUpload.ui.dataTransferHandler.MediaTransferHandler.static.kinds = [ 'file' ];

const config = require( './config.json' );

enhancedUpload.ui.dataTransferHandler.MediaTransferHandler.static.types = config.allowedMimeTypes;

enhancedUpload.ui.dataTransferHandler.MediaTransferHandler.static.extensions =
	config.allowedFileExtensions;

/**
 * @inheritdoc
 */
enhancedUpload.ui.dataTransferHandler.MediaTransferHandler.prototype.process = function () {
	const file = this.item.getAsFile();

	const action = ve.ui.actionFactory.create( 'window', this.surface );
	action.open( 'mediaUpload', { file: file } );
};

ve.ui.dataTransferHandlerFactory.register(
	enhancedUpload.ui.dataTransferHandler.MediaTransferHandler
);
