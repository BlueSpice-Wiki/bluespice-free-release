( function ( mw ) {
	mw.ext.forms.widget.edit.ProgressBar = function ( cfg ) {
		mw.ext.forms.widget.edit.ProgressBar.parent.call( this, cfg );

		if ( cfg.value ) {
			this.setValue( cfg.value );
		}
	};

	OO.inheritClass( mw.ext.forms.widget.edit.ProgressBar, OO.ui.ProgressBarWidget );

	mw.ext.forms.widget.edit.ProgressBar.prototype.getValue = function () {
		return this.getProgress();
	};

	mw.ext.forms.widget.edit.ProgressBar.prototype.setValue = function ( value ) {
		if ( value ) {
			this.setProgress( value );
		}
	};
}( mediaWiki ) );
