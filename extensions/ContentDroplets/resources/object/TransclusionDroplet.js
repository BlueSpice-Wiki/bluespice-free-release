ext.contentdroplets.object.TransclusionDroplet = function ( cfg ) {
	ext.contentdroplets.object.TransclusionDroplet.parent.call( this, cfg );
};

OO.inheritClass( ext.contentdroplets.object.TransclusionDroplet,
	ext.contentdroplets.object.CustomInspectorDroplet );

ext.contentdroplets.object.TransclusionDroplet.prototype.registerOverrides = function () {
	this.registerDataModel( 'Block' );
	this.registerDataModel( 'Inline' );
	this.registerDataModel();
	this.registerContentEditable( 'Block' );
	this.registerContentEditable( 'Inline' );
	this.registerContentEditable();
	this.registerCommand();
	if ( !this.preload ) {
		this.registerContextItem();
		this.registerInspector();
	}
};

ext.contentdroplets.object.TransclusionDroplet.prototype.matchNode = function ( domElement ) {
	const data = $( domElement ).data();
	return this.templateMatches( this.getTemplateFromData( data ) );
};

ext.contentdroplets.object.TransclusionDroplet.prototype.templateMatches =
// eslint-disable-next-line no-unused-vars
	function ( templateData ) {
		return false;
	};

ext.contentdroplets.object.TransclusionDroplet.prototype.updateMWData =
	function ( newData, mwData ) {
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
			// necessary for checkboxes and templates with yes and no
			if ( typeof template.params[ key ] === 'boolean' ) {
				if ( template.params[ key ] === true ) {
					template.params[ key ] = 'yes';
				} else {
					template.params[ key ] = 'no';
				}
				template.params[ key ] = { wt: template.params[ key ].toString() };
			}

			// eslint-disable-next-line no-prototype-builtins
			if ( newData.hasOwnProperty( key ) ) {
				template.params[ key ] = { wt: newData[ key ] };
			}
		}

		mwData.parts[ 0 ].template = template;
		return mwData;
	};

ext.contentdroplets.object.TransclusionDroplet.prototype.getTemplateFromData = function ( data ) {
	// eslint-disable-next-line no-prototype-builtins
	if ( !data || !data.hasOwnProperty( 'mw' ) || !data.mw.hasOwnProperty( 'parts' ) ) {
		return false;
	}

	const parts = data.mw.parts;
	// eslint-disable-next-line no-prototype-builtins
	if ( parts.length === 0 || !parts[ 0 ].hasOwnProperty( 'template' ) ) {
		return false;
	}

	return parts[ 0 ].template;
};

ext.contentdroplets.object.TransclusionDroplet.prototype.toDataElement =
	// eslint-disable-next-line no-unused-vars
	function ( domElements, converter ) {
		return false;
	};

ext.contentdroplets.object.TransclusionDroplet.prototype.registerDataModel = function ( suffix ) {
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
	ext.contentdroplets.dm[ classname ].static.tagName = 'span';
	ext.contentdroplets.dm[ classname ].static.isContent = true;
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

ext.contentdroplets.object.TransclusionDroplet.prototype.registerContentEditable =
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
		ext.contentdroplets.ce[ classname ].static.tagName = 'span';
		ext.contentdroplets.ce[ classname ].static.primaryCommandName = this.getClassname( 'Command' );
		ve.ce.nodeFactory.register( ext.contentdroplets.ce[ classname ] );
	};

ext.contentdroplets.object.TransclusionDroplet.prototype.registerContextItem = function () {
	const classname = this.getClassname( 'ContextItem' );

	ext.contentdroplets.ui[ classname ] = function () {
		ext.contentdroplets.ui[ classname ].super.apply( this, arguments );
	};
	OO.inheritClass( ext.contentdroplets.ui[ classname ], ve.ui.MWTransclusionContextItem );

	ext.contentdroplets.ui[ classname ].static.name = this.getKey();
	ext.contentdroplets.ui[ classname ].static.title = this.getName();
	ext.contentdroplets.ui[ classname ].static.label = this.getName();
	ext.contentdroplets.ui[ classname ].static.icon = 'contentDroplet';
	ext.contentdroplets.ui[ classname ].static.modelClasses = [
		ext.contentdroplets.dm[ this.getClassname() ],
		ext.contentdroplets.dm[ this.getClassname( 'Block' ) ],
		ext.contentdroplets.dm[ this.getClassname( 'Inline' ) ]
	];
	ext.contentdroplets.ui[ classname ].static.commandName = this.getClassname( 'Command' );
	ext.contentdroplets.ui[ classname ].prototype.getDescription = function () {
		return this.description;
	}.bind( this );

	ext.contentdroplets.ui[ classname ].prototype.onEditButtonClick = function () {
		const surface = ve.init.target.getSurface(),
			command = ve.ui.commandRegistry.lookup( this.getClassname( 'Command' ) );

		if ( command ) {
			command.execute( surface, undefined, 'context' );
		}
	}.bind( this );

	ve.ui.contextItemFactory.register( ext.contentdroplets.ui[ classname ] );
};

