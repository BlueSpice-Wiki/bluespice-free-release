'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

ext.pdfcreator.ui.booklet.pages.Footer = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.Footer.parent.call( this, name, cfg );

	this.appendPreview();
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.Footer,
	ext.pdfcreator.ui.booklet.pages.SectionPage );

ext.pdfcreator.ui.booklet.pages.Footer.prototype.getElements = function () {
	const options = [];
	for ( const key in this.params ) {
		options.push( {
			data: '{{{' + this.params[ key ] + '}}}'
		} );
	}
	this.leftFooterSection = this.getParamsCombobox( this.data.leftContent || '' );
	this.leftFooterSection.connect( this, {
		change: function () {
			this.updatePreview();
		}
	} );
	this.middleFooterSection = this.getParamsCombobox( this.data.middleContent || '' );
	this.middleFooterSection.connect( this, {
		change: function () {
			this.updatePreview();
		}
	} );
	this.rightFooterSection = this.getParamsCombobox( this.data.rightContent || '' );
	this.rightFooterSection.connect( this, {
		change: function () {
			this.updatePreview();
		}
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-footer-page-heading' ).text(),
					classes: [ 'pdfcreator-edit-heading' ]
				} ),
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-footer-page-desc' ).text(),
					classes: [ 'pdfcreator-edit-desc' ]
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.leftFooterSection, {
					label: mw.message( 'pdfcreator-template-edit-dlg-footer-left-label' ).text(),
					align: 'top',
					help: mw.message( 'pdfcreator-template-edit-dlg-text-input-help-label' ).text()
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.middleFooterSection, {
					label: mw.message( 'pdfcreator-template-edit-dlg-footer-middle-label' ).text(),
					align: 'top',
					help: mw.message( 'pdfcreator-template-edit-dlg-text-input-help-label' ).text()
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.rightFooterSection, {
					label: mw.message( 'pdfcreator-template-edit-dlg-footer-right-label' ).text(),
					align: 'top',
					help: mw.message( 'pdfcreator-template-edit-dlg-text-input-help-label' ).text()
				} )
			]
		} )
	];
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.getData = function () {
	return {
		leftContent: this.leftFooterSection.getValue(),
		middleContent: this.middleFooterSection.getValue(),
		rightContent: this.rightFooterSection.getValue()
	};
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.appendPreview = function () {
	const $previewCnt = $( '<div>' ).addClass( 'pdfcreator-footer-preview' );

	const $table = $( '<table>' ).addClass( 'pdfcreator-preview-table-footer' );
	const $tableRow = $( '<tr>' );
	this.$leftTd = $( '<td>' ).addClass( 'pdfcreator-preview-table-footer-left' );
	this.$middleTd = $( '<td>' ).addClass( 'pdfcreator-preview-table-footer-middle' );
	this.$rightTd = $( '<td>' ).addClass( 'pdfcreator-preview-table-footer-right' );
	$tableRow.append( this.$leftTd ).append( this.$middleTd ).append( this.$rightTd );
	$table.append( $tableRow );
	$previewCnt.append( $table );
	const $fieldSet = this.$element[ 0 ].getElementsByTagName( 'fieldset' )[ 0 ];
	$( $fieldSet ).append( $previewCnt );

	this.updatePreview();
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.updatePreview = function () {
	this.preparePreview( this.leftFooterSection.getValue() ).done( ( result ) => {
		this.$leftTd.html( result );
	} );
	this.preparePreview( this.middleFooterSection.getValue() ).done( ( result ) => {
		this.$middleTd.html( result );
	} );
	this.preparePreview( this.rightFooterSection.getValue() ).done( ( result ) => {
		this.$rightTd.html( result );
	} );
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.preparePreview = function ( value ) {
	const dfd = $.Deferred();

	/** eslint-disable-next-line es-x/no-string-prototype-matchall */
	const intMatches = [ ...value.matchAll( /{{int:([\w-]+)}}/g ) ];
	const intKeys = intMatches.map( ( match ) => match[ 1 ] );
	this.translations = [];

	if ( intKeys.length > 0 ) {
		this.parseMessages( intKeys, value ).done( ( result ) => {
			dfd.resolve( result );
		} );
	} else {
		value = this.parseParams( value );
		dfd.resolve( value );
	}

	return dfd.promise();
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.parseMessages = function ( intKeys, message ) {
	const dfds = [];
	const dfd = $.Deferred();
	for ( let key = 0; key < intKeys.length; key++ ) {
		const parseDfd = this.parseMessage( intKeys[ key ] );
		parseDfd.done( ( result ) => {
			this.translations[ key ] = result;
		} );
		dfds.push( parseDfd );
	}
	$.when.apply( this, dfds ).then( () => {
		let counter = -1;
		let result = message.replace( /{{int:([\w-]+)}}/g, ( _, key ) => {
			counter++;
			return this.translations[ counter ] || `{{int:${ key }}}`;
		} );

		result = this.parseParams( result );
		dfd.resolve( result );
	} );
	return dfd.promise();
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.parseParams = function ( content ) {
	return content.replace( /{{{([\w-]+)}}}/g, ( _, key ) => {
		const date = new Date();
		if ( key === 'export-time' ) {
			return date.getHours() + ':' + new Date().getMinutes();
		}
		if ( key === 'export-date' ) {
			return date.getDay() + '.' + date.getMonth() + '.' + date.getFullYear();
		}
		if ( key === 'username' || key === 'user-realname' ) {
			return mw.user.getName();
		}
		return this.params[ key ].example !== undefined ? this.params[ key ].example : `{{{${ key }}}}`;
	} );
};

ext.pdfcreator.ui.booklet.pages.Footer.prototype.parseMessage = function ( message ) {
	const dfd = $.Deferred();
	const api = new mw.Api();

	api.parse( '{{int: ' + message + '}}', {
		disablelimitreport: '',
		preview: '',
		title: 'MediaWiki:' + message
	} ).done( ( result ) => {
		dfd.resolve( result );
	} ).fail( ( error ) => {
		dfd.reject( error );
	} );

	return dfd.promise();
};
