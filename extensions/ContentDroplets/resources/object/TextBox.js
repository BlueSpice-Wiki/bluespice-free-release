ext.contentdroplets.object.TextBox = function ( cfg ) {
	ext.contentdroplets.object.TextBox.parent.call( this, cfg );
};

OO.inheritClass( ext.contentdroplets.object.TextBox,
	ext.contentdroplets.object.TransclusionDroplet );

ext.contentdroplets.object.TextBox.prototype.templateMatches = function ( templateData ) {
	if ( !templateData ) {
		return false;
	}

	const target = templateData.target.wt;
	return target.trim( '\n' ) === 'Textbox' &&
		'text-box-' + templateData.params.boxtype.wt === this.getKey();
};

ext.contentdroplets.object.TextBox.prototype.getFormItems = function () {
	const data = this.getDropdownData();
	return [
		{
			name: 'icon',
			label: mw.message( 'contentdroplets-droplet-text-box-show-icon-label' ).text(),
			type: 'checkbox',
			labelAlign: 'inline'
		},
		{
			name: 'boxtype',
			label: mw.message( 'contentdroplets-droplet-text-box-type-label' ).text(),
			type: 'dropdown',
			options: data
		},
		{
			name: 'header',
			label: mw.message( 'contentdroplets-droplet-text-box-header-label' ).text(),
			type: 'textarea',
			rows: 2
		},
		{
			name: 'text',
			label: mw.message( 'contentdroplets-droplet-text-box-text-label' ).text(),
			type: 'textarea',
			rows: 3
		}
	];
};

ext.contentdroplets.object.TextBox.prototype.getDropdownData = function () {
	const types = require( './textboxtypes.json' ); // eslint-disable-line no-shadow
	const data = [];
	let type; // eslint-disable-line no-shadow
	if ( Array.isArray( types ) ) {
		for ( type in types ) {
			data.push( {
				data: types[ type ],
				// The following messages are used here:
				// * contentdroplets-droplet-text-box-success
				// * contentdroplets-droplet-text-box-warning
				// * contentdroplets-droplet-text-box-important
				// * contentdroplets-droplet-text-box-note
				// * contentdroplets-droplet-text-box-tip
				// * contentdroplets-droplet-text-box-neutral
				label: mw.message( 'contentdroplets-droplet-text-box-' + types[ type ] ).text()
			} );
		}
	}
	return data;
};

ext.contentdroplets.object.TextBox.prototype.modifyFormDataBeforeSubmission =
	function ( dataPromise ) {
		// Convert true/false from checkbox control, to yes/no expected by the Textbox template
		const dfd = $.Deferred();
		dataPromise.done( ( data ) => {
			data.icon = data.icon ? 'yes' : 'no';
			dfd.resolve( data );
		} ).fail( function () {
			dfd.reject( arguments );
		} );

		return dfd.promise();
	};

ext.contentdroplets.object.TextBox.prototype.getForm = function ( data ) {
	// convert yes/no to true and false for checkbox control
	if ( data.icon === 'no' ) {
		data.icon = false;
	} else {
		data.icon = true;
	}
	return ext.contentdroplets.object.TextBox.parent.prototype.getForm.call( this, data );
};

ext.contentdroplets.object.TextBox.prototype.registerContentEditable =
	function ( suffix ) {
		suffix = suffix || '';

		const droplet = this,
			classname = this.getClassname( suffix );

		ext.contentdroplets.ce[ classname ] = function ( model, config ) {
			config = config || {};
			config.wikitext = droplet.getContent();
			ext.contentdroplets.ce[ classname ].super.call( this, model, config );
		};
		OO.inheritClass( ext.contentdroplets.ce[ classname ], ve.ce.MWTransclusionNode );

		ext.contentdroplets.ce[ classname ].static.name = 'contentDroplet/' + classname;
		ext.contentdroplets.ce[ classname ].static.tagName = 'div';
		ext.contentdroplets.ce[ classname ].static.primaryCommandName = this.getClassname( 'Command' );
		ve.ce.nodeFactory.register( ext.contentdroplets.ce[ classname ] );
	};

ext.contentdroplets.object.TextBox.prototype.registerDataModel = function ( suffix ) {
	suffix = suffix || '';

	const classname = this.getClassname( suffix ),
		droplet = this;

	ext.contentdroplets.dm[ classname ] = function ( config ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !config.attributes.mw.hasOwnProperty( 'parts' ) ) {
			config.attributes.mw = {
				parts: [
					{
						template: JSON.parse( droplet.getContent() )
					}
				]
			};
		}
		ext.contentdroplets.dm[ classname ].super.apply( this, arguments );
	};

	OO.inheritClass( ext.contentdroplets.dm[ classname ], ve.dm.MWTransclusionNode );

	ext.contentdroplets.dm[ classname ].static.name = 'contentDroplet/' + classname;
	ext.contentdroplets.dm[ classname ].static.matchTagNames = null;
	ext.contentdroplets.dm[ classname ].static.tagName = 'div';
	ext.contentdroplets.dm[ classname ].static.isContent = false;
	ext.contentdroplets.dm[ classname ].static.matchRdfaTypes = [ 'mw:Transclusion' ];
	ext.contentdroplets.dm[ classname ].static.matchFunction = this.matchNode.bind( this );
	if ( !suffix ) {
		ext.contentdroplets.dm[ classname ].static.blockType = 'contentDroplet/' + this.getClassname( 'Block' );
		ext.contentdroplets.dm[ classname ].static.inlineType = 'contentDroplet/' + this.getClassname( 'Inline' );
	}

	ext.contentdroplets.dm[ classname ].static.getWikitext = function ( content ) {
		let i, len, part, template, param,
			wikitext = '';

		// eslint-disable-next-line no-prototype-builtins
		if ( content.hasOwnProperty( 'params' ) ) {
			content = { parts: [ { template: content } ] };
		}
		// Build wikitext from content
		for ( i = 0, len = content.parts.length; i < len; i++ ) {
			part = content.parts[ i ];
			if ( part.template ) {
				// Template
				template = part.template;
				wikitext += '{{' + template.target.wt;
				for ( param in template.params ) {
					wikitext += '|' + param + '=' +
						this.escapeParameter(
							template.params[ param ].wt ||
							template.params[ param ]
						);
				}
				wikitext += '}}';
			} else {
				// Plain wikitext
				wikitext += part;
			}
		}

		return wikitext;
	};

	ext.contentdroplets.dm[ classname ].prototype.getWikitext = function () {
		return ext.contentdroplets.dm[ classname ].static.getWikitext( this.getAttribute( 'mw' ) );
	};
	ve.dm.modelRegistry.register( ext.contentdroplets.dm[ classname ] );
};

// Register all droplets that use this class

const types = require( './textboxtypes.json' );
let type;
for ( type in types ) {
	ext.contentdroplets.registry.register( 'text-box-' + types[ type ], ext.contentdroplets.object.TextBox );
}
