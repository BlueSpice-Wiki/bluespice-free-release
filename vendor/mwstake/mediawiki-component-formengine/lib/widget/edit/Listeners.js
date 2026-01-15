( function ( mw ) {
	mw.ext.forms.widget.edit.Listeners = function ( cfg ) {

		cfg.base = [
			{
				type: 'text',
				required: true,
				name: 'event',
				labelAlign: 'top',
				label: 'Event'
			},
			{
				type: 'js_input',
				required: true,
				name: 'callback',
				label: 'Callback',
				labelAlign: 'top'
			},
			{
				type: 'hr',
				noLayout: true
			}
		];

		cfg.returnType = 'object';
		cfg.returnKey = 'event';

		this.events = cfg.events || [];
		if ( this.events.length ) {
			const options = [];
			for ( let i = 0; i < this.events.length; i++ ) {
				options.push( {
					label: this.events[ i ],
					data: this.events[ i ]
				} );
			}
			cfg.base[ 0 ].type = 'dropdown';
			cfg.base[ 0 ].options = options;

		}
		cfg.addInitially = false;
		mw.ext.forms.widget.edit.Listeners.parent.call( this, cfg );

	};

	OO.inheritClass( mw.ext.forms.widget.edit.Listeners, mw.ext.forms.widget.edit.Multiplier );

	mw.ext.forms.widget.edit.Listeners.prototype.setValue = function ( value ) {
		if ( !value ) {
			return;
		}

		for ( const name in value ) {
			const addedItems = this.addItem();
			addedItems.event.setValue( name );
			addedItems.callback.setValue( value[ name ] );
		}
	};

	mw.ext.forms.widget.edit.Listeners.prototype.getValue = function () {
		const listeners = {};
		for ( const name in this.items ) {
			listeners[ this.items[ name ].event.getValue() ] = this.items[ name ].callback.getValue();
		}

		return listeners;
	};

}( mediaWiki ) );