ext.contentdroplets.object.TransclusionDroplet.prototype.registerCommand = function () {
	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			this.getClassname( 'Command' ), 'window', 'toggle',
			{ args: [ this.getClassname( 'Inspector' ), this ] }
		)
	);
};

ext.contentdroplets.object.TransclusionDroplet.prototype.registerInspector = function () {
	const droplet = this,
		classname = this.getClassname( 'Inspector' );
	ext.contentdroplets.ui[ classname ] = function ( config ) {
		ext.contentdroplets.ui[ classname ].super.call(
			this,
			ve.extendObject( {
				padded: false,
				expanded: true,
				scrollable: false
			},
			config
			) );
	};

	OO.inheritClass( ext.contentdroplets.ui[ classname ], ve.ui.MWLiveExtensionInspector );

	ext.contentdroplets.ui[ classname ].static.name = classname;
	ext.contentdroplets.ui[ classname ].static.title = this.getName();
	ext.contentdroplets.ui[ classname ].static.modelClasses = [
		ext.contentdroplets.dm[ this.getClassname() ],
		ext.contentdroplets.dm[ this.getClassname( 'Block' ) ],
		ext.contentdroplets.dm[ this.getClassname( 'Inline' ) ]
	];
	ext.contentdroplets.ui[ classname ].prototype.initialize = function () {
		ext.contentdroplets.ui[ classname ].super.prototype.initialize.call( this );
	};

	ext.contentdroplets.ui[ classname ].prototype.getSetupProcess = function ( data ) {
		return ext.contentdroplets.ui[ classname ].super.prototype.getSetupProcess.call(
			this, data
		).next( function () {
			const attributes = this.selectedNode.element.attributes || {};
			const template = droplet.getTemplateFromData( attributes );
			const params = template.params;
			let key;

			for ( key in params ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( !params.hasOwnProperty( key ) ) {
					continue;
				}
				if ( typeof params[ key ] === 'object' ) {
					params[ key ] = params[ key ].wt;
				}
			}
			const form = droplet.getForm( params );
			form.connect( this, {
				renderComplete: 'updateSize',
				change: 'onValueUpdated'
			} );
			form.render();
			this.$body.html( new OO.ui.PanelLayout( {
				expanded: false,
				padded: true,
				content: [
					form
				]
			} ).$element );
			form.$element.addClass( 'nopadding' );

			this.actions.setAbilities( { done: true } );
			this.updateSize();

		}, this );
	};

	ext.contentdroplets.ui[ classname ].prototype.getReadyProcess = function ( data ) {
		return ext.contentdroplets.ui[ classname ].super.prototype.getReadyProcess.call(
			this, data
		).next( function () {
			this.focus();
		}, this );
	};

	ext.contentdroplets.ui[ classname ].prototype.getBodyHeight = function () {
		return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 10;
	};

	ext.contentdroplets.ui[ classname ].prototype.onValueUpdated = function ( promise ) {
		promise.done( ( d ) => {
			this.dataToUpdate = d;
			this.onChange();
		} );
	};

	ext.contentdroplets.ui[ classname ].prototype.getActionProcess = function ( action ) {
		if ( action === 'done' ) {
			return new OO.ui.Process( () => {
				this.insertOrUpdateNode();
				this.close( { action: 'done' } );
			} );
		}

		return ext.contentdroplets.ui[ classname ].parent.prototype.getActionProcess.call(
			this, action
		);
	};

	ext.contentdroplets.ui[ classname ].prototype.getNewElement = function () {
		return {
			type: 'contentDroplet/' + droplet.getClassname(),
			attributes: {
				mw: {
					parts: [
						{
							template: JSON.parse( droplet.getContent() )
						}
					]
				}
			}
		};
	};

	ext.contentdroplets.ui[ classname ].prototype.updateMwData = function ( mwData ) {
		droplet.updateMWData( this.dataToUpdate, mwData );
	};

	ve.ui.windowFactory.register( ext.contentdroplets.ui[ classname ] );
};

ext.contentdroplets.object.TransclusionDroplet.prototype.getVeInsertCommand = function () {
	return this.getClassname( 'Command' );
};
