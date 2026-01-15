ext.userProfile.ui.ProfilePanel = function( cfg ) {
	cfg = cfg || {};
	cfg.padded = true;
	cfg.expanded = false;
	ext.userProfile.ui.ProfilePanel.parent.call( this, cfg );

	this.fields = cfg.fields || [];
	this.user = cfg.user || null;
	this.userDisplay = cfg.userDisplay || this.user;
	this.isOwn = cfg.isOwn || false;
	this.editable = cfg.editable || false;
	this.isOnUserPage = cfg.isOnUserPage || false;
	this.setLoading( true );
	this.appendUsername = cfg.appendUsername || false;
	this.fieldsToShow = cfg.fieldsToShow || [];

	this.$element.addClass( 'user-profile-panel' );
	this.$element.attr( 'aria-label', mw.msg( 'userprofile-profile-panel-aria-label', this.userDisplay ) );
};

OO.inheritClass( ext.userProfile.ui.ProfilePanel, OO.ui.PanelLayout );

ext.userProfile.ui.ProfilePanel.prototype.initialize = function() {
	this.loadData().done( function( response ) {
		var data = response.data || {};
		var fields = response.fields || {};
		this.$imageCnt = $( '<div>' ).addClass( 'user-profile-image-cnt' );
		this.$dataCnt = $( '<div>' ).addClass( 'user-profile-data-cnt' );
		this.renderImage( data.imageUrl || '' );
		this.renderHeader( data, fields );
		this.renderFields( data, fields );
		setTimeout( function() {
			// It needs that long for image to render, so we can avoid flickering
			this.setLoading( false );
			this.$element.append( this.$imageCnt, this.$dataCnt );
		}.bind( this ), 1000 );
	}.bind( this ) );
};

ext.userProfile.ui.ProfilePanel.prototype.loadData = function() {
	var dfd = $.Deferred();
	$.ajax( {
		url: mw.util.wikiScript( 'rest' ) + '/userprofile/v1/' + this.user
	} ).done( function( data ) {
		dfd.resolve( data );
	}.bind( this ) ).fail( function( err ) {
		if ( err.responseJSON.message === 'User not found' ) {
			this.enterErrorMode( 'userprofile-user-not-exist-error' );
		} else {
			console.error( err );
			this.enterErrorMode();
		}
	}.bind( this ) );
	return dfd.promise();
};

ext.userProfile.ui.ProfilePanel.prototype.enterErrorMode = function( messageKey = 'userprofile-general-error' ) {
	this.setLoading( false );
	this.$element.addClass( 'user-profile-error' );
	this.$element.html(
		new OO.ui.MessageWidget( {
			type: 'error',
			label: mw.message( messageKey ).text()
		} ).$element
	);
};

ext.userProfile.ui.ProfilePanel.prototype.setLoading = function( loading ) {
	if ( loading ) {
		this.$element.empty();
		this.$element.addClass( 'user-profile-loading' );
	} else {
		this.$element.removeClass( 'user-profile-loading' );
	}
};

ext.userProfile.ui.ProfilePanel.prototype.renderImage = function( imageUrl ) {
	var $img = $( '<img>' ).attr( 'src', imageUrl ).addClass( 'user-profile-image' );
	$img.attr( 'alt', mw.msg( 'userprofile-profile-image-alt', this.userDisplay ) );
	if ( this.editable && this.isOwn ) {
		this.$imageCnt.append(
			this.getImageEditWidget().$element
		);
	}
	this.$imageCnt.append( $img );
};

