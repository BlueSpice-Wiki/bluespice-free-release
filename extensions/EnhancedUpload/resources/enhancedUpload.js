$( () => {
	let singleUpload = false;
	let destFilename = '';
	let categories = null;
	let description = null;

	const initUploadWidget = () => {
		const widgetConfig = {
			singleUpload: singleUpload,
			destFilename: destFilename
		};

		if ( categories !== null ) {
			widgetConfig.categories = categories;
		}
		if ( description !== null ) {
			widgetConfig.description = description;
		}

		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#enhancedUpload-container' ).append(
			new enhancedUpload.ui.UploadWidget( widgetConfig ).$element
		);
		if ( $( document ).find( '#enhancedUpload-skeleton-cnt' ) ) {
			// eslint-disable-next-line no-jquery/no-global-selector
			$( '#enhancedUpload-skeleton-cnt' ).empty();
		}

		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#enhancedUpload-container' ).addClass( 'enhancedUpload-widget' );
	};

	if ( mw.util.getParamValue( 'wpDestFile' ) ) {
		singleUpload = true;
		destFilename = mw.util.getParamValue( 'wpDestFile' );

		// Fetch page contents for the destination file
		new mw.Api().get( {
			action: 'query',
			titles: 'File:' + destFilename,
			prop: 'revisions',
			rvprop: 'content',
			rvslots: 'main',
			formatversion: 2
		} ).done( ( data ) => {
			if ( data.query && data.query.pages && data.query.pages.length > 0 ) {
				const page = data.query.pages[ 0 ];
				if ( page.revisions && page.revisions.length > 0 ) {
					const content = page.revisions[ 0 ].slots.main.content;

					// Get the localized category namespace name
					const categoryNamespace = mw.config.get( 'wgNamespaceIds' ).category;
					const categoryPrefix = mw.config.get( 'wgFormattedNamespaces' )[ categoryNamespace ];

					// Extract categories from content using the correct localized keyword
					const categoryRegex = new RegExp( '\\[\\[(?:' + categoryPrefix + '|Category):([^\\]]+)\\]\\]', 'gi' );
					const foundCategories = [];
					let match;
					while ( ( match = categoryRegex.exec( content ) ) !== null ) {
						foundCategories.push( match[ 1 ].trim() );
					}

					if ( foundCategories.length > 0 ) {
						categories = foundCategories.join( '|' );
					}

					// Extract description (content without category links)
					const descriptionRegex = new RegExp( '\\[\\[(?:' + categoryPrefix + '|Category):[^\\]]+\\]\\]', 'gi' );
					const descriptionText = content.replace( descriptionRegex, '' ).trim();
					if ( descriptionText ) {
						description = descriptionText;
					}
				}
			}
			initUploadWidget();
		} ).fail( () => {
			initUploadWidget();
		} );
	} else {
		initUploadWidget();
	}
} );
