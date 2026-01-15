bs.util.registerNamespace( 'bs.pagesvisited.util.tag' );

bs.pagesvisited.util.tag.PagesVisitedDefinition = function BsVecUtilPagesVisitedDefinition() {
	bs.pagesvisited.util.tag.PagesVisitedDefinition.super.call( this );
};

OO.inheritClass( bs.pagesvisited.util.tag.PagesVisitedDefinition, bs.vec.util.tag.Definition );

bs.pagesvisited.util.tag.PagesVisitedDefinition.prototype.getCfg = function () {
	const cfg = bs.pagesvisited.util.tag.PagesVisitedDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'Pagesvisited',
		name: 'pagesvisited',
		tagname: 'bs:pagesvisited',
		menuItemMsg: 'bs-pagesvisited-ve-pagesvisited-title',
		descriptionMsg: 'bs-pagesvisited-tag-pagesvisited-desc',
		attributes: [ {
			name: 'count',
			labelMsg: 'bs-pagesvisited-ve-pagesvisited-attr-count-label',
			helpMsg: 'bs-pagesvisited-ve-pagesvisited-attr-count-help',
			type: 'number',
			default: '7'
		}, {
			name: 'maxtitlelength',
			labelMsg: 'bs-pagesvisited-ve-pagesvisited-attr-maxtitlelength-label',
			helpMsg: 'bs-pagesvisited-ve-pagesvisited-attr-maxtitlelength-help',
			type: 'number',
			default: '40'
		}, {
			name: 'order',
			labelMsg: 'bs-pagesvisited-ve-pagesvisited-attr-order-label',
			helpMsg: 'bs-pagesvisited-ve-pagesvisited-attr-order-help',
			type: 'dropdown',
			default: 'time',
			options: [
				{ data: 'time', label: mw.message( 'bs-pagesvisited-tag-pagesvisited-attr-order-option-time' ).plain() },
				{ data: 'pagename', label: mw.message( 'bs-pagesvisited-tag-pagesvisited-attr-order-option-pagename' ).plain() }
			]
		}, {
			name: 'namespaces',
			labelMsg: 'bs-pagesvisited-ve-pagesvisited-attr-namespaces-label',
			helpMsg: 'bs-pagesvisited-ve-pagesvisited-attr-namespaces-help',
			type: 'text',
			default: ''
		} ]
	} );
};

bs.vec.registerTagDefinition(
	new bs.pagesvisited.util.tag.PagesVisitedDefinition()
);
