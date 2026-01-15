ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.widget = ext.enhancedUI.widget || {};

ext.enhancedUI.widget.NamespaceOptionWidget = function ( cfg ) {
	cfg = cfg || {};
	cfg.classes = [ 'enhanced-namespace-option-widget' ];
	ext.enhancedUI.widget.NamespaceOptionWidget.super.call( this, cfg );

	this.$badge = $( '<span>' ).text( cfg.count || 0 );
	this.$badge.attr( 'aria-label', mw.message(
		'enhanced-standard-uis-allpages-namespace-badge-aria-label', cfg.count ).text() );
	this.$badge.addClass( 'enhanced-namespace-option-badge' );
	this.$element.append( this.$badge );

};

OO.inheritClass( ext.enhancedUI.widget.NamespaceOptionWidget, OO.ui.MenuOptionWidget );
