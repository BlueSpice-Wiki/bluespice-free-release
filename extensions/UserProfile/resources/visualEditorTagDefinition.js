ext.userProfile.tag.UserProfileDefinition = function() {
	ext.userProfile.tag.UserProfileDefinition.super.call( this );
};

OO.inheritClass( ext.userProfile.tag.UserProfileDefinition, bs.vec.util.tag.Definition );

ext.userProfile.tag.UserProfileDefinition.prototype.getCfg = function() {
	var cfg = ext.userProfile.tag.UserProfileDefinition.super.prototype.getCfg.call( this );

	const fields = mw.config.get( 'wgUserProfileAvailableFields' ) || {};
	let options = [];
	for ( const key in fields ) {
		options.push( { data: key, label: fields[key].label || key } );
	}

	return $.extend( cfg, {
		classname : 'UserProfile',
		name: 'userprofile',
		tagname: 'user-profile',
		descriptionMsg: 'userprofile-droplet-name-description',
		menuItemMsg: 'userprofile-droplet-name',
		attributes: [{
			name: 'user',
			labelMsg: 'userprofile-ve-attr-user-label',
			helpMsg: 'userprofile-ve-attr-user-help',
			type: 'user',
		},{
			name: 'framed',
			labelMsg: 'userprofile-ve-attr-framed-label',
			type: 'toggle'
		},{
			name: 'orientation',
			labelMsg: 'userprofile-ve-attr-orientation-label',
			type: 'dropdown',
			default: 'horizontal',
			options: [
				{ data: 'horizontal', label: mw.msg( 'userprofile-ve-attr-orientation-horizontal' ) },
				{ data: 'vertical', label: mw.msg( 'userprofile-ve-attr-orientation-vertical' ) }
			]
		}, {
			name: 'fields',
			labelMsg: 'userprofile-ve-attr-fields',
			helpMsg: 'userprofile-ve-attr-fields-label-help',
			type: 'multiselect',
			allowArbitrary: false,
			options: options,
			valueSeparator: ','
		}]
	});
};

bs.vec.registerTagDefinition(
	new ext.userProfile.tag.UserProfileDefinition()
);

ext.userProfile.tag.UserProfileDefinitionLegacy = function() {
	ext.userProfile.tag.UserProfileDefinitionLegacy.super.call( this );
};

OO.inheritClass( ext.userProfile.tag.UserProfileDefinitionLegacy, bs.vec.util.tag.Definition );

ext.userProfile.tag.UserProfileDefinitionLegacy.prototype.getCfg = function() {
	var cfg = ext.userProfile.tag.UserProfileDefinitionLegacy.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'UserProfileLegacy',
		name: 'userprofileLegacy',
		tagname: 'bs:socialentityprofile',
		descriptionMsg: 'userprofile-droplet-name-description',
		menuItemMsg: 'userprofile-droplet-name',
		attributes: [{
			name: 'username',
			labelMsg: 'userprofile-ve-attr-user-label',
			type: 'text',
		}]
	});
};

bs.vec.registerTagDefinition(
	new ext.userProfile.tag.UserProfileDefinitionLegacy()
);
