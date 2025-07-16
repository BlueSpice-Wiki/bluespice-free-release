( function ( mw, $, bs ) {
	const _instances = {}; // eslint-disable-line no-underscore-dangle

	function createEditor( id, cfg ) {
		cfg.floatable = cfg.floatable || false;
		cfg.format = cfg.format || 'wikitext';
		cfg.value = cfg.value || '';

		const dfd = $.Deferred();
		if ( cfg.format === 'html' ) {
			_makeEditor( id, cfg ).done( ( target ) => {
				dfd.resolve( target );
			} );
		} else if ( cfg.format === 'wikitext' ) {
			_transformToHtml( cfg.value ).done( ( html ) => {
				cfg.value = html;
				_makeEditor( id, cfg ).done( ( target ) => {
					dfd.resolve( target );
				} );
			} );
		}

		return dfd.promise();
	}

	function getInstance( id ) {
		return _instances[ id ];
	}

	function _makeEditor( id, cfg ) { // eslint-disable-line no-underscore-dangle
		const dfd = $.Deferred();

		ve.init.platform.initialize().done( () => {
			const target = new ve.init.sa.BlueSpiceTarget();
			target.addSurface(
				ve.dm.converter.getModelFromDom(
					ve.createDocumentFromHtml( cfg.value )
				)
			);
			// Editor toolbar should not float when there are more than one instances
			target.toolbar.floatable = cfg.floatable;

			// save for future reference
			_instances[ id ] = target;

			// mysteriously, parent is needed here to actually render to the
			// element, otherwise the toolbar finds no element to attach to,
			// issueing the error message "jQuery.fn.offset() requires an
			// element connected to a document"
			$( cfg.renderTo ).parent().append( target.$element );

			target.getHtml = function () {
				const dfd = $.Deferred(); // eslint-disable-line no-shadow
				const value = this.getSurface().getHtml();
				dfd.resolve( value );
				return dfd.promise();
			};

			target.getWikiText = function () {
				const dfd = $.Deferred(); // eslint-disable-line no-shadow
				const value = this.getSurface().getHtml();
				_transformToWikiText( value ).done( ( data ) => {
					dfd.resolve( data );
				} );
				return dfd.promise();
			};

			// some extensions, e.g. PageForms, do not support asynchronous
			// calls. Hence, we need to provide a synchronous function here.
			target.getWikiTextSync = function () {
				const textToSubmit = this.getSurface().getHtml();
				let result = false;
				// Remove trailing slash
				const origin = window.location.origin.replace( /\/$/, '' );
				// Remove leading slash
				const apiPath = mw.util.wikiScript( 'api' ).replace( /^\//, '' );

				$.ajax( {
					type: 'POST',
					url: [ origin, '/', apiPath ].join( '' ),
					data: {
						action: 'visualeditoredit',
						page: 'NEWVISUALEDITORCONTEXT',
						paction: 'serialize',
						html: textToSubmit,
						token: mw.user.tokens.get( 'csrfToken' ),
						format: 'json'
					},
					dataType: 'json',
					success: function ( response ) {
						result = response.visualeditoredit.content;
					},
					async: false
				} );

				return result;
			};

			dfd.resolve( target );
		} );

		return dfd.promise();
	}

	function _transformToWikiText( fromHtml ) { // eslint-disable-line no-underscore-dangle
		return _transform( 'html', fromHtml );
	}

	function _transformToHtml( fromWikiText ) { // eslint-disable-line no-underscore-dangle
		const dfd = $.Deferred();
		_transform( 'wikitext', fromWikiText ).done( ( html ) => {
			// Remove legacy returned IDs
			// leads to not editable elements in UI
			// ERM41142
			const cleaned = html.replace( /<span[^>]*typeof=["']mw:FallbackId["'][^>]*><\/span>/g, '' );

			dfd.resolve( cleaned );
		} );

		return dfd.promise();
	}

	function _transform( from, content ) { // eslint-disable-line no-underscore-dangle
		const dfd = $.Deferred(),
			api = new mw.Api(),
			data = {
				action: from === 'wikitext' ? 'visualeditor' : 'visualeditoredit',
				page: 'NEWVISUALEDITORCONTEXT',
				paction: from === 'wikitext' ? 'parsefragment' : 'serialize',
				format: 'json'
			};

		content = content || '';
		data[ from ] = content;

		api.postWithToken( 'csrf', data )
			.done( ( response ) => {
				const contentObj = from === 'wikitext' ? response.visualeditor : response.visualeditoredit;
				dfd.resolve( contentObj.content );
			} )
			.fail( () => {
				dfd.reject();
			} );

		return dfd.promise();
	}

	/**
	 * HINT:https://www.mediawiki.org/w/index.php?title=VisualEditor/Gadgets&oldid=2776161#Checking_if_VisualEditor_is_in_regular_'visual'_mode_or_'source'_mode
	 *
	 * @param {ve.init.Target} target
	 * @return {Array}
	 */
	function getCategoriesFromTarget( target ) {
		let model;
		let document;
		let metadata;

		const surface = target.getSurface();

		if ( surface.getMode() === 'visual' ) {
			model = surface.getModel();
			document = model.getDocument();
			metadata = document.getMetadata();
			return getCategoriesFromMetadata( metadata );
		} else if ( surface.getMode() === 'source' ) {
			return getCategoriesFromHtml( surface.getHtml() );
		}
		return [];
	}

	/**
	 * @param {ve.dm.MetaItem[]} metadata
	 * @return {Array}
	 */
	function getCategoriesFromMetadata( metadata ) {
		let i;
		let item;
		let prefixedCategory;
		let categoryParts;

		const categories = [];
		for ( i = 0; i < metadata.length; i++ ) {
			item = metadata[ i ];
			if ( !item || item.type !== 'mwCategory' ) {
				continue;
			}
			// Yes, this is the way to read out categories!
			// HINT: https://github.com/wikimedia/mediawiki-extensions-VisualEditor/blob/ffaab335cee2d9a45cf6767fc40d3463f599b175/modules/ve-mw/ui/pages/ve.ui.MWCategoriesPage.js#L209
			prefixedCategory = item.element.attributes.category;
			categoryParts = prefixedCategory.split( ':', 2 );
			categories.push( categoryParts[ 1 ] );
		}

		return categories;
	}

	function getCategoriesFromHtml( html ) {
		const categories = [];
		const regex = new RegExp( '\\[\\[([^\\[\\]]*)\\]\\]', 'gm' ); // eslint-disable-line prefer-regex-literals
		let singleMatch;
		let title;
		let categoryName;

		while ( ( singleMatch = regex.exec( html ) ) !== null ) {
			if ( singleMatch.length !== 2 ) {
				continue;
			}
			categoryName = singleMatch.pop();
			categoryName = categoryName.split( '|' ).shift();
			title = mw.Title.newFromText( categoryName );
			if ( title === null || title.getNamespaceId() !== 14 ) {
				continue;
			}
			categories.push( title.getNameText() );
		}

		return categories;
	}

	bs.util.registerNamespace( 'bs.vec' );
	bs.vec.createEditor = createEditor;
	bs.vec.getInstance = getInstance;
	bs.vec.getCategoriesFromTarget = getCategoriesFromTarget;
	bs.vec.getCategoriesFromMetadata = getCategoriesFromMetadata;
	bs.vec.getCategoriesFromHtml = getCategoriesFromHtml;
	bs.vec.transformToWikiText = _transformToWikiText;

	// Hide QM tab
	let $qmControls;

	mw.hook( 've.activationComplete' ).add( () => {
		if ( !$qmControls ) {
			$qmControls = $( '#bs-qualitymanagement-panel, a[href="#bs-qualitymanagement-panel"]' );
		}
		$qmControls.hide();
	} );

	mw.hook( 've.deactivationComplete' ).add( () => {
		if ( $qmControls ) {
			$qmControls.show();
		}
	} );

	// VisualEditorConnector config
	const configValues = require( './config.json' );
	bs.vec.config = new mw.Map();
	bs.vec.config.set( configValues );

}( mediaWiki, jQuery, blueSpice ) );
