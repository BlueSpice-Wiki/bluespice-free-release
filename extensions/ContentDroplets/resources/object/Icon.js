ext.contentdroplets.object.Icon = function ( cfg ) {
	ext.contentdroplets.object.Icon.parent.call( this, cfg );
};

OO.inheritClass( ext.contentdroplets.object.Icon, ext.contentdroplets.object.TransclusionDroplet );

ext.contentdroplets.object.Icon.prototype.templateMatches = function ( templateData ) {
	if ( !templateData ) {
		return false;
	}

	const target = templateData.target.wt;
	return target.trim( '\n' ) === 'Icon';
};

ext.contentdroplets.object.Icon.prototype.getFormItems = function () {
	return [
		{
			name: 1,
			label: mw.message( 'contentdroplets-droplet-icon-class-label' ).text(),
			type: 'text'
		},
		{
			name: 2,
			label: mw.message( 'contentdroplets-droplet-icon-font-size-label' ).text(),
			type: 'text'
		},
		{
			name: 3,
			label: mw.message( 'contentdroplets-droplet-icon-color-label' ).text(),
			type: 'text'
		},
		{
			name: 4,
			label: mw.message( 'contentdroplets-droplet-icon-orientation-label' ).text(),
			type: 'text'
		}
	];
};

ext.contentdroplets.object.Icon.prototype.updateMWData = function ( newData, mwData ) {
	newData = newData || {};

	// eslint-disable-next-line no-prototype-builtins
	const template = ( mwData.hasOwnProperty( 'parts' ) && mwData.parts.length > 0 &&
		// eslint-disable-next-line no-prototype-builtins
		mwData.parts[ 0 ].hasOwnProperty( 'template' ) ) ? mwData.parts[ 0 ].template : null;
	let key;
	if ( !template ) {
		return mwData;
	}
	for ( key in template.params ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !template.params.hasOwnProperty( key ) ) {
			continue;
		}
		if ( typeof template.params[ key ] === 'string' ) {
			template.params[ key ] = { wt: template.params[ key ] };
		}
		// eslint-disable-next-line no-prototype-builtins
		if ( newData && !newData.hasOwnProperty( key ) ) {
			delete template.params[ key ];
		}
	}
	for ( key in newData ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( newData.hasOwnProperty( key ) ) {
			template.params[ key ] = { wt: newData[ key ] };
		}
	}

	mwData.parts[ 0 ].template = template;
	return mwData;
};

ext.contentdroplets.registry.register( 'icon', ext.contentdroplets.object.Icon );
