StandardDialogs.ui.BasePage = function BasePage( name, config ) {
	StandardDialogs.ui.BasePage.super.call( this, name, config );
	this.pageName = config.pageName;
	this.setup();
};

OO.inheritClass( StandardDialogs.ui.BasePage, OO.ui.PageLayout );

StandardDialogs.ui.BasePage.prototype.setupOutlineItem = function () {
	StandardDialogs.ui.BasePage.super.prototype.setupOutlineItem.apply( this, arguments );

	if ( this.outlineItem ) {
		this.outlineItem.setLabel( '' );
	}
};
