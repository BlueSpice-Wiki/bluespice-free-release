$( function ( mw ) {
	const $form = $( '#mw-prefs-form' );
	const $container = $form.find( '.notifications-subscriptions' );
	const $input = $form.find( 'input.ext-notifyme-subscriptions-hidden' );

	if ( $input.length > 0 ) {
		const manager = new ext.notifyme.ui.panel.SubscriptionManager( {
			data: JSON.parse( $input.val() ),
			channelConfigurationRegistry: ext.notifyme.channelConfigurationRegistry,
			buckets: $container.data( 'buckets' ),
			events: $container.data( 'events' ),
			channelLabels: $container.data( 'channel-labels' )
		} );
		manager.connect( this, {
			change: function ( value ) {
				$input.val( JSON.stringify( value ) );
				// Trigger change event so that the form is marked as dirty
				const event = new Event( 'change' );
				document.dispatchEvent( event );
			}
		} );
		$container.find( '.oo-ui-progressBarWidget' ).remove();
		$container.append( manager.$element );
	}

	mw.hook( 'enhanced-standard-preferences-setup-sections' ).add( ( context, prefs ) => {
		const pref = prefs.notifications.subs[ 0 ];
		if ( pref.type !== 'notifications-subscriptions' ) {
			return;
		}
		pref.type = 'custom';
		pref.content = {
			classname: 'ext.notifyme.ui.panel.SubscriptionManager',
			pref: 'notifications-subscriptions',
			cfg: {
				data: JSON.parse( pref.default ),
				channelConfigurationRegistry: ext.notifyme.channelConfigurationRegistry,
				buckets: pref.value.bucketData,
				events: pref.value.eventData,
				channelLabels: pref.value.channelLabels
			}
		};
	} );
}( mediaWiki ) );
