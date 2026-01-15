$( () => {

	mw.hook( 'wikiEditor.toolbarReady' ).add( ( $textarea ) => {
		$textarea.wikiEditor( 'addToToolbar', {
			section: 'secondary',
			group: 'default',
			tools: {
				mailTemplateHelp: {
					type: 'element',
					element: function ( context ) {
						return new ext.notifyme.ui.widget.MailEditingHelp(
							mw.config.get( 'wgNotificationsMailTemplateHelp' ), {
								$textarea: context.$textarea
							}
						).$element;
					}
				}
			}
		}
		);
	} );

} );
