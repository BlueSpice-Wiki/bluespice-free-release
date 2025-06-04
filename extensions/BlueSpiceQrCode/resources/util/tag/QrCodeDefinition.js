bs.util.registerNamespace( 'bs.qrcode.util.tag' );

bs.qrcode.util.tag.QrCodeDefinition = function BsVecUtilQrCodeDefinition() {
	bs.qrcode.util.tag.QrCodeDefinition.super.call( this );
};

OO.inheritClass( bs.qrcode.util.tag.QrCodeDefinition, bs.vec.util.tag.Definition );

bs.qrcode.util.tag.QrCodeDefinition.prototype.getCfg = function () {
	const cfg = bs.qrcode.util.tag.QrCodeDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'QrCode',
		name: 'qrCode',
		tagname: 'qrcode',
		icon: 'bluespice',
		descriptionMsg: 'bs-qr-code-tag-qrcode-desc',
		menuItemMsg: 'bs-qr-code-tag-qrcode-title',
		attributes: [ {
			name: 'page',
			labelMsg: 'bs-qr-code-tag-attr-page-label',
			helpMsg: 'bs-qr-code-tag-attr-page-help',
			type: 'title'
		}, {
			name: 'query',
			labelMsg: 'bs-qr-code-tag-attr-query-label',
			helpMsg: 'bs-qr-code-tag-attr-query-help',
			type: 'text'
		}, {
			name: 'desc',
			labelMsg: 'bs-qr-code-tag-attr-desc-label',
			helpMsg: 'bs-qr-code-tag-attr-desc-help',
			type: 'text'
		}, {
			name: 'size',
			labelMsg: 'bs-qr-code-tag-attr-size-label',
			helpMsg: 'bs-qr-code-tag-attr-size-help',
			type: 'dropdown',
			default: 'medium',
			options: [
				{ data: 80, label: mw.message( 'bs-qr-code-tag-attr-size-option-small' ).plain() },
				{ data: 120, label: mw.message( 'bs-qr-code-tag-attr-size-option-medium' ).plain() },
				{ data: 400, label: mw.message( 'bs-qr-code-tag-attr-size-option-large' ).plain() }
			]
		} ]
	} );
};

bs.vec.registerTagDefinition(
	new bs.qrcode.util.tag.QrCodeDefinition()
);
