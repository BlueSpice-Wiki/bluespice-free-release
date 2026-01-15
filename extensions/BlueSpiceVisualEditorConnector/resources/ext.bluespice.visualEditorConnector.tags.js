( ( mw, bs ) => {
	const tagDefinitions = [];

	/**
	 * @param {bs.vec.util.TagDefinition} tagDefinition
	 */
	function registerTagDefinition( tagDefinition ) {
		tagDefinitions.push( tagDefinition );
	}

	/**
	 * @return {bs.vec.util.TagDefinition[]} array of callbacks
	 */
	function getTagDefinitions() {
		return tagDefinitions;
	}

	/**
	 * Waits for `bs.vec.config` to be available.
	 *
	 * @param {number} [retries=10] - Maximum retry attempts.
	 * @param {number} [delay=50] - Delay between retries (milliseconds).
	 * @return {Promise<object>} Resolves with `bs.vec.config` once available.
	 */
	function waitForBsVecConfig( retries = 10, delay = 50 ) {
		return new Promise( ( resolve, reject ) => {
			let attempts = 0;
			const checkConfig = setInterval( () => {
				if ( bs.vec.config ) {
					clearInterval( checkConfig );
					resolve( bs.vec.config );
					return;
				}

				attempts++;
				if ( attempts >= retries ) {
					clearInterval( checkConfig );
					reject( new Error( 'bs.vec.config undefined' ) );
				}
			}, delay );
		} );
	}

	bs.util.registerNamespace( 'bs.vec' );
	bs.vec.registerTagDefinition = registerTagDefinition;
	bs.vec.getTagDefinitions = getTagDefinitions;

	async function init() {
		try {
			// Step 1: Load the tags classes
			await mw.loader.using( [ 'ext.bluespice.visualEditorConnector.tags.classes', 'ext.visualEditor.mwcore' ] );
			// Keep in sync with `extension.json/ResourceModules/ext.bluespice.visualEditorConnector.tags.classes/scripts`
			const tagRegistry = new bs.vec.util.tag.Registry();

			// Step 2: Load all plugin modules that may want to register to those classes
			const bsvecConfig = await waitForBsVecConfig();
			const bsvecTagDefinitions = bsvecConfig.get( 'TagDefinitions' );
			if ( bsvecTagDefinitions.length === 0 ) {
				return;
			}

			await mw.loader.using( bsvecTagDefinitions );
			tagRegistry.initialize();
		} catch ( error ) {
			console.error( error.message ); // eslint-disable-line no-console
		}
	}

	init();
} )( mediaWiki, blueSpice );
