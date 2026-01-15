( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.MenuTagMultiselectView = function( cfg ) {
		mw.ext.forms.widget.view.MenuTagMultiselectView.parent.call( this, cfg );
		this.setDisabled( true );
	};

	OO.inheritClass( mw.ext.forms.widget.view.MenuTagMultiselectView, OO.ui.MenuTagMultiselectWidget );

} )( mediaWiki, jQuery );