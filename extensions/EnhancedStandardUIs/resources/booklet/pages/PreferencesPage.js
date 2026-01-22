ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.booklet = ext.enhancedUI.booklet || {};

ext.enhancedUI.booklet.PreferencesPage = function ( name, cfg ) {
	ext.enhancedUI.booklet.PreferencesPage.parent.call( this, name, {
		padded: true,
		expanded: false
	} );
	this.label = cfg.label;
	this.sectionLabels = cfg.sectionLabels;
	this.prefs = cfg.prefs;
	this.isActive = cfg.active || false;
	this.prefInputs = {};
	this.mobile = cfg.mobile;
	this.icons = require( './icons.json' ).icons;
	this.$element = $( '<div>' ).addClass( 'enhanced-preferences-page-content' );
	this.setupPrefSection();
};

OO.inheritClass( ext.enhancedUI.booklet.PreferencesPage, OO.ui.PageLayout );

ext.enhancedUI.booklet.PreferencesPage.prototype.setupOutlineItem = function () {
	let iconName = 'settings';
	if ( this.icons[ this.name ] ) {
		iconName = this.icons[ this.name ];
	}
	this.outlineItem.setIcon( iconName );
	this.outlineItem.setLabel( this.label );
};

ext.enhancedUI.booklet.PreferencesPage.prototype.markActive = function () {
	if ( this.outlineItem ) {
		this.outlineItem.$element.addClass( 'enhanced-pref-active' );
		this.isActive = true;
	}
};

ext.enhancedUI.booklet.PreferencesPage.prototype.isActive = function () {
	return this.isActive;
};

ext.enhancedUI.booklet.PreferencesPage.prototype.resetActive = function () {
	this.outlineItem.$element.removeClass( 'enhanced-pref-active' );
	this.isActive = false;
};

ext.enhancedUI.booklet.PreferencesPage.prototype.setupPrefSection = function () {
	for ( const sectionName in this.sectionLabels ) {
		const $section = $( '<div>' ).addClass( 'enhanced-preferences-page-content-section' );
		$section.append( $( '<h2>' ).text( this.sectionLabels[ sectionName ].message ) );
		let emptySection = true;

		for ( const i in this.prefs[ sectionName ] ) {
			const pref = this.prefs[ sectionName ][ i ];

			if ( pref[ 'hide-if' ] ) {
				const comparison = pref[ 'hide-if' ][ 0 ];
				const variablePref = pref[ 'hide-if' ][ 1 ];
				const value = pref[ 'hide-if' ][ 2 ];

				const prefElement = this.findByKey( this.prefs, variablePref );
				if ( prefElement[ variablePref ] + comparison + value ) {
					continue;
				}
			}

			const prefInput = this.getPrefWidget( pref );
			if ( !prefInput ) {
				continue;
			}
			emptySection = false;
			const key = pref.pref;
			this.prefInputs[ key ] = prefInput;
			if ( pref.type === 'custom' ) {
				$section.append( prefInput.$element );
			} else {
				const infoField = new OO.ui.FieldLayout(
					prefInput,
					// eslint-disable-next-line mediawiki/class-doc
					{
						align: this.mobile ? 'top' : 'left',
						label: pref[ 'label-message' ] ? new OO.ui.HtmlSnippet( pref[ 'label-message' ] ) : '',
						help: pref[ 'help-message' ] ? new OO.ui.HtmlSnippet( pref[ 'help-message' ] ) : '',
						helpInline: true,
						classes: [ 'mw-pref-' + key ]
					} );
				$section.append( infoField.$element );
			}
		}
		if ( emptySection ) {
			continue;
		}
		this.$element.append( $section );
	}
};

ext.enhancedUI.booklet.PreferencesPage.prototype.findByKey = function ( obj, preference ) {
	if ( obj.pref && preference === obj.pref ) {
		return obj;
	}
	// eslint-disable-next-line es-x/no-object-values
	for ( const n of Object.values( obj ).filter( Boolean ).filter( ( v ) => typeof v === 'object' ) ) {
		const found = this.findByKey( n, preference );
		if ( found ) {
			return found;
		}
	}
};