ext.userProfile.ui.ProfilePanel.prototype.renderHeader = function( data, fields ) {
	var hasRealName = data.realName && data.realName.length > 0,
		mainName = hasRealName ? data.realName : data.username,
		$nameHeader = $( '<div>' ).addClass( 'user-profile-name-header' ),
		nameLabel = new OO.ui.LabelWidget( {
			label: mainName,
			framed: false,
			title: hasRealName ?
				mw.msg( 'userprofile-profile-name-title' ) :
				mw.msg( 'userprofile-profile-username-title' ),
			classes: [ 'user-profile-name' ]
		} );
	if ( !this.isOnUserPage ) {
		$nameHeader.append(
			$( '<a>' ).attr( 'href', mw.util.getUrl( 'User:' + data.username ) ).append( nameLabel.$element )
		);

	} else {
		$nameHeader.append(
			nameLabel.$element
		);
	}

	if ( hasRealName && this.appendUsername ) {
		$nameHeader.append(
			new OO.ui.LabelWidget( {
				label: '(@' + data.username + ')',
				title: mw.msg( 'userprofile-profile-username-title' ),
				classes: [ 'user-profile-username' ]
			} ).$element
		);
	}

	this.$dataCnt.append( $nameHeader );

	var metas = [];
	for ( var key in fields ) {
		if (
			this.shouldShow( key ) &&
			fields.hasOwnProperty( key ) &&
			fields[key].hasOwnProperty( 'isMeta' ) &&
			fields[key].isMeta
		) {
			if ( !data.hasOwnProperty( key ) ) {
				continue;
			}
			metas.push( $( '<div>' ).addClass( 'user-profile-meta' ).text( fields[key].label + ': ' + data[key] ) );
		}
	}

	if ( metas.length > 0 ) {
		this.$dataCnt.append( $( '<div>' ).addClass( 'user-profile-meta-cnt' ).append( metas ) );
	}
};

ext.userProfile.ui.ProfilePanel.prototype.renderFields = function( data, fields ) {
	var layouts = [];
	for ( var key in fields ) {
		if ( !this.shouldShow( key ) ) {
			continue;
		}
		if ( !fields.hasOwnProperty( key ) ) {
			continue;
		}
		if ( fields[key].isMeta || fields[key].isSystem || data.hasOwnProperty( key ) === false || data[key] === '' ) {
			continue;
		}
		layouts.push( this.renderField( key, data[key], fields[key] ) );
	}

	this.$dataCnt.append(
		$( '<div>' ).addClass( 'user-profile-fields-cnt' ).append( layouts )
	);
};

ext.userProfile.ui.ProfilePanel.prototype.renderField = function( key, value, field ) {
	var $field = $( '<div>' ).addClass( 'user-profile-field' ),
		$label = $( '<div>' ).addClass( 'user-profile-field-label' ).text( field.label ),
		$value = $( '<div>' ).addClass( 'user-profile-field-value' ).text( value );

	if ( field.hasOwnProperty( 'url' ) ) {
		$value = $( '<a>' )
			.attr( 'href', field.url.replace( '{value}', value ) )
			.addClass( 'user-profile-field-value' )
			.text( value );
	}

	$field.append( $label, $value );
	return $field;
};

ext.userProfile.ui.ProfilePanel.prototype.getImageEditWidget = function() {
	this.editImageButton = new OO.ui.ButtonMenuSelectWidget( {
		icon: 'edit',
		framed: false,
		classes: [ 'user-profile-edit-image-btn' ],
		label: mw.msg( 'userprofile-edit-image' ),
		menu: {
			horizontalPosition: 'end',
			items: []
		}
	} );

	this.editImageButton.getMenu().connect( this, {
		select: function( item ) {
			if ( item instanceof OO.ui.MenuOptionWidget ) {
				if ( item.getData() instanceof ext.userProfile.ui.ProfileImageProvider ) {
					item.getData().execute();
				}
			}
		}
	} );

	const providerModules = ext.userProfile.profileImage.providerModules;
	mw.loader.using( providerModules ).done( function() {
		const registry = ext.userProfile.profileImage.providerRegistry.registry;
		for ( var key in registry ) {
			if ( !registry.hasOwnProperty( key ) ) {
				continue;
			}
			var provider = registry[key];
			this.editImageButton.getMenu().addItems( [
				new OO.ui.MenuOptionWidget( {
					data: provider,
					label: provider.getLabel()
				} )
			] );
		}
	}.bind( this ) );

	return this.editImageButton;
};

ext.userProfile.ui.ProfilePanel.prototype.shouldShow = function( key ) {
	if ( this.fieldsToShow.length === 0 ) {
		return true;
	}
	return this.fieldsToShow.indexOf( key ) !== -1;
};