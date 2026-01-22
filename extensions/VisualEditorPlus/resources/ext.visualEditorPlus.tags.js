function getSpecifications() {
	const dfd = $.Deferred();
	$.ajax( {
		url: mw.util.wikiScript( 'rest' ) + '/mws/v1/tags',
		type: 'GET'
	} ).done( ( data ) => {
		const definitions = [];
		for ( const fullData of data ) {
			const spec = fullData.clientSpecification;
			if ( !spec ) {
				continue;
			}
			for ( let i = 0; i < fullData.tags.length; i++ ) {
				const tagDefinition = {
					instance: null
				};
				const definitionData = Object.assign( spec, {
					name: fullData.tags[ i ].replaceAll( ':', '_' ),
					tagname: fullData.tags[ i ],
					paramDefinitions: fullData.paramDefinition
				} );
				mw.hook( 'ext.visualEditorPlus.tags.getTagDefinition' ).fire( definitionData, tagDefinition );
				if ( tagDefinition instanceof ext.visualEditorPlus.ui.tag.Definition ) {
					definitions.push( tagDefinition );
					continue;
				}
				if ( tagDefinition.instance instanceof ext.visualEditorPlus.ui.tag.Definition ) {
					definitions.push( tagDefinition.instance );
					continue;
				}
				definitions.push( new ext.visualEditorPlus.ui.tag.Definition( definitionData ) );
			}
		}
		dfd.resolve( {
			definitions: definitions,
			tags: data
		} );
	} );
	return dfd.promise();
}

function _initialize() { // eslint-disable-line no-underscore-dangle
	const dfd = $.Deferred();
	getSpecifications().done( ( specs ) => {
		const _registry = new ext.visualEditorPlus.ui.tag.Registry(); // eslint-disable-line no-underscore-dangle

		specs.definitions.forEach( ( definition ) => {
			_registry.registerTagDefinition( definition );
		} );

		/**
		 * @param {ext.visualEditorPlus.ui.tag.Registry} registry
		 * @param {Object} tags All tag data as retrieved from the API
		 */
		mw.hook( 'ext.visualEditorPlus.tags.registerTags' ).fire( _registry, specs.tags );
		_registry.initialize();
		dfd.resolve();
	} );

	return dfd.promise();
}

mw.libs.ve.targetLoader.addPlugin( () => {
	const dfd = $.Deferred();
	_initialize().done( () => {
		dfd.resolve();
	} );
	return dfd.promise();
} );
