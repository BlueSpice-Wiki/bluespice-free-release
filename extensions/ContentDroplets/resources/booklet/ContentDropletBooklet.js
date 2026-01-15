window.cd = window.cd || {};
window.cd.booklet = window.cd.booklet || {};

ext.contentdroplets.ui.ContentDropletBooklet = function ( name, cfg ) {
	ext.contentdroplets.ui.ContentDropletBooklet.parent.call( this, name, cfg );
};

OO.inheritClass( ext.contentdroplets.ui.ContentDropletBooklet, OO.ui.BookletLayout );
