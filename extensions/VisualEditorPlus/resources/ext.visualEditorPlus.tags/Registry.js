ext.visualEditorPlus.ui.tag.Registry = function BsVecUtilTagRegistry() {
	this.definitions = [];
};

OO.initClass( ext.visualEditorPlus.ui.tag.Registry );

ext.visualEditorPlus.ui.tag.Registry.prototype.registerTagDefinition = function ( definition ) {
	if ( !( definition instanceof ext.visualEditorPlus.ui.tag.Definition ) ) {
		throw new Error( 'Invalid tag definition, must be instance of ext.visualEditorPlus.ui.tag.Definition' );
	}
	this.definitions.push( definition );
};

ext.visualEditorPlus.ui.tag.Registry.prototype.initialize = function () {
	for ( let i = 0; i < this.definitions.length; i++ ) {
		this.register( this.definitions[ i ] );
	}
};

ext.visualEditorPlus.ui.tag.Registry.prototype.register = function ( definition ) {
	this.createCeForTag( definition );
	this.createDmForTag( definition );
	this.createCommandForTag( definition );
	this.createToolForTag( definition );
	this.createInspectorForTag( definition );
};

ext.visualEditorPlus.ui.tag.Registry.prototype.createCeForTag = function ( definition ) {
	const classname = definition.classname + 'Node';
	ext.visualEditorPlus.ce[ classname ] = function () {
		ext.visualEditorPlus.ce[ classname ].super.apply( this, arguments );
	};

	OO.inheritClass( ext.visualEditorPlus.ce[ classname ], ve.ce.MWInlineExtensionNode );

	ext.visualEditorPlus.ce[ classname ].static.name = definition.name;
	ext.visualEditorPlus.ce[ classname ].static.primaryCommandName = definition.tagname;

	ext.visualEditorPlus.ce[ classname ].static.rendersEmpty = definition.rendersEmpty;

	ext.visualEditorPlus.ce[ classname ].prototype.validateGeneratedContents = function ( $element ) {
		if ( $element.is( 'span' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
			return false;
		}
		return true;
	};

	ve.ce.nodeFactory.register( ext.visualEditorPlus.ce[ classname ] );
};

ext.visualEditorPlus.ui.tag.Registry.prototype.createDmForTag = function ( definition ) {
	const classname = definition.classname + 'Node';
	ext.visualEditorPlus.dm[ classname ] = function () {
		ext.visualEditorPlus.dm[ classname ].super.apply( this, arguments );
	};

	OO.inheritClass( ext.visualEditorPlus.dm[ classname ], ve.dm.MWInlineExtensionNode );

	ext.visualEditorPlus.dm[ classname ].static.name = definition.name;
	ext.visualEditorPlus.dm[ classname ].static.tagName = definition.tagname;

	// Name of the parser tag
	ext.visualEditorPlus.dm[ classname ].static.extensionName = definition.tagname;

	// This tag renders without content
	if ( definition.rendersEmpty ) {
		ext.visualEditorPlus.dm[ classname ].static.childNodeTypes = [];
		ext.visualEditorPlus.dm[ classname ].static.isContent = true;
	}

	ve.dm.modelRegistry.register( ext.visualEditorPlus.dm[ classname ] );
};

ext.visualEditorPlus.ui.tag.Registry.prototype.createCommandForTag = function ( definition ) {
	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			definition.name + 'Command', 'window', 'open',
			{ args: [ definition.name + 'Inspector' ] }
		)
	);
};

ext.visualEditorPlus.ui.tag.Registry.prototype.createToolForTag = function ( definition ) {
	const classname = definition.classname + 'InspectorTool';
	ext.visualEditorPlus.ui[ classname ] = function ( toolGroup, config ) {
		ext.visualEditorPlus.ui[ classname ].super.call( this, toolGroup, config );
	};
	OO.inheritClass( ext.visualEditorPlus.ui[ classname ], ve.ui.FragmentInspectorTool );

	ext.visualEditorPlus.ui[ classname ].static.name = definition.name + 'Tool';
	ext.visualEditorPlus.ui[ classname ].static.group = definition.toolGroup;
	ext.visualEditorPlus.ui[ classname ].static.autoAddToCatchall = false;
	ext.visualEditorPlus.ui[ classname ].static.icon = definition.icon;
	ext.visualEditorPlus.ui[ classname ].static.title = definition.menuMessage;
	ext.visualEditorPlus.ui[ classname ].static.modelClasses = [ ext.visualEditorPlus.dm[ definition.classname + 'Node' ] ];
	ext.visualEditorPlus.ui[ classname ].static.commandName = definition.name + 'Command';
	ve.ui.toolFactory.register( ext.visualEditorPlus.ui[ classname ] );
};

