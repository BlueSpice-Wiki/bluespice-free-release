( function ( mw, $, undefined ) {

	/**
	 * This override is here to enable noDefaultValue option, which, if set,
	 * will initiate the widget with no preselected items
	 */
	mw.ext.forms.widget.edit.RadioSelectInputWidget = function( cfg ) {

		this.noDefaultValue = cfg.noDefaultValue || false;

		mw.ext.forms.widget.edit.RadioSelectInputWidget.super.call( this, cfg );

		if ( cfg.horizontal ) {
			this.$element.addClass( 'widget-multioption-horizontal' );
		}
	};

	OO.inheritClass( mw.ext.forms.widget.edit.RadioSelectInputWidget, OO.ui.RadioSelectInputWidget );

	mw.ext.forms.widget.edit.RadioSelectInputWidget.prototype.setValue = function( value ) {
		var selected;
		value = this.cleanUpValue( value );
		// Only allow setting values that are actually present in the dropdown
		selected = this.radioSelectWidget.findItemFromData( value );
		if ( selected || !this.noDefaultValue ) {
			mw.ext.forms.widget.edit.RadioSelectInputWidget.parent.prototype.setValue.call( this, value );
		}

		return this;
	};

} )( mediaWiki, jQuery );