ext.enhancedUI.booklet.PreferencesPage.prototype.onPrefValueChanged = function ( key, value ) {
	this.emit( 'prefChange', this, key, value );
};

ext.enhancedUI.booklet.PreferencesPage.prototype.getPrefWidget = function ( pref ) {
	let prefInput = new OO.ui.Widget();
	switch ( pref.type ) {
		case 'info':
			if ( pref.raw ) {
				prefInput = new OO.ui.Widget( {
					content: [ new OO.ui.HtmlSnippet( pref.default ) ]
				} );
			} else {
				prefInput = new OO.ui.LabelWidget( {
					label: pref.default
				} );
			}
			break;
		case 'check': {
			prefInput = new OO.ui.CheckboxInputWidget( {
				selected: pref.default
			} );
			prefInput.connect( this, {
				change: ( value ) => {
					this.onPrefValueChanged( pref.pref, value );
				}
			} );
			break;
		}
		case 'text': {
			prefInput = new OO.ui.TextInputWidget( {
				value: pref.default
			} );
			prefInput.connect( this, {
				change: ( value ) => {
					this.onPrefValueChanged( pref.pref, value );
				}
			} );
			break;
		}
		case 'toggle': {
			if ( pref.disabled ) {
				return false;
			}
			prefInput = new OO.ui.ToggleSwitchWidget( {
				value: pref.default
			} );
			prefInput.connect( this, {
				change: ( value ) => {
					this.onPrefValueChanged( pref.pref, value );
				}
			} );
			break;
		}
		case 'select': {
			const options = [];
			if ( pref.options ) {
				for ( const selectOpt in pref.options ) {
					options.push( {
						data: pref.options[ selectOpt ],
						label: selectOpt
					} );
				}
			}
			if ( options.length <= 1 ) {
				return false;
			}
			if ( pref[ 'options-messages' ] ) {
				for ( const selectOptMsg in pref[ 'options-messages' ] ) {
					options.push( {
						data: pref[ 'options-messages' ][ selectOptMsg ],
						label: new OO.ui.HtmlSnippet( selectOptMsg ) } );
				}
			}
			prefInput = new OO.ui.DropdownInputWidget( {
				options: options,
				value: pref.default,
				$overlay: true
			} );
			prefInput.connect( this, {
				change: ( value ) => {
					this.onPrefValueChanged( pref.pref, value );
				}
			} );
			break;
		}
		case 'radio': {
			const options = [];
			if ( pref.options ) {
				for ( const radioOption in pref.options ) {
					options.push( new OO.ui.RadioOptionWidget( {
						data: pref.options[ radioOption ],
						label: new OO.ui.HtmlSnippet( radioOption ) } ) );
				}
			}
			if ( pref[ 'options-messages' ] ) {
				for ( const radioOptionMsg in pref[ 'options-messages' ] ) {
					options.push( new OO.ui.RadioOptionWidget( {
						data: pref[ 'options-messages' ][ radioOptionMsg ],
						label: new OO.ui.HtmlSnippet( radioOptionMsg ) } ) );
				}
			}
			if ( options.length <= 1 ) {
				return false;
			}
			prefInput = new OO.ui.RadioSelectWidget( {
				items: options
			} );
			prefInput.selectItemByData( pref.default );
			prefInput.connect( this, {
				select: ( widget ) => {
					this.onPrefValueChanged( pref.pref, widget.getData() );
				}
			} );
			break;
		}
		case 'int':
		case 'float': {
			const cfg = {
				input: { value: pref.default },
				min: pref.min || 0
			};
			if ( pref.max ) {
				cfg.max = pref.max;
			}
			prefInput = new OOJSPlus.ui.widget.NumberInputWidget( cfg );
			prefInput.connect( this, {
				change: ( value ) => {
					this.onPrefValueChanged( pref.pref, value );
				}
			} );
			break;
		}
		case 'timezone': {
			const options = [];
			if ( pref.options ) {
				for ( const area in pref.options ) {
					for ( const timeZone in pref.options[ area ] ) {
						options.push( {
							data: pref.options[ area ][ timeZone ],
							label: timeZone
						} );
					}
				}
			}
			prefInput = new OOJSPlus.ui.widget.FilterableComboBoxWidget( {
				options: options,
				$overlay: true,
				allowArbitrary: false,
				menu: {
					filterMode: 'substring',
					filterFromInput: true
				}
			} );
			prefInput.setValueByData( pref.default );
			prefInput.getMenu().connect( this, {
				select: ( value ) => {
					if ( !value ) {
						return;
					}
					const data = value.getData();
					if ( pref.default === data ) {
						return;
					}
					pref.default = data;
					this.updateLocalTime( data );
					this.onPrefValueChanged( pref.pref, data );
				}
			} );
			break;
		}
		case 'multiselect': {
			const options = [];
			if ( pref.options ) {
				for ( const option in pref.options ) {
					options.push( { data: pref.options[ option ], label: option } );
				}
			}
			if ( pref[ 'options-messages' ] ) {
				for ( const optionMsg in pref[ 'options-messages' ] ) {
					options.push( {
						data: pref[ 'options-messages' ][ optionMsg ],
						label: new OO.ui.HtmlSnippet( optionMsg )
					} );
				}
			}
			const selectedOptions = [];
			if ( pref.default ) {
				for ( const select in pref.default ) {
					const selectedEl = options.find( ( option ) => option.data === pref.default[ select ] );
					if ( selectedEl ) {
						selectedOptions.push( selectedEl );
					}
				}
			}

			prefInput = new OO.ui.MenuTagMultiselectWidget( {
				options: options,
				$overlay: true,
				inputPosition: 'outline',
				selected: selectedOptions
			} );
			prefInput.connect( this, {
				remove: ( item ) => {
					const prefKey = pref.pref + item.getData();
					this.onPrefValueChanged( prefKey, false );
				},
				add: ( item ) => {
					const prefKey = pref.pref + item.getData();
					this.onPrefValueChanged( prefKey, true );
				}
			} );
			break;
		}
		case 'custom': {
			const customCfg = pref.content;
			const classname = this.callbackFromString( customCfg.classname );
			// eslint-disable-next-line new-cap
			const customPrefInput = new classname( customCfg.cfg );

			customPrefInput.connect( this, {
				change: function ( value ) {
					this.onPrefValueChanged( pref.pref, JSON.stringify( value ) );
				}
			} );
			prefInput = customPrefInput;
			break;
		}
	}
	return prefInput;
};

