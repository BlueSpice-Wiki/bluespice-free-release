ext.notifyme.ui.EmailChannelPreferences = function ( cfg ) {
	cfg = cfg || {};
	ext.notifyme.ui.EmailChannelPreferences.parent.call( this, cfg );

	this.$element.addClass( 'ext-notifyme-email-preference-provider' );
};

OO.inheritClass( ext.notifyme.ui.EmailChannelPreferences, ext.notifyme.ui.ChannelPreferences );

ext.notifyme.ui.EmailChannelPreferences.prototype.getLayout = function () {
	this.frequencyPicker = new OO.ui.DropdownWidget( {
		$overlay: true,
		menu: {
			items: [
				new OOJSPlus.ui.widget.MenuOptionWithDescription( {
					data: 'instant', label: mw.message( 'notifyme-ui-email-frequency-instant' ).text(),
					description: mw.message( 'notifyme-ui-email-frequency-instant-desc' ).text()
				} ),
				new OOJSPlus.ui.widget.MenuOptionWithDescription( {
					data: 'daily', label: mw.message( 'notifyme-ui-email-frequency-daily' ).text(),
					description: mw.message( 'notifyme-ui-email-frequency-daily-desc' ).text()
				} ),
				new OOJSPlus.ui.widget.MenuOptionWithDescription( {
					data: 'weekly', label: mw.message( 'notifyme-ui-email-frequency-weekly' ).text(),
					description: mw.message( 'notifyme-ui-email-frequency-weekly-desc' ).text()
				} )
			]
		}
	} );
	this.frequencyPicker.getMenu().connect( this, { select: 'onFrequencySelect' } );
	this.frequencyPicker.getMenu().selectItem( this.frequencyPicker.getMenu().findFirstSelectableItem() );

	this.frequencyPickerLayout = new OO.ui.FieldLayout( this.frequencyPicker, {
		align: 'left',
		label: mw.message( 'notifyme-ui-email-frequency-label' ).text()
	} );

	return this.frequencyPickerLayout;
};

ext.notifyme.ui.EmailChannelPreferences.prototype.setValue = function ( value ) {
	ext.notifyme.ui.EmailChannelPreferences.parent.prototype.setValue.call( this, value );
	this.frequencyPicker.getMenu().selectItemByData( value.frequency );
};

ext.notifyme.ui.EmailChannelPreferences.prototype.onFrequencySelect = function ( selected ) {
	this.value.frequency = selected.getData();
	this.emit( 'change', this.value );
};

ext.notifyme.channelConfigurationRegistry.register( 'email', new ext.notifyme.ui.EmailChannelPreferences() );
