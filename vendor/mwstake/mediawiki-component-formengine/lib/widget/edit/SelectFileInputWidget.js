( function ( mw, $ ) {
	mw.ext.forms.widget.edit.SelectFileInput = function ( cfg ) {
		this.fileWidget = new OO.ui.SelectFileWidget( Object.assign( {
			accept: [
				'image/png',
				'image/jpeg'
			],
			$tabIndexed: $( '<a class="oo-ui-buttonElement-button" role="button" tabindex="0" aria-disabled="false" rel="nofollow"><span class="oo-ui-iconElement-icon oo-ui-icon-upload"></span><span class="oo-ui-labelElement-label">Select a file</span><span class="oo-ui-indicatorElement-indicator oo-ui-indicatorElement-noIndicator"></span><input title="" class="oo-ui-inputWidget-input" type="file" tabindex="-1" accept="image/png, image/jpeg"></a>' ), // eslint-disable-line no-jquery/no-parse-html-literal
			showDropTarget: true
		}, cfg ) );
		mw.ext.forms.widget.edit.SelectFileInput.parent.call( this, cfg );

		this.$input.remove();
		this.$element.append( this.fileWidget.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.SelectFileInput, OO.ui.InputWidget );

	mw.ext.forms.widget.edit.SelectFileInput.prototype.setValue = function ( value ) {
		if ( !value ) {
			return this.fileWidget.setValue( null );
		}
		this.toFile( value ).then( ( file ) => {
			this.fileWidget.setValue( file );
		} );
	};

	mw.ext.forms.widget.edit.SelectFileInput.prototype.getValue = function ( raw ) {
		const dfd = $.Deferred();
		const value = this.fileWidget.getValue();
		if ( !value || raw ) {
			dfd.resolve( value );
			return dfd.promise();
		}

		const file = value;
		const reader = new FileReader();
		reader.readAsDataURL( file );
		reader.onload = function () {
			dfd.resolve( {
				filename: file.name,
				type: file.type,
				base64: reader.result
			} );
		};
		return dfd.promise();
	};

	mw.ext.forms.widget.edit.SelectFileInput.prototype.toFile = function ( value ) {
		return ( fetch( value.base64 )
			.then( ( res ) => res.arrayBuffer() )
			.then( ( buf ) => new File( [ buf ], value.filename, { type: value.type } ) )
		);
	};

}( mediaWiki ) );
