bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple = function BsVecUiForeignStructuredUploadBookletLayoutSimple( config ) {
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple, bs.vec.ui.ForeignStructuredUpload.BookletLayout );

bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.prototype.renderUploadForm = function () {
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.super.prototype.renderUploadForm.apply( this );
	this.removeMyOwnWorkCheckbox();
	return this.uploadForm;
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.prototype.removeMyOwnWorkCheckbox = function () {
	const items = this.uploadForm.getItems();
	// items[0] === OO.ui.FieldsetLayout
	const fieldlayouts = items[ 0 ].getItems();
	// fieldlayouts = [
	// 0 = this.selectFileWidget
	// 1 = this.ownWorkCheckbox
	// 3 = this.messageLabel
	// ]
	fieldlayouts[ 1 ].$element.hide();
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.prototype.onUploadFormChange = function () {
	const file = this.selectFileWidget.getValue(),
		valid = !!file;
	this.emit( 'uploadValid', valid );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.prototype.renderInfoForm = function () {
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.super.prototype.renderInfoForm.apply( this );
	this.descriptionWidget.setRequired( false );
	return this.infoForm;
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple.prototype.onInfoFormChange = function () {
	$.when(
		this.filenameWidget.getValidity()
	).done( () => {
		this.emit( 'infoValid', true );
	} ).fail( () => {
		this.emit( 'infoValid', false );
	} );
};
