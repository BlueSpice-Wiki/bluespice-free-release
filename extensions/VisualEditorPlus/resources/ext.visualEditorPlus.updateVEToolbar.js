( function ( ve ) {

	mw.hook( 've.activate' ).add( () => {
		// Search for insert group, add plus icon and remove label
		// ERM40066
		const groups = ve.init.mw.DesktopArticleTarget.static.toolbarGroups;

		for ( const i in groups ) {
			if ( groups[ i ].name === 'insert' ) {
				groups[ i ].icon = 'add';
				groups[ i ].label = '';
			}
			if ( groups[ i ].name === 'help' ) {
				groups[ i ].promote = [];
			}
		}

		const toolsToMove = [
			'mwFeedbackDialog',
			'commandHelp',
			'openHelpCompletionsTrigger',
			'mwUserGuide'
		];

		for ( const i in toolsToMove ) {
			const tool = ve.ui.toolFactory.lookup( toolsToMove[ i ] );
			tool.static.group = 'utility';
		}
		for ( const i in groups ) {
			if ( groups[ i ].name === 'pageMenu' ) {
				groups[ i ].demote = [ ...groups[ i ].demote, ...toolsToMove ];
			}
		}
	} );

}( ve ) );
