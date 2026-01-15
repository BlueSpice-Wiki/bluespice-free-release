(function( mw, $, mwstake ) {

	var _alerts = {};

	/**
	 * Types and markup inspired by
	 * https://getbootstrap.com/docs/3.3/components/#alerts
	 * @param string id
	 * @param jQuery $elem
	 * @param string type May be 'success', 'info', 'warning', 'danger'
	 * @returns jQuery The actual alert wrapper element
	 */
	function _add( id, $elem, type ) {
		type = type || mwstake.alerts.TYPE_WARNING;

		if( _alerts[id] ) {
			var $oldAlert = _alerts[id];
			$oldAlert.remove();
		}

		var $box = $( '<div class="alert alert-' + type + '" role="alert">' );
		$box.append( $elem );
		var $container = _getContainer();
		$container.append( $box );

		_alerts[id] = $box;

		return $box;
	}

	function _remove( id ) {
		var $box = _alerts[id];
		if( $box ) {
			$box.remove();
			delete( _alerts[id] );
		}
	}

	function _getContainer() {
		var $container = $( '#mwstake-alert-container' );
		return $container;
	}

	//Init server-side generated alerts
	$(function() {
		var $container = _getContainer();
		var $boxes = $container.find( '[data-mwstake-alert-id]' );
		$boxes.each( function() {
			var $box = $(this);
			var id = $box.data( 'mwstake-alert-id' );
			_alerts[id] = $box;
		} );

		wireDismissableAlertButtons();
	});

	window.mwstake = window.mwstake || {};
	window.mwstake.alerts = {
		add: _add,
		remove: _remove,

		//Keep in sync with IAlertProvider constants
		TYPE_SUCCESS: 'success',
		TYPE_INFO: 'info',
		TYPE_WARNING: 'warning',
		TYPE_DANGER: 'danger'
	};


	function wireDismissableAlertButtons() {
		$( '.dismiss-btn' ).each( function( k, el ) {
			var $button = $( el );
			var btn = OO.ui.infuse( $button );
			btn.connect( btn, {
				click: function() {
					var $alert = this.$element.parents( '.alert' );
					mw.hook( 'mwstake.components.alertbanners.alert.dismiss' ).fire( $alert, this );
					$alert.remove();
				}
			} );
		} );
	}

})( mediaWiki, jQuery );
