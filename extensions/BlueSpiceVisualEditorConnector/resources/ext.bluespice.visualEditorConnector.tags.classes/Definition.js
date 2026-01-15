bs.util.registerNamespace( 'bs.vec.util.tag' );

// bs.vec.util.TagDefinition
bs.vec.util.tag.Definition = function BsVecUtilTagDefinition() {};

OO.initClass( bs.vec.util.tag.Definition );

bs.vec.util.tag.Definition.prototype.initialize = function () {};

bs.vec.util.tag.Definition.prototype.createFields = function ( inspector, cfg ) {
	const attributes = cfg.attributes;
	for ( let i = 0; i < attributes.length; i++ ) {
		if ( attributes[ i ].type === 'tab' ) {
			continue;
		}
		inspector[ attributes[ i ].name + 'Input' ] =
			bs.vec.util.tag.Definition.prototype.createInputWidget( inspector, attributes[ i ] );

		const inspectorData = {
			align: 'left',
			$overlay: inspector.$body,
			label: ve.msg( attributes[ i ].labelMsg ) // eslint-disable-line mediawiki/msg-doc
		};
		if ( attributes[ i ].helpMsg ) {
			inspectorData.help = ve.msg( attributes[ i ].helpMsg ); // eslint-disable-line mediawiki/msg-doc
		}
		inspector[ attributes[ i ].name + 'Layout' ] = new OO.ui.FieldLayout(
			inspector[ attributes[ i ].name + 'Input' ], inspectorData
		);
		if ( cfg.tabbed === true ) {
			const tabName = attributes[ i ].tab;
			const tabClassName = 'tab' + tabName;
			inspector[ tabClassName ].$element.append(
				inspector[ attributes[ i ].name + 'Layout' ].$element
			);
		} else {
			inspector.indexLayout.$element.append(
				inspector[ attributes[ i ].name + 'Layout' ].$element
			);
		}
		if ( attributes[ i ].changeHandler ) {
			inspector[ attributes[ i ].name + 'Input' ].on( 'change', attributes[ i ].changeHandler );
		}
	}
};

bs.vec.util.tag.Definition.prototype.setValues = function ( inspector, attrs, cfg ) {
	const attributes = cfg.attributes;
	for ( let i = 0; i < attributes.length; i++ ) {
		const name = attributes[ i ].name;
		const inputName = name + 'Input';
		// If there are already attributes in the tag, save them to attributes.default. This is
		// needed for input fields with lazy loading. In this case, it can happen that during
		// loading, getValue returns no value. If so, updateMWdata will remove the attribute.
		// After lazy loading is complete, setValue falls back to attributes.default. By saving
		// the original value here, we can restore the original value from wikitext.
		if ( attrs[ name ] ) {
			attributes[ i ].default = attrs[ name ];
		}
		switch ( attributes[ i ].type ) {
			case 'tab':
				inspector.indexLayout.setTabPanel(
					attrs[ name ] || attributes[ i ].default
				);
				break;
			case 'toggle':
				inspector[ inputName ].setValue(
					!( ( attrs[ name ] == false || attrs[ name ] === 'false' ) ) // eslint-disable-line eqeqeq
				);
				break;
			case 'percent':
				inspector[ inputName ].setValue(
					attrs[ name ] ? attrs[ name ].replace( '%', '' ) : attributes[ i ].default.replace( '%', '' )
				);
				break;
			case 'multiselect':
				const val = attrs[ name ] ? attrs[ name ].split( attributes[ i ].valueSeparator || ',' ) : []; // eslint-disable-line no-case-declarations
				inspector[ inputName ].setValue(
					val.map( ( item ) => ( { data: item.trim(), label: '' } ) )
				);
				break;
			case 'groups':
				const groups = attrs[ name ] ? attrs[ name ].split( attributes[ i ].valueSeparator || ',' ) : []; // eslint-disable-line no-case-declarations
				inspector[ inputName ].setValue( groups );
				break;
			default:
				inspector[ inputName ].setValue(
					attrs[ name ] || attributes[ i ].default
				);
		}
		if ( attributes[ i ].type !== 'tab' ) {
			inspector[ inputName ].on( 'change', inspector.onChangeHandler );
		}
	}
};

bs.vec.util.tag.Definition.prototype.getNewElement = function ( inspector, element ) {
	return element;
};

bs.vec.util.tag.Definition.prototype.updateMwData = function ( inspector, mwData, cfg ) {
	const attributes = cfg.attributes;
	for ( let i = 0; i < attributes.length; i++ ) {
		if ( attributes[ i ].type === 'tab' ) {
			mwData.attrs[ attributes[ i ].name ] =
				inspector.indexLayout.getCurrentTabPanel().getData().value;
		} else if ( attributes[ i ].type === 'toggle' ) {
			mwData.attrs[ attributes[ i ].name ] =
				inspector[ attributes[ i ].name + 'Input' ].getValue() == false ? // eslint-disable-line eqeqeq
					'false' :
					'true';
		} else if ( attributes[ i ].type === 'multiselect' || attributes[ i ].type === 'groups' ) {
			const value = inspector[ attributes[ i ].name + 'Input' ].getValue();
			mwData.attrs[ attributes[ i ].name ] = value.join( attributes[ i ].valueSeparator || ',' );
		} else if ( inspector[ attributes[ i ].name + 'Input' ].getValue() ) {
			switch ( attributes[ i ].type ) {
				case 'percent':
					mwData.attrs[ attributes[ i ].name ] = inspector[ attributes[ i ].name + 'Input' ].getValue() + '%';
					break;
				default:
					mwData.attrs[ attributes[ i ].name ] = inspector[ attributes[ i ].name + 'Input' ].getValue();
			}
		} else {
			delete ( mwData.attrs[ attributes[ i ].name ] );
		}
	}
	return mwData;
};

