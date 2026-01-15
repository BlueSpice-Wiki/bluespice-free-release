( function ( mw ) {
	mw.ext.forms.widget.view.CategoryMultiselectView = function ( cfg ) {
		mw.ext.forms.widget.view.CategoryMultiselectView.parent.call( this, cfg );
		this.setDisabled( true );
	};

	OO.inheritClass( mw.ext.forms.widget.view.CategoryMultiselectView, mw.ext.forms.widget.edit.CategoryMultiselect );

}( mediaWiki, jQuery ) );
