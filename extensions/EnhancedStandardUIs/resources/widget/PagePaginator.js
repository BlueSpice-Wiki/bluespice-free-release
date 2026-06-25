ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.widget = ext.enhancedUI.widget || {};

ext.enhancedUI.widget.PagePaginator = function ( cfg ) {
	ext.enhancedUI.widget.PagePaginator.parent.call( this, cfg );
	this.currentEntriesShown.$element.hide();
	this.totalWidget.$element.hide();
	this.currentStart = 0;
	this.currentEnd = 0;

	this.shownPagesLabel = new OO.ui.LabelWidget( {
		classes: [ 'shown-pages-label' ]
	} );
	this.navigation.$element.prepend( this.shownPagesLabel.$element );
};

OO.inheritClass( ext.enhancedUI.widget.PagePaginator, OOJSPlus.ui.data.grid.Paginator );

ext.enhancedUI.widget.PagePaginator.prototype.showRange = function ( start, end ) {
	ext.enhancedUI.widget.PagePaginator.parent.prototype.showRange.call( this, start, end );

	this.currentStart = start + 1;
	this.currentEnd = end + 1;
	this.updateShownPagesLabel();

};

ext.enhancedUI.widget.PagePaginator.prototype.updateTotal = function ( total, isApproximate ) {
	this.currentTotal = total;
	this.totalIsApproximate = isApproximate;
	this.updateShownPagesLabel();
};

ext.enhancedUI.widget.PagePaginator.prototype.updateShownPagesLabel = function () {
	if ( this.currentStart === this.currentEnd && this.currentStart === 0 ) {
		this.shownPagesLabel.$element.hide();
		return;
	}
	this.shownPagesLabel.$element.show();
	let msg = 'enhanced-standard-uis-allpages-panel-index-paginator-shown-pages';
	if ( this.totalIsApproximate ) {
		msg += '-approximate';
	}

	// * enhanced-standard-uis-allpages-panel-index-paginator-shown-pages
	// * enhanced-standard-uis-allpages-panel-index-paginator-shown-pages-approximate
	this.shownPagesLabel.setLabel( mw.msg(
		msg,
		this.currentStart,
		this.currentEnd,
		this.currentTotal
	) );
};
