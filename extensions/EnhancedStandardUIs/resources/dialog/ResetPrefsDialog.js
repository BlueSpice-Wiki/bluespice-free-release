ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.dialog = ext.enhancedUI.dialog || {};

ext.enhancedUI.dialog.ResetPrefsDialog = function ( cfg ) {
	cfg = cfg || {};
	ext.enhancedUI.dialog.ResetPrefsDialog.super.apply( this, cfg );

	this.$element.addClass( 'reset-prefs-dialog' );
};

OO.inheritClass( ext.enhancedUI.dialog.ResetPrefsDialog, OO.ui.ProcessDialog );

ext.enhancedUI.dialog.ResetPrefsDialog.static.name = 'reset-prefs-dialog';

ext.enhancedUI.dialog.ResetPrefsDialog.static.title = mw.message( 'enhanced-standard-uis-prefs-reset-dlg-title' ).text();

ext.enhancedUI.dialog.ResetPrefsDialog.static.size = 'medium';

ext.enhancedUI.dialog.ResetPrefsDialog.static.padded = true;

ext.enhancedUI.dialog.ResetPrefsDialog.static.scrollable = false;

ext.enhancedUI.dialog.ResetPrefsDialog.static.actions = [
	{
		title: mw.message( 'cancel' ).text(),
		icon: 'close',
		flags: 'safe'
	},
	{
		action: 'reset',
		label: mw.message( 'enhanced-standard-uis-prefs-reset-dlg-action-label' ).text(),
		flags: [ 'primary', 'destructive' ]
	}
];

ext.enhancedUI.dialog.ResetPrefsDialog.prototype.initialize = function () {
	ext.enhancedUI.dialog.ResetPrefsDialog.super.prototype.initialize.apply( this, arguments );

	const panelLayout = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	const labelWidget = new OO.ui.LabelWidget( {
		padded: true,
		label: mw.message( 'enhanced-standard-uis-prefs-reset-dlg-confirm-label' ).text()
	} );

	panelLayout.$element.append( labelWidget.$element );

	this.$body.append( panelLayout.$element );
	this.updateSize();
};

ext.enhancedUI.dialog.ResetPrefsDialog.prototype.getActionProcess = function ( action ) {
	const dialog = this;
	if ( action ) {
		dialog.pushPending();
		mw.loader.using( 'ext.enhancedstandarduis.api' ).done( () => {
			const api = new ext.enhancedUI.api.Api();
			api.resetPreferences().done( () => {
				dialog.close( { action: action } );
				dialog.emit( 'reset-done' );
			} ).fail( ( error ) => {
				dialog.popPending();
				dialog.showErrors( new OO.ui.Error( error.error[ 'error-msg' ], { recoverable: false } ) );
			} );
		} );
	}
	return ext.enhancedUI.dialog.ResetPrefsDialog.super.prototype.getActionProcess.call(
		this, action
	);
};
