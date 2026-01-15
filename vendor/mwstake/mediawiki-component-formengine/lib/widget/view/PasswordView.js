( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.PasswordView = function( cfg ) {
		mw.ext.forms.widget.view.PasswordView.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.view.PasswordView, OO.ui.LabelWidget );

	mw.ext.forms.widget.view.PasswordView.prototype.setValue = function( value ) {
		this.setLabel( '**********' );
	};

} )( mediaWiki, jQuery );
