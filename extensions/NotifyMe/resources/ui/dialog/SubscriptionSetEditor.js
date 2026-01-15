ext.notifyme.ui.dialog.SubscriptionSetEditor = function ( cfg ) {
	cfg = cfg || {};
	cfg.size = 'large';
	ext.notifyme.ui.dialog.SubscriptionSetEditor.super.call( this, cfg );
	this.id = cfg.id || null;
	this.data = cfg.data || {};
	this.setRegistry = ext.notifyme.subscriptionSetRegistry;
	this.buckets = cfg.buckets;
	this.events = cfg.events;
	this.channelLabels = cfg.channelLabels;
};

OO.inheritClass( ext.notifyme.ui.dialog.SubscriptionSetEditor, OO.ui.ProcessDialog );

ext.notifyme.ui.dialog.SubscriptionSetEditor.static.name = 'subscriptionSetEditor';
ext.notifyme.ui.dialog.SubscriptionSetEditor.static.title = mw.message( 'notifyme-ui-dialog-set-editor-title' ).text();
ext.notifyme.ui.dialog.SubscriptionSetEditor.static.actions = [
	{ action: 'create', label: mw.message( 'notifyme-ui-dialog-set-editor-create' ).text(), flags: [ 'primary', 'progressive' ] },
	{ label: mw.message( 'notifyme-ui-dialog-set-editor-cancel' ).text(), flags: [ 'safe', 'close' ] }
];

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.initialize = function () {
	ext.notifyme.ui.dialog.SubscriptionSetEditor.super.prototype.initialize.apply( this, arguments );

	this.panel = new OO.ui.PanelLayout( { padded: true, expanded: false } );

	this.setConfigPanel = new OO.ui.PanelLayout( { padded: false, expanded: false } );

	const items = [];
	for ( const key in this.setRegistry.registry ) {
		const set = this.setRegistry.lookup( key );
		if ( !set ) {
			continue;
		}
		const setInstance = new set(); // eslint-disable-line new-cap
		items.push( new OO.ui.ButtonOptionWidget( {
			data: setInstance.getKey(),
			label: setInstance.getLabel()
		} ) );
	}

	this.setTypePicker = new OO.ui.ButtonSelectWidget( {
		$overlay: true,
		items: items
	} );
	this.setTypePicker.connect( this, { select: 'onTypeSelect' } );

	const bucketItems = [];

	for ( const bucketKey in this.buckets ) {
		if ( !this.buckets.hasOwnProperty( bucketKey ) ) {
			continue;
		}
		if ( this.buckets[ bucketKey ].mandatory ) {
			continue;
		}
		const $label = new OO.ui.HorizontalLayout( {
			classes: [ 'ext-notifyme-ui-dialog-set-editor-bucket-label' ],
			items: [
				new OO.ui.LabelWidget( {
					label: this.buckets[ bucketKey ].label
				} ),
				new OO.ui.PopupButtonWidget( {
					icon: 'info',
					framed: false,
					invisibleLabel: true,
					$overlay: this.$overlay,
					popup: {
						head: false,
						$content: this.makePopupContent( bucketKey ),
						padded: true,
						hideCloseButton: true,
						autoFlip: true
					}
				} )
			]
		} ).$element;
		bucketItems.push( new OO.ui.RadioOptionWidget( {
			data: bucketKey,
			label: $label
		} ) );
	}
	this.bucketSelector = new OO.ui.RadioSelectWidget( {
		classes: [ 'ext-notifyme-ui-dialog-set-editor-bucket-picker' ],
		required: true,
		items: bucketItems
	} );

	const channelItems = [];
	for ( const channelKey in this.channelLabels ) {
		if ( !this.channelLabels.hasOwnProperty( channelKey ) ) {
			continue;
		}
		const config = {
			data: channelKey,
			label: mw.message( 'notifyme-ui-dialog-set-editor-channels-prefixed', this.channelLabels[ channelKey ] ).text()
		};
		if ( channelKey === 'web' ) {
			continue;
		}
		channelItems.push( new OO.ui.CheckboxMultioptionWidget( config ) );
	}
	this.channelSelector = new OO.ui.CheckboxMultiselectWidget( {
		items: channelItems
	} );

	this.panel.$element.append( new OO.ui.FieldLayout( this.setTypePicker, {
		align: 'top',
		label: mw.message( 'notifyme-ui-dialog-set-editor-type' ).text()
	} ).$element );
	this.panel.$element.append( new OO.ui.FieldsetLayout( { items: [ this.setConfigPanel ] } ).$element );
	this.panel.$element.append( new OO.ui.FieldLayout( this.bucketSelector, {
		align: 'top',
		label: mw.message( 'notifyme-ui-dialog-set-editor-bucket' ).text()
	} ).$element );
	this.panel.$element.append( new OO.ui.FieldLayout( this.channelSelector, {
		align: 'top',
		label: mw.message( 'notifyme-ui-dialog-set-editor-channels-hint' ).text()
	} ).$element );
	this.$body.append( this.panel.$element );
	if ( !$.isEmptyObject( this.data ) ) {
		this.setValue( this.data );
		// Cannot change type of existing set, as set are individual classes
		this.setTypePicker.setDisabled( true );
	} else {
		this.setTypePicker.selectItem( this.setTypePicker.findFirstSelectableItem() );
		this.bucketSelector.selectItem( this.bucketSelector.findFirstSelectableItem() );
	}
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.makePopupContent = function ( bucketKey ) {
	const layout = new OO.ui.PanelLayout( {
		classes: [ 'ext-notifyme-ui-dialog-event-desc-panel' ],
		padded: true,
		expanded: false
	} );

	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'notifyme-ui-dialog-popup-header-label' ).text()
	} );
	layout.$element.append( label.$element );

	const $html = $( '<table>' ).addClass( 'wikitable' );
	const $tableHeader = $( '<tr>' );
	$tableHeader.append( $( '<th>' ).text( mw.message( 'notifyme-ui-dialog-popup-table-event-label' ).text() ) );
	$tableHeader.append( $( '<th>' ).text( mw.message( 'notifyme-ui-dialog-popup-table-desc-label' ).text() ) );
	$html.append( $tableHeader );

	for ( const key in this.events[ bucketKey ] ) {
		const $tr = $( '<tr>' );
		$tr.append( $( '<td>' ).text( key ) );
		$tr.append( $( '<td>' ).html( this.events[ bucketKey ][ key ] ) );
		$html.append( $tr );
	}

	layout.$element.append( $html );
	return layout.$element;
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.setValue = function ( data ) {
	this.setTypePicker.selectItemByData( data.setType );
	this.bucketSelector.selectItemByData( data.bucket );
	if ( this.setEditor ) {
		this.setEditor.setValue( data.set );
	}
	this.channelSelector.selectItemsByData( data.channels );
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.onTypeSelect = function ( item ) {
	this.setConfigPanel.$element.children().remove();
	const set = this.setRegistry.lookup( item.getData() );
	if ( !set ) {
		return;
	}
	const setInstance = new set(); // eslint-disable-line new-cap
	this.data.setType = item.getData();
	this.setEditor = setInstance.getEditor( this );
	if ( !this.setEditor ) {
		setTimeout( this.updateSize.bind( this ), 0 );
		return;
	}
	this.setConfigPanel.$element.html( this.setEditor.getLayout().$element );
	setTimeout( this.updateSize.bind( this ), 0 );
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.getBodyHeight = function () {
	return this.$body.outerHeight( true );
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.getValidity = function () {
	if ( this.setEditor ) {
		return this.setEditor.getValidity();
	}
	return $.Deferred().resolve().promise();
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.setValidityFlag = function ( valid ) {
	if ( this.setEditor ) {
		return this.setEditor.setValidityFlag( valid );
	}
};

ext.notifyme.ui.dialog.SubscriptionSetEditor.prototype.getActionProcess = function ( action ) {
	return ext.notifyme.ui.dialog.SubscriptionSetEditor.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'create' ) {
				this.pushPending();
				this.getValidity().done( () => {
					this.popPending();
					this.close( { action: action, value: Object.assign( this.data, {
						set: this.setEditor ? this.setEditor.getValue() : {},
						bucket: this.bucketSelector.findSelectedItem().getData(),
						channels: [ 'web' ].concat( this.channelSelector.findSelectedItemsData() )
					}, { id: this.id } ) } );
				} ).fail( () => {
					this.setValidityFlag( false );
					this.popPending();
				} );
			}
			if ( action === 'cancel' ) {
				this.close( { action: action } );
			}
		}, this
	);
};
