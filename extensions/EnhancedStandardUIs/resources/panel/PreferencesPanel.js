ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.panel = ext.enhancedUI.panel || {};

require( './../booklet/pages/PreferencesPage.js' );
require( './../dialog/ResetPrefsDialog.js' );

ext.enhancedUI.panel.PreferencesPanel = function ( cfg ) {
	cfg = cfg || {};
	ext.enhancedUI.panel.PreferencesPanel.super.apply( this, cfg );
	this.$element = $( '<div>' ).addClass( 'enhanced-ui-preferences-panel' );
	this.$content = $( '<div>' ).addClass( 'enhanced-ui-preferences-content' );

	this.sections = cfg.sections || [];
	this.preferences = cfg.preferences || [];
	this.changedPrefs = {};
	this.activePages = [];
	this.mobile = cfg.mobile || false;
	this.$element.append( this.$content );

	mw.hook( 'enhanced-standard-preferences-setup-sections' ).fire( this, this.preferences );

	if ( $( document ).find( '#oojsplus-skeleton-cnt' ) ) {
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#oojsplus-skeleton-cnt' ).empty();
	}
	this.setup();
};

OO.inheritClass( ext.enhancedUI.panel.PreferencesPanel, OO.ui.PanelLayout );

ext.enhancedUI.panel.PreferencesPanel.prototype.setup = function () {
	$( this.$content ).empty();

	this.setupToolbar();
	this.setupSearch();

	this.bookletLayout = new OOJSPlus.ui.booklet.Booklet( {
		outlined: !this.mobile,
		expanded: false
	} );
	this.bookletLayout.connect( this, {
		set: ( page ) => {
			location.hash = 'mw-prefsection-' + page.getName();
		}
	} );

	if ( this.mobile ) {
		this.mobileSetup();
	} else {
		this.bookletLayout.outlineSelectWidget.$element.attr( 'aria-label',
			mw.message( 'enhanced-standard-uis-prefs-menu-aria-label' ).text()
		);
	}

	this.createPages();

	this.$content.append( this.bookletLayout.$element );
	if ( location.hash ) {
		const section = location.hash.replace( '#mw-prefsection-', '' );
		this.bookletLayout.setPage( section );
		if ( this.mobile ) {
			this.pageDropdown.getMenu().selectItemByData( section );
		}
	} else {
		this.bookletLayout.selectFirstSelectablePage();
		if ( this.mobile ) {
			const firstItem = this.pageDropdown.getMenu().findFirstSelectableItem();
			this.pageDropdown.getMenu().selectItem( [ firstItem ] );
		}
	}
};

ext.enhancedUI.panel.PreferencesPanel.prototype.createPages = function () {
	this.bookletLayout.clearPages();
	this.prefPages = [];
	for ( const section in this.sections ) {
		const prefPage = new ext.enhancedUI.booklet.PreferencesPage(
			section,
			{
				label: this.sections[ section ].message,
				sectionLabels: this.sections[ section ].sections,
				mobile: this.mobile,
				prefs: this.preferences[ section ]
			}
		);
		if ( prefPage.$element[ 0 ].children.length === 0 ) {
			continue;
		}
		// eslint-disable-next-line es-x/no-array-prototype-includes
		if ( this.activePages.includes( prefPage ) ) {
			prefPage.markActive();
		}
		prefPage.connect( this, {
			prefChange: 'addPrefForSave'
		} );
		this.prefPages.push( prefPage );
	}
	this.bookletLayout.addPages( this.prefPages );
};

ext.enhancedUI.panel.PreferencesPanel.prototype.setupToolbar = function () {
	this.toolbar = new OOJSPlus.ui.toolbar.ManagerToolbar( {
		saveable: true,
		sticky: true,
		actions: [
			new OOJSPlus.ui.toolbar.tool.ToolbarTool( {
				name: 'resetPrefs',
				icon: 'reload',
				displayBothIconAndLabel: true,
				title: mw.message( 'enhanced-standard-uis-prefs-reset-button-label' ).text(),
				callback: ( toolInstance ) => {
					const windowManager = new OO.ui.WindowManager();
					$( document.body ).append( windowManager.$element );
					const resetDialog = new ext.enhancedUI.dialog.ResetPrefsDialog();
					resetDialog.connect( this, {
						'reset-done': () => {
							mw.notify( mw.message( 'enhanced-standard-uis-prefs-reset-notify' ).text() );
							window.location.reload();
						}
					} );
					windowManager.addWindows( [ resetDialog ] );
					windowManager.openWindow( resetDialog );
					toolInstance.setActive( false );
				}
			} )
		]
	} );
	this.toolbar.setup();
	this.toolbar.initialize();
	this.saveTool = this.toolbar.getTool( 'save' );
	this.saveTool.setDisabled( true );

	this.toolbar.connect( this, {
		save: () => {
			mw.loader.using( 'ext.enhancedstandarduis.api' ).done( () => {
				const api = new ext.enhancedUI.api.Api();
				api.savePreferences( this.changedPrefs ).done( () => {
					this.deactivateLeaveWarning();
					mw.notify( mw.message( 'savedprefs' ).text() );
					window.location.reload();
				} );
			} );
		}
	} );

	this.$content.append( this.toolbar.$element );
};

ext.enhancedUI.panel.PreferencesPanel.prototype.setupSearch = function () {
	this.search = new OO.ui.SearchInputWidget();
	this.search.connect( this, {
		change: 'searchForValue'
	} );

	this.$content.append(
		new OO.ui.FieldLayout( this.search, {
			align: this.mobile ? 'top' : 'left',
			label: mw.message( 'enhanced-standard-uis-prefs-panel-search-label' ).text(),
			classes: [ 'enhanced-ui-preferences-search' ]
		} ).$element
	);
};

