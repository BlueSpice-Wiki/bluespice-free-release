( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.OptionView = function( cfg ) {
		mw.ext.forms.widget.view.OptionView.parent.call( this, cfg );
		this.options = cfg.options || [];
	};

	OO.inheritClass( mw.ext.forms.widget.view.OptionView, OO.ui.LabelWidget );

	mw.ext.forms.widget.view.OptionView.prototype.setValue = function( value ) {
		for( var i = 0; i < this.options.length; i++ ) {
			if ( this.options[i].data === value ) {
				return this.setLabel( this.options[i].label );
			}
		}
		this.setLabel( value );
	};

} )( mediaWiki, jQuery );
