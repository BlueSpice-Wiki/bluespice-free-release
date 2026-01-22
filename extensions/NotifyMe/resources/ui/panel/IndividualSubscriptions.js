/* global SparkMD5 */

ext.notifyme.ui.panel.IndividualSubscriptions = function ( cfg ) {
	cfg = cfg || {};
	cfg.expanded = false;
	cfg.padded = true;
	ext.notifyme.ui.panel.IndividualSubscriptions.parent.call( this, cfg );
	this.setRegistry = ext.notifyme.subscriptionSetRegistry;
	this.buckets = cfg.buckets || [];
	this.events = cfg.events || [];
	this.channelLabels = cfg.channelLabels || {};

	this.emptyMessage = null;
	this.sets = {};

	this.$element.addClass( 'ext-notifyme-individual-subscriptions' );
	this.makeLayout();

	this.connect( this, {
		change: 'controlEmptySetMessage'
	} );

	this.setValue( cfg.data || [] );
};

OO.inheritClass( ext.notifyme.ui.panel.IndividualSubscriptions, OO.ui.PanelLayout );

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.makeLayout = function () {
	this.addButton = new OO.ui.ButtonWidget( {
		label: mw.message( 'notifyme-ui-subscription-set-add' ).text(),
		icon: 'add',
		flags: [ 'progressive' ],
		classes: [ 'subscription-set-add-button' ]
	} );
	this.addButton.connect( this, { click: 'onAddClick' } );

	this.setPanel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );
	const mainLayout = new OO.ui.FieldsetLayout( {
		label: mw.message( 'notifyme-ui-subscription-set-label' ).text(),
		items: [
			this.addButton,
			this.setPanel
		]
	} );

	this.$element.append( mainLayout.$element );
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.onAddClick = function () {
	this.openDialog();
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.openDialog = function ( id, data ) {
	data = data || {};
	const windowManager = new OO.ui.WindowManager();
	$( 'body' ).append( windowManager.$element );
	const window = new ext.notifyme.ui.dialog.SubscriptionSetEditor( {
		id: id,
		data: data,
		buckets: this.buckets,
		events: this.events,
		channelLabels: this.channelLabels
	} );
	windowManager.addWindows( [ window ] );
	windowManager.openWindow( window ).closed.then( ( data ) => { // eslint-disable-line no-shadow
		if ( data && data.action === 'create' ) {
			if ( data.value.id ) {
				this.sets[ data.value.id ].setValue( data.value );
				this.emit( 'change', this.getValue() );
			} else {
				this.addSet( data.value );
			}
		}
	} );
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.addSet = function ( data ) {
	const set = this.setRegistry.lookup( data.setType ),
		hash = this.generateId( data );

	if ( !set ) {
		console.error( 'Invalid set type provided', data.setType ); // eslint-disable-line no-console
		return;
	}
	if ( this.sets.hasOwnProperty( hash ) ) {
		OO.ui.alert( mw.message( 'notifyme-ui-subscription-set-duplicate' ).text() );
		return;
	}
	const setInstance = new set( hash ); // eslint-disable-line new-cap
	setInstance.setChannelLabels( this.channelLabels );
	setInstance.setBucketDisplayLabels( this.buckets );
	setInstance.setValue( data );
	setInstance.connect( this, { edit: 'onEditClick', delete: 'onDeleteClick' } );

	this.sets[ hash ] = setInstance;
	this.setPanel.$element.append( setInstance.$element );
	this.emit( 'change', this.getValue() );
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.generateId = function ( data ) {
	// Hash data object
	return SparkMD5.hash( JSON.stringify( data ) );
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.onEditClick = function ( id ) {
	this.openDialog( id, this.sets[ id ].getValue() );
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.onDeleteClick = function ( id ) {
	this.sets[ id ].$element.remove();
	delete this.sets[ id ];
	this.emit( 'change', this.getValue() );
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.getValue = function () {
	const value = [];
	for ( const hash in this.sets ) {
		if ( this.sets.hasOwnProperty( hash ) ) {
			const setValue = Object.assign( {
				setType: this.sets[ hash ].getKey()
			}, this.sets[ hash ].getValue() );
			value.push( setValue );
		}
	}
	return value;
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.setValue = function ( value ) {
	if ( !value || value.length === 0 ) {
		this.controlEmptySetMessage( value );
		return;
	}
	for ( let i = 0; i < value.length; i++ ) {
		this.addSet( value[ i ] );
	}
};

ext.notifyme.ui.panel.IndividualSubscriptions.prototype.controlEmptySetMessage = function ( value ) {
	if ( !value || value.length === 0 ) {
		if ( !this.emptyMessage ) {
			this.emptyMessage = new OO.ui.MessageWidget( {
				type: 'notice',
				label: mw.message( 'notifyme-ui-subscription-set-empty' ).text()
			} );
			this.setPanel.$element.prepend( this.emptyMessage.$element );
		}
	} else if ( this.emptyMessage ) {
		this.emptyMessage.$element.remove();
		delete this.emptyMessage;
	}
};