ext.enhancedUI.booklet.PreferencesPage.prototype.callbackFromString = function ( callback ) {
	const parts = callback.split( '.' );
	let func = window[ parts[ 0 ] ];
	for ( let i = 1; i < parts.length; i++ ) {
		func = func[ parts[ i ] ];
	}

	return func;
};

ext.enhancedUI.booklet.PreferencesPage.prototype.isEmpty = function () {
	if ( this.$element[ 0 ].childNodes.length < 1 ) {
		return true;
	}
	return false;
};

ext.enhancedUI.booklet.PreferencesPage.prototype.updateLocalTime = function ( type ) {
	// eslint-disable-next-line no-jquery/no-global-selector
	const $localTimeHolder = $( '#wpLocalTime' );
	// eslint-disable-next-line no-jquery/no-global-selector
	const servertime = parseInt( $( 'input[name="wpServerTime"]' ).val(), 10 );
	const minuteDiff = parseInt( type.split( '|' )[ 1 ], 10 ) || 0;

	let localTime = servertime + minuteDiff;

	// Bring time within the [0,1440) range.
	localTime = ( ( localTime % 1440 ) + 1440 ) % 1440;

	function minutesToHours( min ) {
		const tzHour = Math.floor( Math.abs( min ) / 60 ),
			tzMin = Math.abs( min ) % 60,
			tzString = ( ( min >= 0 ) ? '' : '-' ) + ( ( tzHour < 10 ) ? '0' : '' ) + tzHour +
				':' + ( ( tzMin < 10 ) ? '0' : '' ) + tzMin;
		return tzString;
	}

	$localTimeHolder.text( mw.language.convertNumber( minutesToHours( localTime ) ) );
};