bs.vec.util.tag.Definition.prototype.createInputWidget = function ( inspector, attribute ) {
	let widget;
	switch ( attribute.type ) {
		case 'tab':
			break;
		case 'custom':
			widget = new attribute.widgetClass( Object.assign( { // eslint-disable-line new-cap
				inspector: inspector,
				attribute: attribute,
				options: attribute.options || [],
				value: attribute.default || false
			}, attribute.widgetCfg || {} ) );
			break;
		case 'dropdown':
			widget = new OO.ui.DropdownInputWidget( {
				options: attribute.options,
				value: attribute.default
			} );
			break;
		case 'toggle':
			widget = new OO.ui.ToggleSwitchWidget( {
				value: attribute.default || false
			} );
			break;
		case 'number':
			widget = new OO.ui.NumberInputWidget( {
				min: attribute.min || -Infinity,
				max: attribute.max || Infinity,
				value: attribute.default || 0
			} );
			break;
		case 'percent':
			widget = new OO.ui.NumberInputWidget( {
				min: 0,
				max: 100,
				value: attribute.default || 0
			} );
			break;
		case 'title':
			widget = new mw.widgets.TitleInputWidget( {
				value: attribute.default || '',
				placeholder: attribute.placeholderMsg ? mw.msg( attribute.placeholderMsg ) : '' // eslint-disable-line mediawiki/msg-doc
			} );
			break;
		case 'multiselect':
			widget = new OO.ui.MenuTagMultiselectWidget( {
				options: attribute.options,
				$overlay: true,
				allowArbitrary: attribute.allowArbitrary,
				data: { valueSeparator: attribute.valueSeparator || ',' },
				value: attribute.default,
				placeholder: attribute.placeholderMsg ? mw.msg( attribute.placeholderMsg ) : '' // eslint-disable-line mediawiki/msg-doc
			} );
			break;
		case 'user':
			widget = new OOJSPlus.ui.widget.UserPickerWidget();
			break;
		case 'groups':
			widget = new OOJSPlus.ui.widget.GroupMultiSelectWidget( {
				$overlay: true,
				groupTypes: [ 'custom', 'core-minimal', 'extension-minimal' ]
			} );
			break;
		case 'text':
		default:
			widget = new OO.ui.TextInputWidget( {
				value: attribute.default || '',
				placeholder: attribute.placeholderMsg ? mw.msg( attribute.placeholderMsg ) : '' // eslint-disable-line mediawiki/msg-doc
			} );
	}
	return widget;
};

/**
 * Config object to define a tag and its attributes. Should be extended by sub class.
 * Params:
 * * classname: (string) Generic name for the classes to be created. Will be used in
 * classnameInspector, classnameNode, etc.
 * * name: (string) Internal name for the tag.
 * * tagname: (string) Name of the tag as used in wikicode.
 * * descriptionMsg: (string) Message key for the description section.
 * * menuItemMsg: (string) Message key for the tool menu item.
 * * rendersEmpty: (bool) true if the tag does not neccessarily need innerHTML.
 * * hideMainInput: (bool) true if main input field (for innerHTML) should be hidden.
 * * icon: (string) Name of icon for tool and inspector.
 * * toolGroup: (string) Name of the tool group the menu item should go to or '' if it should be
 * hidden in the menu.
 * * tabbed: (bool) true if the inspector should render attributes in tabs.
 * * tabs: (array) List of tab objects.
 * * name: (string) Internal name of the tab.
 * * labelMsg: (string) Message key for the tab label.
 * * value: (string) Value to use if tab selection is being used as a value for an tag attribute.
 * * attributes: (array) Defines tag attribute objects.
 * * name: (string) Key of the attribute as used in the tag.
 * * labelMsg: (string) Message key for the attribute field label in inspector.
 * * helpMsg: (string) Message key for the attribute field help message in inspector.
 * * type: (string) Input field type. Possible values: dropdown, text, number, percent, tab
 * * default: (string) Default value of the attribute.
 * * tab: (string) Name of the tab this attribute should be rendered to.
 * * options: (array) Only for dropdown. An array of items: [{data:'X', label:'X'}].
 * * placeholder: (string) Only for text: A placeholder text.
 * inspector: (object) Describes specific functional configurations for inspectors.
 * * methods: (object) Callback methods for various hooks. Can be overwritten to add more complex
 * behavior.
 * * createFields: Callback for creatFields method.
 * * setValues: Callback for setValues method.
 * * updateMwData: Callback for updateMwData method.
 *
 * @return {Object}
 */
bs.vec.util.tag.Definition.prototype.getCfg = function () {
	return {
		attributes: [],
		rendersEmpty: true,
		hideMainInput: true,
		tabbed: false,
		tabs: [ {
			name: 'common',
			labelMsg: 'bs-visualeditorconnector-inspector-tab-common-name'
		}, {
			name: 'advanced',
			labelMsg: 'bs-visualeditorconnector-inspector-tab-advanced-name'
		} ],
		inspector: {
			methods: {
				createFields: this.createFields,
				setValues: this.setValues,
				updateMwData: this.updateMwData,
				getNewElement: this.getNewElement
			}
		},
		toolGroup: '',
		icon: 'bluespice'
	};
};
