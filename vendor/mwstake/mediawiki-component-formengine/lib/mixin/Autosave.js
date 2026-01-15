( function ( mw, $, undefined ) {
	mw.ext.forms.mixin.Autosave = function( asConfig ) {
		this.interval = 60;
		this.silent = false;
		if ( typeof asConfig === 'string' ) {
			this.type = asConfig;
		} else if ( typeof asConfig === 'object' ) {
			this.type = asConfig.type;
			if ( asConfig.hasOwnProperty( 'interval' ) ) {
				this.interval = parseInt( asConfig.interval );
			}
			if ( asConfig.hasOwnProperty( 'silent' ) ) {
				this.silent = !!asConfig.silent;
			}
		}
		if ( mw.ext.forms.mixin.Autosave.static.types.indexOf ( this.type ) === -1 ) {
			this.type = mw.ext.forms.mixin.Autosave.static.types[0];
		}

		window.setInterval( function() {
			// Closure to disable calling this from elsewhere
			if ( this.type === 'target' ) {
				this.saveProgress( true ).done( function() {
					if ( !this.silent ) {
						this.successMessage( mw.message( 'mwstake-formengine-form-autosave-success' ).text(), true, 1000 );
					}
				}.bind( this ) );
			}
		}.bind( this ), this.interval * 1000 );
	};

	OO.initClass( mw.ext.forms.mixin.Autosave );

	mw.ext.forms.mixin.Autosave.static.types = [ 'target' ];

} )( mediaWiki, jQuery );