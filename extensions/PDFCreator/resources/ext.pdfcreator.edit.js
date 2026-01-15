'use strict';

window.ext = window.ext || {};

ext.pdfcreator = {
	ui: {},
	api: {}
};

( function () {

	const htmlEditors = {};
	const tabs = OO.ui.infuse( $( '.pdf-creator-template-tab' ) );
	if ( tabs ) {
		$( '#pdf-creator-skeleton-cnt' ).css( 'display', 'none' );
		$( '#pdf-creator-template-cnt' ).removeAttr( 'style' );
	}
	const Vue = require( 'vue' );
	const EditorToolbar = require( './ui/EditorToolbar.vue' );
	const helpConfig = require( './ui/helpConfig.json' );
	const h = Vue.h;
	Vue.createMwApp( {
		mounted: function () {
		},
		render: function () {
			return h( EditorToolbar, {
				class: '',
				toolbar: true,
				tools: [
					{
						label: mw.message( 'pdfcreator-editor-action-help-label' ).text(),
						slot: 'right',
						type: 'button',
						weight: 'primary',
						id: 'help'
					},
					{
						action: 'progressive',
						label: mw.message( 'pdfcreator-editor-action-save-label' ).text(),
						slot: 'right',
						type: 'button',
						weight: 'primary',
						id: 'save'
					}
				],
				hasCancelButton: true,
				config: helpConfig.pageParams,
				toolbarFloatingOffset: 10
			} );
		}
	} ).mount( '#pdf-creator-toolbar' );
	let actionButton = $( '#mw-content-text' ).find( '.cdx-button--action-progressive' );
	if ( actionButton.length === 1 ) {
		actionButton[ 0 ].disabled = true;
	}

	$( 'textarea[data-editor]' ).each( function () {
		const textarea = $( this );
		const slotname = textarea.attr( 'name' );

		// Maybe this prevent editor issue
		if ( typeof ace === 'undefined' ) {
			textarea.on( 'input', () => {
				actionButton = $( '#mw-content-text' ).find( '.cdx-button--action-progressive' );
				if ( actionButton.length === 1 ) {
					actionButton[ 0 ].disabled = false;
				}
			} );

			htmlEditors[ slotname ] = textarea;
			return;
		}

		const editDiv = $( '<div>', { // eslint-disable-line no-jquery/no-constructor-attributes
			position: 'absolute',
			width: '100%',
			height: '500px',
			class: textarea.attr( 'class' )
		} ).insertBefore( textarea );
		const mode = textarea.data( 'editor' );

		textarea.css( 'display', 'none' );
		ace.config.set( 'basePath', mw.config.get( 'wgScriptPath' ) + '/extensions/CodeEditor/modules/lib/ace/' ); // eslint-disable-line no-undef
		const editor = ace.edit( editDiv[ 0 ] ); // eslint-disable-line no-undef
		editor.getSession().setValue( textarea.val() );
		editor.getSession().setMode( 'ace/mode/' + mode );
		editor.getSession().on( 'change', () => {
			actionButton = $( '#mw-content-text' ).find( '.cdx-button--action-progressive' );
			if ( actionButton.length === 1 ) {
				actionButton[ 0 ].disabled = false;
			}
		} );
		htmlEditors[ slotname ] = editor;
	} );

	require( './api/Api.js' );
	document.addEventListener( 'pdfcreator_editor_save', () => {
		const pbWidget = new OO.ui.ProgressBarWidget( {
			progress: false,
			classes: [ 'pdfcreator-loading-save' ]
		} );

		$( '#mw-content-text' ).prepend( pbWidget.$element );

		if ( typeof ace === 'undefined' ) {
			saveTextareas();
			return;
		}
		const errors = [];

		for ( const editor in htmlEditors ) {
			if ( !htmlEditors[ editor ] ) {
				saveTextareas();
				return;
			}
			if ( typeof htmlEditors[ editor ].getValue === 'undefined' ) {
				saveTextareas();
				return;
			}
			if ( htmlEditors[ editor ].getValue() === '' ) {
				continue;
			}
			const annotations = htmlEditors[ editor ].getSession().getAnnotations();
			const error = annotations.filter( ( itm ) => {
				itm.editor = editor;
				return itm.type === 'error';
			} );
			error.forEach( ( err ) => {
				errors.push( err );
			} );
		}
		const validatedBody = validateBodyData(
			htmlEditors.pdfcreator_template_body.getValue()
		);
		if ( errors.length > 0 || !validatedBody ) {
			showErrors( errors, validatedBody );
		}

		/* eslint-disable camelcase */
		const data = {
			pdfcreator_template_header: htmlEditors.pdfcreator_template_header.getValue(),
			pdfcreator_template_body: htmlEditors.pdfcreator_template_body.getValue(),
			pdfcreator_template_footer: htmlEditors.pdfcreator_template_footer.getValue(),
			pdfcreator_template_intro: htmlEditors.pdfcreator_template_intro.getValue(),
			pdfcreator_template_outro: htmlEditors.pdfcreator_template_outro.getValue(),
			pdfcreator_template_styles: htmlEditors.pdfcreator_template_styles.getValue(),
			pdfcreator_template_options: htmlEditors.pdfcreator_template_options.getValue() || '{}',
			main: htmlEditors.pdfcreator_template_main.getValue()
		};
		saveData( data );
	} );

	const toolbarOffset = require( './addToolbarOffset.json' );
	const offset = toolbarOffset.offset;
	$( window ).on( 'scroll', function () {
		const windowTop = $( this ).scrollTop();
		const $toolbar = $( '#pdf-creator-toolbar' );
		const contentWidth = getContentWidth();
		if ( windowTop > offset ) {
			$toolbar.css( 'top', offset );
			$toolbar.css( 'position', 'fixed' );
			$toolbar.css( 'width', contentWidth );
			$toolbar.css( 'z-index', 5 );
		} else {
			$toolbar.removeAttr( 'style' );
		}
	} );

	function getContentWidth() {
		return $( '#mw-content-text' ).innerWidth();
	}

	function validateBodyData( body ) {
		if ( !body.includes( '{{{content}}}' ) ) {
			return false;
		}
		return body;
	}

	function saveTextareas() {
		const validatedBody = validateBodyData(
			htmlEditors.pdfcreator_template_body[ 0 ].value
		);
		if ( !validatedBody ) {
			showErrors( [], validatedBody );
			return;
		}
		const data = {
			// eslint-disable camelcase
			pdfcreator_template_header: htmlEditors.pdfcreator_template_header[ 0 ].value,
			pdfcreator_template_body: htmlEditors.pdfcreator_template_body[ 0 ].value,
			pdfcreator_template_footer: htmlEditors.pdfcreator_template_footer[ 0 ].value,
			pdfcreator_template_intro: htmlEditors.pdfcreator_template_intro[ 0 ].value,
			pdfcreator_template_outro: htmlEditors.pdfcreator_template_outro[ 0 ].value,
			pdfcreator_template_styles: htmlEditors.pdfcreator_template_styles[ 0 ].value,
			pdfcreator_template_options: htmlEditors.pdfcreator_template_options[ 0 ].value || '{}',
			main: htmlEditors.pdfcreator_template_main[ 0 ].value
			// eslint-enable camelcase
		};
		saveData( data );
	}

	function showErrors( errors, validateBody ) {
		const panel = new OO.ui.PanelLayout( {
			expanded: false,
			framed: true,
			padded: true,
			classes: [ 'pdfcreator-editor-error-panel' ]
		} );

		errors.forEach( ( error ) => {
			const type = error.editor.split( '_' )[ 2 ];
			const tabName = mw.message( 'pdfcreator-tab-panel-' + type + '-label' ).text(); // eslint-disable-line mediawiki/msg-doc

			// Note: error.row + 1 necessary to match line numbers
			const errorLine = error.row + 1;
			const msg = mw.message( 'pdfcreator-editor-error-label', tabName, errorLine, error.text ).text();
			const msgWidget = new OO.ui.MessageWidget( {
				type: error.type,
				label: new OO.ui.HtmlSnippet( msg )
			} );
			panel.$element.append( msgWidget.$element );
		} );

		if ( !validateBody ) {
			const validatedWidget = new OO.ui.MessageWidget( {
				type: 'error',
				label: new OO.ui.HtmlSnippet(
					mw.message( 'pdfcreator-editor-error-missing-content' ).text()
				)
			} );
			panel.$element.append( validatedWidget.$element );
		}
		$( '#mw-content-text' ).find( '.pdfcreator-editor-error-panel' ).remove();
		$( '#mw-content-text' ).find( '.pdfcreator-loading-save' ).remove();
		$( '#mw-content-text' ).prepend( panel.$element );
	}

	function saveData( data ) {
		const api = new ext.pdfcreator.api.Api();
		const pageName = mw.config.get( 'wgPageName' );
		const subpageName = pageName.split( '/' )[ 1 ];
		api.save( subpageName, data ).done( () => {
			window.location.href = mw.util.getUrl(
				pageName
			);
		} ).fail( ( error ) => {
			$( '#mw-content-text' ).find( '.pdfcreator-loading-save' ).remove();
			OO.ui.alert( error.error[ 0 ] );
		} );
	}

}() );
