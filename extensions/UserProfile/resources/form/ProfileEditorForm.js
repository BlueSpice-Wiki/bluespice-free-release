ext.userProfile.form.Editor = function ( cfg, fields, data ) {
	cfg = cfg || {};
	const formConfig = {
		definition: {
			items: this.getDefinitionItems( fields ),
			buttons: []
		},
		errorReporting: false,
		data: data,
		showTitle: false
	};

	ext.userProfile.form.Editor.parent.call( this, formConfig );
};

OO.inheritClass( ext.userProfile.form.Editor, mw.ext.forms.standalone.Form );
OO.mixinClass( ext.userProfile.form.Editor, OO.EventEmitter );

ext.userProfile.form.Editor.prototype.getDefinitionItems = function ( fields, data ) { // eslint-disable-line no-unused-vars
	const items = [];
	for ( const key in fields ) {
		if ( !fields.hasOwnProperty( key ) ) {
			continue;
		}
		if ( fields[ key ].isMeta || fields[ key ].isReadOnly || !fields[ key ].formDefinition ) {
			continue;
		}
		if ( fields[ key ].formDefinition.hasOwnProperty( 'widget_validate' ) ) {
			const validate = fields[ key ].formDefinition.widget_validate;
			// Check if regex
			if ( /[*/$^]/.test( validate ) ) {
				fields[ key ].formDefinition.widget_validate = new RegExp( validate ); // eslint-disable-line camelcase
			}
		}
		items.push( fields[ key ].formDefinition );
	}

	return items;
};

ext.userProfile.form.Editor.prototype.onRenderComplete = function ( form ) {
	const rawItems = Object.values( form.rawItems );
	if ( !rawItems.length ) {
		return;
	}
	const lastItem = rawItems[ rawItems.length - 1 ];
	const item = form.items.inputs[ lastItem.name ] || null;
	if ( !item ) {
		return;
	}
	item.$element.on( 'focusout', () => {
		this.$element.trigger( 'lastItemFocusOut' );
	} );
};
