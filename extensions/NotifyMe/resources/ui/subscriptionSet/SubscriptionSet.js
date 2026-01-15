ext.notifyme.ui.SubscriptionSet = function ( id ) {
	this.id = id;
	this.bucketDisplayLabels = {};
	// Parent constructor
	ext.notifyme.ui.SubscriptionSet.parent.apply( this, arguments );
	this.value = null;
	this.$element.addClass( 'ext-notifyme-subscriptionSet' );
};

OO.inheritClass( ext.notifyme.ui.SubscriptionSet, OO.ui.Widget );

ext.notifyme.ui.SubscriptionSet.prototype.getLayout = function () {
	return new OO.ui.FieldsetLayout();
};

ext.notifyme.ui.SubscriptionSet.prototype.getLabel = function () {
	// STUB
	return '';
};

ext.notifyme.ui.SubscriptionSet.prototype.setBucketDisplayLabels = function ( labels ) {
	this.bucketDisplayLabels = labels;
};

ext.notifyme.ui.SubscriptionSet.prototype.getKey = function () {
	// STUB
	return '';
};

ext.notifyme.ui.SubscriptionSet.prototype.getValue = function () {
	return this.value;
};

ext.notifyme.ui.SubscriptionSet.prototype.setValue = function ( value ) {
	this.value = value;
	this.$element.empty();
	this.render();
};

ext.notifyme.ui.SubscriptionSet.prototype.getEditor = function ( dialog ) { // eslint-disable-line no-unused-vars
	return null;
};

/**
 * Final method, do not override
 */
ext.notifyme.ui.SubscriptionSet.prototype.render = function () {
	const tileLabel = new OO.ui.LabelWidget( {
		label: this.getLabel(),
		classes: [ 'tile-label' ]
	} );
	const tileValue = new OO.ui.LabelWidget( {
		label: this.getHeaderKeyValue(),
		title: this.getHeaderKeyValue(),
		classes: [ 'tile-value' ]
	} );
	this.bucketLabel = new OO.ui.LabelWidget( {
		label: this.getBucketLabel()
	} );

	const headerLayout = new OO.ui.HorizontalLayout( {
		items: [ tileLabel, tileValue, this.getButtons() ],
		classes: [ 'ext-notifyme-subscriptionSet-header' ]
	} );
	this.deliveryLabel = new OO.ui.LabelWidget( {
		label: this.makeDeliveryLabel()
	} );
	this.deliveryIconsLayout = new OO.ui.HorizontalLayout( {
		classes: [ 'ext-notifyme-subscriptionSet-delivery-icons' ],
		items: this.makeDeliveryIcons()
	} );
	const deliveryLayout = new OO.ui.HorizontalLayout( {
		classes: [ 'ext-notifyme-subscriptionSet-delivery' ],
		items: [ this.deliveryLabel, this.deliveryIconsLayout ]
	} );

	this.$element.append( headerLayout.$element, this.bucketLabel.$element, deliveryLayout.$element );
};

ext.notifyme.ui.SubscriptionSet.prototype.getHeaderKeyValue = function () {
	return '';
};

ext.notifyme.ui.SubscriptionSet.prototype.getBucketLabel = function () {
	if ( this.bucketDisplayLabels.hasOwnProperty( this.value.bucket ) ) {
		return this.bucketDisplayLabels[ this.value.bucket ].label;
	}
	return this.value.bucket;
};

ext.notifyme.ui.SubscriptionSet.prototype.getButtons = function () {
	const editButton = new OO.ui.ButtonWidget( {
		title: mw.msg( 'notifyme-ui-edit' ),
		icon: 'edit',
		framed: false
	} );
	const deleteButton = new OO.ui.ButtonWidget( {
		title: mw.msg( 'notifyme-ui-delete' ),
		icon: 'trash',
		framed: false
	} );

	editButton.connect( this, { click: function () {
		this.emit( 'edit', this.id );
	} } );
	deleteButton.connect( this, { click: function () {
		this.emit( 'delete', this.id );
	} } );

	return new OO.ui.HorizontalLayout( {
		items: [ editButton, deleteButton ],
		classes: [ 'ext-notifyme-subscriptionSet-buttons' ]
	} );
};

ext.notifyme.ui.SubscriptionSet.prototype.makeDeliveryLabel = function () {
	const channels = this.value.channels || [];

	if ( channels.includes( 'web' ) && channels.includes( 'email' ) ) {
		return mw.msg( 'notifyme-ui-delivery-web-email' );
	}
	if ( channels.includes( 'web' ) ) {
		return mw.msg( 'notifyme-ui-delivery-web' );
	}
	return '';
};

ext.notifyme.ui.SubscriptionSet.prototype.makeDeliveryIcons = function () {
	const channels = this.value.channels || [],
		icons = [];
	if ( channels.includes( 'web' ) ) {
		icons.push( new OO.ui.IconWidget( {
			icon: 'bell'
		} ) );
	}
	if ( channels.includes( 'email' ) ) {
		icons.push( new OO.ui.IconWidget( {
			icon: 'message'
		} ) );
	}
	return icons;
};
