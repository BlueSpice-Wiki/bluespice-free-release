( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.CheckboxMultiselectView = function( cfg ) {
		mw.ext.forms.widget.view.CheckboxMultiselectView.parent.call( this, cfg );
		this.setDisabled( true );

		if ( cfg.horizontal ) {
			this.$element.addClass( 'widget-multioption-horizontal' );
		}
	};

	OO.inheritClass( mw.ext.forms.widget.view.CheckboxMultiselectView, OO.ui.CheckboxMultiselectInputWidget );

} )( mediaWiki, jQuery );
