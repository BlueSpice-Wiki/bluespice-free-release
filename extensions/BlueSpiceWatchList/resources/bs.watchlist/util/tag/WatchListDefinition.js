bs.util.registerNamespace( 'bs.watchlist.util.tag' );
bs.watchlist.util.tag.WatchListDefinition = function BsVecUtilTagWatchListDefinition() {
	bs.watchlist.util.tag.WatchListDefinition.super.call( this );
};

OO.inheritClass( bs.watchlist.util.tag.WatchListDefinition, bs.vec.util.tag.Definition );

bs.watchlist.util.tag.WatchListDefinition.prototype.getCfg = function () {
	const cfg = bs.watchlist.util.tag.WatchListDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'Watchlist',
		name: 'watchlist',
		tagname: 'bs:watchlist',
		descriptionMsg: 'bs-watchlist-tag-watchlist-desc',
		menuItemMsg: 'bs-watchlist-ve-watchlistinspector-title',
		attributes: [ {
			name: 'count',
			labelMsg: 'bs-watchlist-ve-watchlist-attr-count-label',
			helpMsg: 'bs-watchlist-tag-watchlist-attr-count-help',
			type: 'number',
			default: 5
		}, {
			name: 'maxtitlelength',
			labelMsg: 'bs-watchlist-ve-watchlist-attr-maxtitlelength-label',
			helpMsg: 'bs-watchlist-tag-watchlist-attr-maxtitlelength-help',
			type: 'number',
			default: 20
		}, {
			name: 'order',
			labelMsg: 'bs-watchlist-ve-watchlist-attr-order-label',
			helpMsg: 'bs-watchlist-tag-watchlist-attr-order-help',
			type: 'dropdown',
			default: 'time',
			options: [
				{
					data: 'pagename',
					label: mw.message( 'bs-watchlist-ve-watchlist-attr-pagename-label' ).text()
				}, {
					data: 'time',
					label: mw.message( 'bs-watchlist-ve-watchlist-attr-time-label' ).text()
				}
			]
		} ]
	} );
};

bs.vec.registerTagDefinition(
	new bs.watchlist.util.tag.WatchListDefinition()
);
