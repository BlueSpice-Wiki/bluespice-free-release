ext.contentdroplets.object.ParserFunctionDroplet = function ( cfg ) {
	ext.contentdroplets.object.ParserFunctionDroplet.parent.call( this, cfg );
};

OO.inheritClass( ext.contentdroplets.object.ParserFunctionDroplet,
	ext.contentdroplets.object.TransclusionDroplet );

ext.contentdroplets.object.ParserFunctionDroplet.prototype.registerOverrides = function () {
	this.registerDataModel( 'Block' );
	this.registerDataModel( 'Inline' );
	this.registerDataModel();
	this.registerContentEditable( 'Block' );
	this.registerContentEditable( 'Inline' );
	this.registerContentEditable();
	this.registerCommand();
	this.registerContextItem();
	this.registerInspector();
};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.matchNode = function ( domElement ) {
	const data = $( domElement ).data();
	return this.functionMatches( this.getTemplateFromData( data ) );
};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.functionMatches =
// eslint-disable-next-line no-unused-vars
	function ( data ) {
		return false;
	};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.getMainParam = function () {
	return '';
};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.convertDataForForm = function ( data ) {
	return data;
};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.updateMWData =
	function ( formData, mwData ) {
		const newData = Object.assign( {}, formData || {} );

		// eslint-disable-next-line no-prototype-builtins
		const template = ( mwData.hasOwnProperty( 'parts' ) && mwData.parts.length > 0 &&
			// eslint-disable-next-line no-prototype-builtins
			mwData.parts[ 0 ].hasOwnProperty( 'template' ) ) ? mwData.parts[ 0 ].template : null;
		let key;
		if ( !template ) {
			return mwData;
		}
		const mainValue = newData[ this.getMainParam() ] || '';
		if ( mainValue ) {
			template.target.wt = `#${ template.target.function || '' }:` + mainValue;
			delete newData[ this.getMainParam() ];
		}

		let paramCounter = 1;
		for ( key in newData ) {
			// necessary for checkboxes and templates with yes and no
			if ( typeof newData[ key ] === 'boolean' ) {
				if ( newData[ key ] === true ) {
					newData[ paramCounter ] = { wt: key };
					paramCounter++;
				}
				delete newData[ key ];
			} else if ( typeof newData[ key ] === 'string' ) {
				newData[ key ] = { wt: newData[ key ] };
			}
		}
		template.params = newData;

		mwData.parts[ 0 ].template = template;
		return mwData;
	};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.getTemplateFromData = function ( data ) {
	// eslint-disable-next-line no-prototype-builtins
	if ( !data || !data.hasOwnProperty( 'mw' ) || !data.mw.hasOwnProperty( 'parts' ) ) {
		return false;
	}

	const parts = data.mw.parts;
	// eslint-disable-next-line no-prototype-builtins
	if ( parts.length === 0 || !parts[ 0 ].hasOwnProperty( 'template' ) ) {
		return false;
	}

	if (
		!Object.prototype.hasOwnProperty.call( parts[ 0 ].template, 'target' ) ||
		!Object.prototype.hasOwnProperty.call( parts[ 0 ].template.target, 'function' )
	) {
		return false;
	}
	return parts[ 0 ].template;
};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.toDataElement =
	// eslint-disable-next-line no-unused-vars
	function ( domElements, converter ) {
		return false;
	};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.registerDataModel = function ( suffix ) {
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

	OO.inheritClass( ext.contentdroplets.dm[ classname ], ve.dm.MWTransclusionInlineNode );

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
				const formattedParams = [];
				for ( param in template.params ) {
					if ( param.match( /^\d+$/ ) ) {
						// Non-named params
						formattedParams.push( this.escapeParameter(
							template.params[ param ].wt || template.params[ param ]
						) );
					} else {
						// Named params
						formattedParams.push( param + '=' + this.escapeParameter(
							template.params[ param ].wt ||
							template.params[ param ]
						) );
					}

				}
				if ( formattedParams.length ) {
					wikitext += '|' + formattedParams.join( '|' );
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

ext.contentdroplets.object.ParserFunctionDroplet.prototype.registerContentEditable =
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

ext.contentdroplets.object.ParserFunctionDroplet.prototype.registerContextItem = function () {
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

ext.contentdroplets.object.ParserFunctionDroplet.prototype.registerCommand = function () {
	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			this.getClassname( 'Command' ), 'window', 'toggle',
			{ args: [ this.getClassname( 'Inspector' ), this ] }
		)
	);
};

ext.contentdroplets.object.ParserFunctionDroplet.prototype.registerInspector = function () {
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
			)
		);
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
			let formData = {};
			const wt = template.target.wt;
			const func = template.target.function || '';
			// Strip #func: from wt
			if ( wt.indexOf( `#${ func }:` ) === 0 ) {
				formData[ droplet.getMainParam() ] = wt.slice( func.length + 2 );
			}
			let key;
			for ( key in params ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( !params.hasOwnProperty( key ) ) {
					continue;
				}

				if ( typeof params[ key ] === 'object' ) {
					formData[ key ] = params[ key ].wt;
				} else {
					formData[ key ] = params[ key ];
				}
			}
			formData = droplet.convertDataForForm( formData );
			const form = droplet.getForm( formData );
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

ext.contentdroplets.object.ParserFunctionDroplet.prototype.getVeInsertCommand = function () {
	return this.getClassname( 'Command' );
};