ext.visualEditorPlus.ui.tag.Registry.prototype.createInspectorForTag = function ( definition ) {
	const classname = definition.classname + 'Inspector';
	ext.visualEditorPlus.ui[ classname ] = function ( config ) {
		ext.visualEditorPlus.ui[ classname ].super.call( this, ve.extendObject( { padded: false, expanded: true, scrollable: false }, config ) );
		this.pendingSetValue = null;
		this.commandParams = {};
	};

	OO.inheritClass( ext.visualEditorPlus.ui[ classname ], ve.ui.MWLiveExtensionInspector );

	ext.visualEditorPlus.ui[ classname ].static.name = definition.name + 'Inspector';
	ext.visualEditorPlus.ui[ classname ].static.title = definition.menuMessage;
	ext.visualEditorPlus.ui[ classname ].static.modelClasses = [ ext.visualEditorPlus.dm[ definition.classname + 'Node' ] ];
	if ( definition.rendersEmpty ) {
		ext.visualEditorPlus.ui[ classname ].static.allowedEmpty = true;
		ext.visualEditorPlus.ui[ classname ].static.selfCloseEmptyBody = true;
	}

	ext.visualEditorPlus.ui[ classname ].prototype.initialize = function () {
		ext.visualEditorPlus.ui[ classname ].super.prototype.initialize.call( this );
		this.indexLayout = new OO.ui.PanelLayout( {
			scrollable: false,
			expanded: false,
			padded: true
		} );

		this.$content.addClass( 've-ui-' + definition.name + '-inspector-content' ); // eslint-disable-line mediawiki/class-doc

		if ( definition.hideMainInput === true ) {
			this.input.$element.remove();
		}

		if ( definition.description ) {
			// Add description field
			this.descriptionField = new OO.ui.LabelWidget( {
				label: $( '<div class="bs-vec-inspector-desc">' + definition.description + '</div>' )
			} );
			this.descriptionLayout = new OO.ui.PanelLayout( { padded: true, expanded: false, scrollable: false } );
			this.descriptionLayout.$element.append( this.descriptionField.$element );
			this.form.$element.prepend( this.descriptionLayout.$element );
		}

		// This is needed because tab panels have absolute position and no size.
		this.indexLayout.$element.append(
			$( '<div style="height:0px; width:100%;"></div>' ) // eslint-disable-line no-jquery/no-parse-html-literal
		);

		this.indexLayout.$element.append(
			this.generatedContentsError.$element
		);

		// Add all other fields
		this.form.$element.append(
			this.indexLayout.$element
		);

		definition.createForm( this ).done( ( form ) => {
			if ( !form ) {
				return;
			}
			this.inspectorForm = form;
			this.inspectorForm.connect( this, {
				renderComplete: 'updateSize'
			} );
			this.inspectorForm.render();
			this.indexLayout.$element.append( this.inspectorForm.$element );
			this.updateSize();
			if ( this.pendingSetValue ) {
				definition.setValues( this, this.pendingSetValue );
				this.actions.setAbilities( { done: true } );
				this.pendingSetValue = null;
			}
		} ).fail( ( error ) => {
			console.error( error ); // eslint-disable-line no-console
		} );
	};

	ext.visualEditorPlus.ui[ classname ].prototype.getSetupProcess = function ( data ) {
		return ext.visualEditorPlus.ui[ classname ].super.prototype.getSetupProcess.call( this, data )
			.next( function () {
				const attributes = this.selectedNode.getAttribute( 'mw' ).attrs;
				if ( data.commandParams || null ) {
					this.commandParams = data.commandParams;
				}
				if ( !this.inspectorForm ) {
					this.pendingSetValue = attributes;
					return;
				}
				definition.setValues( this, attributes );
				this.actions.setAbilities( { done: true } );
			}, this );
	};

	ext.visualEditorPlus.ui[ classname ].prototype.getTeardownProcess = function ( data ) {
		return ext.visualEditorPlus.ui[ classname ].super.prototype.getTeardownProcess.call( this, data )
			.next( function () {
				definition.onTeardown( data, this );
			}, this );
	};

	ext.visualEditorPlus.ui[ classname ].prototype.updateMwData = function ( mwData ) {
		ext.visualEditorPlus.ui[ classname ].super.prototype.updateMwData.call( this, mwData );

		definition.updateMwData( this, mwData );
	};

	ext.visualEditorPlus.ui[ classname ].prototype.getNewElement = function () {
		const element = ext.visualEditorPlus.ui[ classname ].super.prototype.getNewElement.call( this );

		return definition.getNewElement( this, element );
	};

	for ( const method in definition.inspectorMethods ) {
		if ( typeof definition[ method ] === 'function' ) {
			ext.visualEditorPlus.ui[ classname ].prototype[ method ] = definition[ method ];
		}
	}

	ve.ui.windowFactory.register( ext.visualEditorPlus.ui[ classname ] );
};
