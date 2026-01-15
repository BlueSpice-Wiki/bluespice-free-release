( function ( mw ) {
	mw.ext.forms.widget.edit.CustomFormProps = function ( cfg ) {
		cfg.base = [ {
			type: 'text',
			required: true,
			name: 'key',
			label: 'Key'
		}, {
			type: 'text',
			required: true,
			label: 'Value',
			name: 'value'
		},
		{
			type: 'hr',
			noLayout: true
		}
		];

		cfg.addInitially = false;
		mw.ext.forms.widget.edit.CustomFormProps.parent.call( this, cfg );
		new OO.ui.LabelWidget( {
			label: mw.message( 'mwstake-formengine-label-custom-widget-properties' ).text()
		} ).$element.insertBefore( this.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.CustomFormProps, mw.ext.forms.widget.edit.Multiplier );

	mw.ext.forms.widget.edit.CustomFormProps.prototype.setValue = function ( value ) {
		if ( !value ) {
			return;
		}

		for ( const key in value ) {
			if ( !value.hasOwnProperty( key ) ) {
				continue;
			}
			const addedItems = this.addItem();
			const unprefixedKey = key.replace( /^widget_/gi, '' );
			addedItems.key.setValue( unprefixedKey );
			addedItems.value.setValue( value[ key ].toString() );
		}
	};

	mw.ext.forms.widget.edit.CustomFormProps.prototype.getValue = function () {
		const props = {};
		for ( const name in this.items ) {
			props[ 'widget_' + this.getKey( this.items[ name ] ) ] = this.getItemValue( this.items[ name ] );
		}

		return props;
	};

	mw.ext.forms.widget.edit.CustomFormProps.prototype.getKey = function ( item ) {
		return item.key.getValue().trim();
	};

	mw.ext.forms.widget.edit.CustomFormProps.prototype.getItemValue = function ( item ) {
		// TODO: have bigger plans for this
		return item.value.getValue().trim();
	};

}( mediaWiki ) );
