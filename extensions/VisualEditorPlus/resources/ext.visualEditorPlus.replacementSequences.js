// HINT: https://de.wikipedia.org/wiki/Benutzer:Schnark/js/veAutocorrect.js
( function ( ve ) {

	const replacements = {
		' :)': ' 😊',
		' :-)': ' 😊',
		' :(': ' 😞',
		' :-(': ' 😞',
		' :D': ' 😄',
		' :P': ' 😛',
		' :O': ' 😲',
		' ;)': ' 😉',
		' ;-)': ' 😉',
		' :|': ' 😐',
		' :-|': ' 😐',
		' :/': ' 😕',
		' :-/': ' 😕',
		' :-@': ' 😠',
		' :-X': ' 🤐',
		' :\'(': ' 😢',
		' :-O': ' 😲',
		' :-S': ' 😖',
		':lol:': '😂',
		' (:|': '😴',
		' :-[': ' 😳',
		' [-X': ' 😳',
		' (v)': ' ✅',
		':good:': '👍',
		' (x)': ' ❌',
		':bad:': '👎',
		' (?)': ' ❓',
		'--> ': '→',
		'<-- ': '←',
		'<-> ': '↔'
	};

	const sequences = [];

	for ( const key in replacements ) {
		const value = replacements[ key ];
		let encKey = '';
		for ( let i = 0; i < key.length; i++ ) {
			encKey += key.charCodeAt( i );
		}

		const commandName = 'insertReplacement' + encKey;
		const sequenceName = 'replacement' + encKey;

		const command = new ve.ui.Command(
			commandName,
			'content',
			'insert',
			{
				args: [ value, true, true ]
			}
		);

		const replacementSequence = new ve.ui.Sequence(
			sequenceName,
			commandName,
			key,
			key.length
		);

		ve.ui.commandRegistry.register( command );
		sequences.push( replacementSequence );
	}

	sequences.push( new ext.visualEditorPlus.ui.BackTickCodeSequence() );
	sequences.push( new ext.visualEditorPlus.ui.TildeStrikeThroughSequence() );
	sequences.push( new ext.visualEditorPlus.ui.UnderlineItalicSequence() );

	for ( let j = 0; j < sequences.length; j++ ) {
		const sequence = sequences[ j ];
		if ( ve.ui.sequenceRegistry ) {
			ve.ui.sequenceRegistry.register( sequence );
		}
		if ( ve.ui.wikitextSequenceRegistry ) {
			ve.ui.wikitextSequenceRegistry.register( sequence );
		}
	}
}( ve ) );
