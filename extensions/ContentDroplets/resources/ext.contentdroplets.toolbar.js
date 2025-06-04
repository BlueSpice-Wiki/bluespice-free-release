ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'contentdroplet-toolbar', 'window', 'toggle',
		{ args: [ 'contentdroplet-dialog' ], supportedSelections: [ 'linear' ] }
	)
);

ve.ui.ContentDropletTool = function VeUiContentDropletTool() {
	ve.ui.ContentDropletTool.super.apply( this, arguments );
};
OO.inheritClass( ve.ui.ContentDropletTool, ve.ui.Tool );
ve.ui.ContentDropletTool.static.name = 'contentdroplet-toolbar';
ve.ui.ContentDropletTool.static.group = 'dialog';
ve.ui.ContentDropletTool.static.title = mw.message( 'contentdroplets-toolbar-icon-title' ).text();
ve.ui.ContentDropletTool.static.icon = 'contentDroplet';
ve.ui.ContentDropletTool.static.autoAddToCatchall = false;
ve.ui.ContentDropletTool.static.autoAddToGroup = false;
ve.ui.ContentDropletTool.static.commandName = 'contentdroplet-toolbar';

ve.ui.toolFactory.register( ve.ui.ContentDropletTool );

ve.init.mw.Target.static.toolbarGroups.push( {
	include: [ 'contentdroplet-toolbar' ],
	classes: [ 've-ui-toolbar-group-content-droplets' ]
} );

/**
 * command for sequence registry to open dialog from text
 */
ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'dropletFromSequence', 'window', 'toggle',
		{ args: [ 'contentdroplet-dialog' ], supportedSelections: [ 'linear' ] }
	)
);

ve.ui.sequenceRegistry.register(
	new ve.ui.Sequence( 'contentDroplet', 'dropletFromSequence', '<<', 2 )
);

ve.ui.commandHelpRegistry.register( 'insert', 'template', {
	sequences: [ 'contentDroplet' ],
	label: OO.ui.deferMsg( 'contentdroplet-droplets-dialog-title' )
} );

// Load full droplet data
if ( mw.loader.getState( 'ext.bluespice.visualEditorConnector.tags' ) === 'registered' ) {
	// If BlueSpiceVisualEditorConnector is enabled (and not yet loaded), load it first
	mw.loader.using( 'ext.bluespice.visualEditorConnector.tags', () => {
		ext.contentdroplets.getDroplets();
	} );
} else {
	ext.contentdroplets.getDroplets();
}
