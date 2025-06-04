ext.notifyme.ui.panel.SubscriptionManager = function ( cfg ) {
	cfg.expanded = false;
	ext.notifyme.ui.panel.SubscriptionManager.parent.call( this, cfg );
	this.$element.addClass( 'ext-notifyme-subscription-manager' );

	this.channelConfigurationRegistry = cfg.channelConfigurationRegistry || {};

	this.configurationLayout = new OO.ui.FieldsetLayout( {
		label: mw.message( 'notifyme-ui-subscription-manager-configuration' ).text()
	} );

	this.manualSubscriptionPanel = new ext.notifyme.ui.panel.IndividualSubscriptions( {
		buckets: cfg.buckets,
		events: cfg.events,
		data: cfg.data.subscriptions || [],
		channelLabels: cfg.channelLabels
	} );
	this.manualSubscriptionPanel.connect( this, {
		change: 'emitChange'
	} );
	this.addChannelConfigurationPanels( cfg.data );

	this.configurationLayout.addItems( [ this.channelConfigPanel ] );

	this.$element.append(
		this.manualSubscriptionPanel.$element,
		new OO.ui.PanelLayout( {
			expanded: false,
			padded: true,
			content: [ this.configurationLayout ]
		} ).$element
	);
	this.initialized = true;
};

OO.inheritClass( ext.notifyme.ui.panel.SubscriptionManager, OO.ui.PanelLayout );

ext.notifyme.ui.panel.SubscriptionManager.prototype.addChannelConfigurationPanels = function ( configuration ) {
	this.channelConfigPanel = new OO.ui.PanelLayout( {
		expanded: false,
		classes: [ 'ext-notifyme-config-panel' ]
	} );

	this.availableChannelConfigLayouts = {};
	for ( const key in this.channelConfigurationRegistry.registry ) {
		const provider = this.channelConfigurationRegistry.lookup( key );
		if ( !( provider instanceof ext.notifyme.ui.ChannelPreferences ) ) {
			continue;
		}
		const layout = provider.getLayout();
		if ( configuration.channels.hasOwnProperty( key ) ) {
			provider.setValue( configuration.channels[ key ] );
		}
		provider.connect( this, {
			change: function () {
				this.emitChange();
			}
		} );
		const fieldset = new OO.ui.FieldsetLayout( {} );
		fieldset.addItems( [ layout ] );
		this.availableChannelConfigLayouts[ key ] = fieldset;
		this.channelConfigPanel.$element.append( fieldset.$element );
	}
};

ext.notifyme.ui.panel.SubscriptionManager.prototype.emitChange = function () {
	if ( this.initialized ) {
		this.emit( 'change', this.getValue() );
	}
};

ext.notifyme.ui.panel.SubscriptionManager.prototype.getValue = function () {
	const channelPreferences = {};
	for ( const key in this.channelConfigurationRegistry.registry ) {
		const provider = this.channelConfigurationRegistry.lookup( key );
		if ( !( provider instanceof ext.notifyme.ui.ChannelPreferences ) ) {
			continue;
		}
		channelPreferences[ key ] = provider.getValue();
	}

	return {
		channels: channelPreferences,
		subscriptions: this.manualSubscriptionPanel.getValue()
	};
};
