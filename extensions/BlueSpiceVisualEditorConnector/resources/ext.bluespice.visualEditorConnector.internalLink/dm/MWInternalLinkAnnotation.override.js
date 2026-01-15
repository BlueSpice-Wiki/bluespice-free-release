// This is a hard override of a single static method. This is not good practice,
// but for now I can't find a better way around this since its called statically
// I found no evidence that this is fixed in later versions of VisualEditor or Parsoid
// ERM #16886
ve.dm.MWInternalLinkAnnotation.static.getTargetDataFromHref = function ( href, doc ) {
	let isInternal;

	function regexEscape( str ) {
		return str.replace( /([.?*+^$[\]\\(){}|-])/g, '\\$1' );
	}

	// Protocol relative base
	const relativeBase = ve.resolveUrl( mw.config.get( 'wgArticlePath' ), doc ).replace( /^https?:/i, '' );
	const relativeBaseRegex = new RegExp( regexEscape( relativeBase ).replace( regexEscape( '$1' ), '(.*)' ) );
	// Protocol relative href
	const relativeHref = href.replace( /^https?:/i, '' );
	// Paths without a host portion are assumed to be internal
	isInternal = !/^\/\//.test( relativeHref );
	// Check if this matches the server's article path
	const matches = relativeHref.match( relativeBaseRegex );

	if ( matches && matches[ 1 ].indexOf( '?' ) === -1 ) {
		// Take the relative path
		href = matches[ 1 ];
		isInternal = true;
	}

	// This href doesn't necessarily come from Parsoid (and it might not have the "./" prefix), but
	// this method will work fine.
	const data = mw.libs.ve.parseParsoidResourceName( href );

	// Replace Special:FilePath/ coming from Parsoid with normal Media namespace
	// We only need to take care of english version of "Media" namespace,
	// since others are not recognized by Parsoid and would not be treated
	// like media links
	data.title = data.title.replace( 'Special:FilePath/', 'Media:' );
	return {
		title: data.title,
		hrefPrefix: data.hrefPrefix,
		isInternal: isInternal
	};
};
