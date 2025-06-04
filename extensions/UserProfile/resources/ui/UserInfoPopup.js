ext.userProfile.ui.UserInfoPopup = function ( cfg, $floatableContainer ) {
	this.username = cfg.username || '';
	this.content = new OO.ui.PanelLayout( {
		padded: false,
		expanded: false
	} );
	this.autoClose = true;
	this.autoFlip = true;
	this.userPanel = cfg.userPanel || null;

	cfg = cfg || {};
	cfg = $.extend( {
		$floatableContainer	: $floatableContainer,
		$content: this.content.$element,
		padded: false,
		align: 'forwards'
	}, cfg );
	ext.userProfile.ui.UserInfoPopup.parent.call( this, cfg );
	this.$element.addClass( 'ext-userprofile-userinfopopup' );
	this.$element.attr( 'data-username', this.username );
};

OO.inheritClass( ext.userProfile.ui.UserInfoPopup, OO.ui.PopupWidget );

ext.userProfile.ui.UserInfoPopup.prototype.makeContent = function () {
	if ( this.userPanel ) {
		this.content.$element.append( this.userPanel.$element );
		return;
	}
	mw.loader.using( 'ext.userProfile.main', function() {
		const userpanel = new ext.userProfile.ui.ProfilePanel( {
			user: this.username,
			isOwn: false,
			editable: false,
			isOnUserPage: false,
			appendUsername: false,
			fieldsToShow: [ 'email', 'phone' ]
		} );
		userpanel.$element.addClass( 'user-profile-vertical' );
		this.content.$element.append( userpanel.$element );
		userpanel.initialize();
		this.emit( 'infoReady', userpanel );
	}.bind( this ) );
};