ext.enhancedUI.panel.PreferencesPanel.prototype.mobileSetup = function () {
	const mobileDropdownOptions = [];
	for ( const section in this.sections ) {
		mobileDropdownOptions.push( new OO.ui.MenuOptionWidget( {
			data: section,
			label: this.sections[ section ].message
		} ) );
	}
	this.pageDropdown = new OO.ui.DropdownWidget( {
		menu: {
			items: mobileDropdownOptions
		}
	} );
	this.pageDropdown.getMenu().connect( this, {
		select: ( option ) => {
			this.bookletLayout.setPage( option.getData() );
		}
	} );

	const mobileDropdownField = new OO.ui.FieldLayout( this.pageDropdown, {
		label: mw.message( 'enhanced-standard-uis-prefs-menu-aria-label' ).text(),
		align: 'top',
		padded: true,
		classes: [ 'enhanced-ui-preferences-mobile-pref-dropdown' ]
	} );
	this.$content.append( mobileDropdownField.$element );
};

ext.enhancedUI.panel.PreferencesPanel.prototype.addPrefForSave = function ( page, pref, value ) {
	this.changedPrefs[ pref ] = value;
	// eslint-disable-next-line es-x/no-array-prototype-includes
	if ( !this.activePages.includes( page ) ) {
		this.activePages.push( page );
		page.markActive();
	}
	if ( this.saveTool.isDisabled() ) {
		this.saveTool.setDisabled( false );
		this.activateLeaveWarning();
	}
};

ext.enhancedUI.panel.PreferencesPanel.prototype.activateLeaveWarning = function () {
	this.allowCloseWindow = mw.confirmCloseWindow();
};

ext.enhancedUI.panel.PreferencesPanel.prototype.deactivateLeaveWarning = function () {
	this.allowCloseWindow.release();
};

ext.enhancedUI.panel.PreferencesPanel.prototype.searchForValue = function ( searchTerm ) {
	if ( searchTerm.length < 1 ) {
		this.createPages();
		this.bookletLayout.selectFirstSelectablePage();
		this.search.focus();
		return;
	}
	this.searchedPrefs = {};
	for ( const i in this.prefPages ) {
		const page = this.prefPages[ i ];
		let resultKey = null;
		// eslint-disable-next-line es-x/no-object-entries
		for ( const [ key, value ] of Object.entries( page.sectionLabels ) ) {
			if ( value && typeof value.message === 'string' &&
				// eslint-disable-next-line es-x/no-array-prototype-includes
				value.message.toLowerCase().includes( searchTerm.toLowerCase() )
			) {
				resultKey = key;
				break;
			}
		}
		if ( resultKey ) {
			this.searchedPrefs[ page.name ] = {
				[ resultKey ]: page.prefs[ resultKey ]
			};
		}
		const lowerSearch = searchTerm.toLowerCase();
		// eslint-disable-next-line no-unused-vars, es-x/no-object-entries
		const resultKeys = Object.entries( page.prefs ).filter( ( [ sectionKey, entries ] ) => {
			if ( !Array.isArray( entries ) ) {
				return false;
			}
			return entries.some( ( entry ) => {
				let labelMatch = false;

				if ( entry && entry[ 'label-message' ] && typeof entry[ 'label-message' ] === 'string' ) {
					// eslint-disable-next-line es-x/no-array-prototype-includes
					labelMatch = entry[ 'label-message' ].toLowerCase().includes( lowerSearch );
				}

				// eslint-disable-next-line no-prototype-builtins
				const defaultMatch = entry.hasOwnProperty( 'default' ) &&
				// eslint-disable-next-line es-x/no-array-prototype-includes
					String( entry.default ).toLowerCase().includes( lowerSearch );

				const optionKeyMatch = Object.keys( entry.options || {} ).some(
					// eslint-disable-next-line es-x/no-array-prototype-includes
					( optKey ) => optKey.toLowerCase().includes( lowerSearch )
				);

				// eslint-disable-next-line es-x/no-object-values
				const optionValueMatch = Object.values( entry.options || {} ).some(
					// eslint-disable-next-line es-x/no-array-prototype-includes
					( optVal ) => typeof optVal === 'string' && optVal.toLowerCase().includes( lowerSearch )
				);

				return labelMatch || defaultMatch || optionKeyMatch || optionValueMatch;
			} );
		} ).map( ( [ key ] ) => key );
		if ( resultKeys.length > 0 ) {
			for ( const key in resultKeys ) {
				const sectionKey = resultKeys[ key ];
				this.searchedPrefs[ page.name ] = {
					[ sectionKey ]: page.prefs[ sectionKey ]
				};
			}
		}
	}
	this.filterPrefs();
	this.search.focus();
};

ext.enhancedUI.panel.PreferencesPanel.prototype.filterPrefs = function () {
	this.bookletLayout.clearPages();
	const filteredprefPages = [];
	for ( const section in this.searchedPrefs ) {
		const prefPage = new ext.enhancedUI.booklet.PreferencesPage(
			section,
			{
				label: this.sections[ section ].message,
				sectionLabels: this.sections[ section ].sections,
				mobile: this.mobile,
				prefs: this.searchedPrefs[ section ]
			}
		);
		// eslint-disable-next-line es-x/no-array-prototype-includes
		if ( this.activePages.includes( prefPage ) ) {
			prefPage.markActive();
		}
		prefPage.connect( this, {
			prefChange: 'addPrefForSave'
		} );

		if ( prefPage.isEmpty() ) {
			continue;
		}
		filteredprefPages.push( prefPage );
	}
	this.bookletLayout.addPages( filteredprefPages );
	this.bookletLayout.selectFirstSelectablePage();
};
