/**
 * @param {Object} spec
 *
 * Config object to define a tag and its attributes. Should be extended by sub class.
 * Params:
 * * classname: (string) Generic name for the classes to be created. Will be used in
 * classnameInspector, classnameNode, etc.
 * * name: (string) Internal name for the tag.
 * * tagname: (string) Name of the tag as used in wikicode.
 * * description: (string) Message for the description section.
 * * menuMessage: (string) Message for the tool menu item.
 * * rendersEmpty: (bool) true if the tag does not neccessarily need innerHTML.
 * * hideMainInput: (bool) true if main input field (for innerHTML) should be hidden.
 * * icon: (string) Name of icon for tool and inspector.
 * * toolGroup: (string) Name of the tool group the menu item should go to or '' if it should be
 * hidden in the menu.
 * * attributes: (array) Defines tag attribute objects.
 * * name: (string) Key of the attribute as used in the tag.
 * * labelMsg: (string) Message key for the attribute field label in inspector.
 * * helpMsg: (string) Message key for the attribute field help message in inspector.
 * * type: (string) Input field type. Possible values: dropdown, text, number, percent, tab
 * * default: (string) Default value of the attribute.
 * * options: (array) Only for dropdown. An array of items: [{data:'X', label:'X'}].
 * * placeholder: (string) Only for text: A placeholder text.
 * inspector: (object) Describes specific functional configurations for inspectors.
 * * methods: (object) Callback methods for various hooks. Can be overwritten to add more complex
 * behavior.
 * * createFields: Callback for creatFields method.
 * * setValues: Callback for setValues method.
 * * updateMwData: Callback for updateMwData method.
 */
ext.visualEditorPlus.ui.tag.Definition = function ( spec ) {
	this.paramDefinitions = spec.paramDefinitions || [];
	this.classname = spec.classname || '';
	this.name = spec.name || spec.tagname || '';
	this.tagname = spec.tagname || '';
	this.description = spec.description || '';
	this.menuMessage = spec.menuMessage || '';
	this.formSpecification = spec.formSpecification || null;
	this.rendersEmpty = typeof spec.rendersEmpty === 'undefined' ? true : spec.rendersEmpty;
	this.hideMainInput = typeof spec.hideMainInput === 'undefined' ? true : spec.hideMainInput;
	this.inspectorMethods = spec.inspectorMethods || {};
	this.toolGroup = spec.toolGroup || '';
	this.icon = spec.icon || 'bluespice';
};

OO.initClass( ext.visualEditorPlus.ui.tag.Definition );

ext.visualEditorPlus.ui.tag.Definition.prototype.initialize = function () {};

ext.visualEditorPlus.ui.tag.Definition.prototype.createForm = function ( inspector ) {
	const formSpec = this.formSpecification;
	if ( !formSpec ) {
		return $.Deferred().resolve( this.createGenericForm( this.paramDefinitions ) ).promise();
	}
	formSpec.inspector = inspector;
	if ( formSpec.type && formSpec.type === 'async' ) {
		return this.createAsyncForm( formSpec, inspector );
	}
	return $.Deferred().resolve( new mw.ext.forms.standalone.Form( formSpec ) ).promise();
};

ext.visualEditorPlus.ui.tag.Definition.prototype.createGenericForm = function ( attributes ) {
	const items = [];
	for ( const key in attributes ) {
		const attribute = attributes[ key ];
		if ( !attribute ) {
			continue;
		}
		// TODO: Types can be inferred from the attribute definition
		items.push( {
			type: 'text',
			name: key,
			label: key
		} );
	}

	return new mw.ext.forms.standalone.Form( {
		errorReporting: false,
		definition: {
			items: items,
			buttons: []
		}
	} );
};

