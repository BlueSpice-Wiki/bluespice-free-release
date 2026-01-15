( function ( mw, $, d ) {
	mw.ext.forms.mixin.Message = function() {
		this.addMessageContainer();
	};

	OO.initClass( mw.ext.forms.mixin.Message );

	mw.ext.forms.mixin.Message.prototype.addMessageContainer = function() {
		this.$messageContainer = $( '<div>' ).addClass( 'mw-ext-forms-message-cnt' );
		this.$element.prepend( this.$messageContainer );
	};

	mw.ext.forms.mixin.Message.prototype.errorMessage = function( message ) {
		if ( this.errorReporting === true ) {
			this.showMessage( 'error', message );
		}
	};

	mw.ext.forms.mixin.Message.prototype.successMessage = function( message ) {
		this.showMessage( 'success', message );
	};

	mw.ext.forms.mixin.Message.prototype.infoMessage = function( message ) {
		this.showMessage( 'info', message );
	};

	mw.ext.forms.mixin.Message.prototype.showMessage = function( type, message ) {
		this.clearMessage();
		var msg = new OO.ui.MessageWidget( {
			type: type,
			label: message
		} );
		this.$messageContainer.html( msg.$element );
	};

	mw.ext.forms.mixin.Message.prototype.clearMessage = function() {
		this.$messageContainer.empty();
	}

} )( mediaWiki, jQuery, document );
