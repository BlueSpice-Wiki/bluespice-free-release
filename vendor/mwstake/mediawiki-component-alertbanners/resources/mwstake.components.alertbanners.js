( function ( mw, $, mwstake ) {

	const _alerts = {}; // eslint-disable-line no-underscore-dangle

	/**
	 * Types and markup inspired by
	 * https://getbootstrap.com/docs/3.3/components/#alerts
	 *
	 * @param {string} id
	 * @param {jQuery} $elem
	 * @param {string} type May be 'success', 'info', 'warning', 'danger'
	 * @return {jQuery} The actual alert wrapper element
	 */
	function _add( id, $elem, type ) { // eslint-disable-line no-underscore-dangle
		type = type || mwstake.alerts.TYPE_WARNING;

		if ( _alerts[ id ] ) {
			const $oldAlert = _alerts[ id ];
			$oldAlert.remove();
		}

		const $box = $( '<div class="alert alert-' + type + '" role="alert">' );
		$box.append( $elem );
		const $container = _getContainer();
		$container.append( $box );

		_alerts[ id ] = $box;

		return $box;
	}

	function _remove( id ) { // eslint-disable-line no-underscore-dangle
		const $box = _alerts[ id ];
		if ( $box ) {
			$box.remove();
			delete ( _alerts[ id ] );
		}
	}

	function _getContainer() { // eslint-disable-line no-underscore-dangle
		const $container = $( '#mwstake-alert-container' );
		return $container;
	}

	// Init server-side generated alerts
	$( () => {
		const $container = _getContainer();
		const $boxes = $container.find( '[data-mwstake-alert-id]' );
		$boxes.each( function () {
			const $box = $( this );
			const id = $box.data( 'mwstake-alert-id' );
			_alerts[ id ] = $box;
		} );

		wireDismissableAlertButtons();
	} );

	window.mwstake = window.mwstake || {};
	window.mwstake.alerts = {
		add: _add,
		remove: _remove,

		// Keep in sync with IAlertProvider constants
		TYPE_SUCCESS: 'success',
		TYPE_INFO: 'info',
		TYPE_WARNING: 'warning',
		TYPE_DANGER: 'danger'
	};

	function wireDismissableAlertButtons() {
		$( '.dismiss-btn' ).each( ( k, el ) => {
			const $button = $( el );
			const btn = OO.ui.infuse( $button );
			btn.connect( btn, {
				click: function () {
					const $alert = this.$element.parents( '.alert' );
					mw.hook( 'mwstake.components.alertbanners.alert.dismiss' ).fire( $alert, this );
					$alert.remove();
				}
			} );
		} );
	}

}( mediaWiki, jQuery ) );