ext.visualEditorPlus.ui.tag.Definition.prototype.createAsyncForm = function ( formSpec, inspector ) {
	function stringToCallback( str ) {
		if ( typeof str === 'function' ) {
			return str;
		}
		if ( typeof str !== 'string' ) {
			throw new Error( 'Invalid form class name' );
		}
		const parts = str.split( '.' );
		let namespace = window;
		for ( let i = 0; i < parts.length - 1; i++ ) {
			namespace = namespace[ parts[ i ] ];
			if ( !namespace ) {
				throw new Error( 'Invalid form class name' );
			}
		}
		return namespace[ parts[ parts.length - 1 ] ];
	}
	const deferred = $.Deferred();

	if ( formSpec.modules ) {
		mw.loader.using( formSpec.modules, () => {
			const formClass = stringToCallback( formSpec.formClass );
			deferred.resolve( new formClass( { // eslint-disable-line new-cap
				definition: this,
				inspector: inspector
			} ) );
		}, () => {
			console.error( 'Load of async module(s) failed:' + formSpec.modules.join( ',' ) ); // eslint-disable-line no-console
		} );
	} else {
		const formClass = stringToCallback( formSpec.formClass );
		deferred.resolve( new formClass( { // eslint-disable-line new-cap
			definition: this,
			inspector: inspector
		} ) );
	}
	return deferred.promise();
};

ext.visualEditorPlus.ui.tag.Definition.prototype.setValues = function ( inspector, attrs ) {
	if ( !inspector.inspectorForm ) {
		return;
	}
	attrs = this.modifyDataBeforeSetToInspector( attrs, inspector );
	inspector.value = {};
	const inputs = inspector.inspectorForm.form.getInputs();
	for ( const inputName in inputs ) {
		if ( inputName in attrs ) {
			const paramDefinition = this.paramDefinitions[ inputName ] || null;
			let value = attrs[ inputName ];
			if ( paramDefinition && paramDefinition.is_list ) {
				value = value.split( paramDefinition.separator || ',' );
			}
			if ( paramDefinition && paramDefinition.type === 'boolean' ) {
				const trueValues = [ 'true', '1', 'yes', 'on' ];
				if ( trueValues.indexOf( value ) !== -1 ) {
					value = true;
				} else {
					value = false;
				}
			}
			inputs[ inputName ].setValue( value );
			inspector.value[ inputName ] = value;

		}
		inputs[ inputName ].connect( this, {
			change: function () {
				inspector.inspectorForm.form.getData().done( ( data ) => {
					data = this.modifyDataBeforeSetToModel( data );
					inspector.value = data;
					inspector.onChangeHandler();
				} ).fail( ( error ) => {
					console.error( 'Error getting data from form', error ); // eslint-disable-line no-console
				} );
				inspector.updateSize();
			}
		} );
	}
};

ext.visualEditorPlus.ui.tag.Definition.prototype.getNewElement = function ( inspector, element ) {
	return element;
};

ext.visualEditorPlus.ui.tag.Definition.prototype.modifyDataBeforeSetToModel = function ( data ) {
	// This method can be overridden to modify data coming out of the inspector and being set to
	// VE data model
	return data;
};

ext.visualEditorPlus.ui.tag.Definition.prototype.modifyDataBeforeSetToInspector = function ( data, inspector ) { // eslint-disable-line no-unused-vars
	// This method can be overridden to modify the data before it is set in the inspector
	return data;
};

ext.visualEditorPlus.ui.tag.Definition.prototype.onTeardown = function ( data, inspector ) {
	if ( !inspector.inspectorForm ) {
		return;
	}
	const inputs = inspector.inspectorForm.form.getInputs();
	for ( const inputName in inputs ) {
		inputs[ inputName ].disconnect( this );
	}
};

ext.visualEditorPlus.ui.tag.Definition.prototype.updateMwData = function ( inspector, mwData ) {
	if ( !inspector.inspectorForm ) {
		return;
	}

	const currentValue = inspector.value;
	const params = this.paramDefinitions;
	for ( const param in params ) {
		let value = currentValue[ param ] || null;
		if ( params[ param ].type === 'boolean' ) {
			value = currentValue[ param ] ?? null;
		}
		if ( value === null ) {
			delete ( mwData.attrs[ param ] );
			continue;
		}
		mwData.attrs[ param ] = value;
		// If value is boolean convert to string
		if ( params[ param ].type === 'boolean' ) {
			mwData.attrs[ param ] = value ? 'true' : 'false';
		}
		if ( params[ param ].is_list && Array.isArray( value ) ) {
			mwData.attrs[ param ] = value.join( params[ param ].separator || ',' );
		}
	}
};
