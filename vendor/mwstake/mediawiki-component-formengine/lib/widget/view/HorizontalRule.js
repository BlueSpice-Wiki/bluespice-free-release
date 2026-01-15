( function ( mw, $ ) {
	mw.ext.forms.widget.view.HorizontalRule = function ( cfg ) {
		mw.ext.forms.widget.view.HorizontalRule.parent.call( this, cfg );
		this.$element.html( $( '<hr>' ) );
	};

	OO.inheritClass( mw.ext.forms.widget.view.HorizontalRule, OO.ui.Widget );

}( mediaWiki, jQuery ) );
