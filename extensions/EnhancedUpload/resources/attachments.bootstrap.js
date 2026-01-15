( function ( mw, $ ) {
	$( () => {
		// eslint-disable-next-line no-jquery/no-global-selector
		const $loader = $( '.attachments-loader' );

		const $attachments = $(
			'div.attachments-filelist[data-frametitle="' + mw.config.get( 'wgArticleId', 0 ) + '"]'
		);
		if ( $attachments.length < 1 ) {
			return;
		}

		function setLoading( select, loading ) {
			if ( loading ) {
				const progress = new OO.ui.ProgressBarWidget( {
					progress: false
				} );
				$( $loader[ select ] ).html(
					progress.$element
				);
			} else {
				$( $loader[ select ] ).children().remove();
				$( $attachments[ select ] ).removeClass( 'loading' );
			}
		}

		function getEditRight() {
			const dfd = $.Deferred();

			mw.user.getRights().done( ( rights ) => {
				if ( rights.indexOf( 'edit' ) !== -1 ) {
					dfd.resolve( true );
				} else {
					dfd.resolve( false );
				}
			} );
			return dfd.promise();
		}

		for ( let i = 0; i < $loader.length; i++ ) {
			setLoading( i, true );
		}

		getEditRight().done( ( editRight ) => {
			mw.loader.using( 'ext.enhancedupload.attachments' ).done( function () {
				let files, counter, tagTitle, $el, j, attachments, tagCategories, tagPrefix,
					tagDescription, tagSkipOptions, tagHideVersion, tagHideEditor, tagShowSize,
					tagShowCategories;
				for ( j = 0; j < $attachments.length; j++ ) {
					files = $( $attachments[ j ] ).data( 'files' );
					counter = $( $attachments[ j ] ).data( 'no' );
					tagTitle = $( $attachments[ j ] ).data( 'title' );
					tagCategories = $( $attachments[ j ] ).data( 'categories' );
					tagPrefix = $( $attachments[ j ] ).data( 'prefix' );
					tagDescription = $( $attachments[ j ] ).data( 'description' );
					tagSkipOptions = $( $attachments[ j ] ).data( 'skip-options' );
					tagHideVersion = $( $attachments[ j ] ).data( 'hideversion' );
					tagHideEditor = $( $attachments[ j ] ).data( 'hideeditor' );
					tagShowSize = $( $attachments[ j ] ).data( 'showsize' );
					tagShowCategories = $( $attachments[ j ] ).data( 'showcategories' );

					$el = $( '<div>' );
					if ( !editRight ) {
						$( $el ).addClass( 'no-editing' );
					}
					$( $attachments[ j ] ).append( $el );

					attachments = new enhancedUpload.ui.AttachmentsWidget( {
						files: files,
						pageID: mw.config.get( 'wgArticleId', 0 ),
						counter: counter,
						title: tagTitle,
						edit: editRight,
						defaultCategories: tagCategories,
						defaultPrefix: tagPrefix,
						defaultDescription: tagDescription,
						skipOptions: tagSkipOptions,
						hideVersion: tagHideVersion,
						hideEditor: tagHideEditor,
						showSize: tagShowSize,
						showCategories: tagShowCategories
					} );
					$el.append( attachments.$element );

					if ( !files.length ) {
						setLoading( counter, false );
					}
					attachments.connect( this, {
						loaded: function ( count ) {
							setLoading( count, false );
						}
					} );
					// Avoid racing condition, make sure to call methods that fire events
					// only after even handlers are attached. In this case, `loaded` event
					attachments.init();
				}
			} );
		} );

	} );

}( mediaWiki, jQuery ) );
