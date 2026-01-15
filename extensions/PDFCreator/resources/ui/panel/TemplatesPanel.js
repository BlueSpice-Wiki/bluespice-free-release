'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.panel = ext.pdfcreator.ui.panel || {};

ext.pdfcreator.ui.panel.TemplatesPanel = function ( cfg ) {
	ext.pdfcreator.ui.panel.TemplatesPanel.super.apply( this, cfg );
	this.editRight = cfg.editRight || false;
	this.$element = $( '<div>' ).addClass( 'pdfcreator-ui-templates-panel' );
	this.$overlay = cfg.$overlay || null;

	mw.loader.using( [ 'ext.pdfcreator.export.api' ] ).done( () => {
		const api = new ext.pdfcreator.api.Api();
		api.getTemplates().done( ( data ) => {
			this.setupGrid( data );
		} );
	} );
};

OO.inheritClass( ext.pdfcreator.ui.panel.TemplatesPanel, OO.ui.PanelLayout );

ext.pdfcreator.ui.panel.TemplatesPanel.prototype.setupGrid = function ( data ) {
	const columns = {
		template: {
			headerText: mw.message( 'pdfcreator-panel-templates-overview-column-templates-label' ).text(),
			type: 'url',
			urlProperty: 'url',
			valueParser: ( value, row ) => {
				if ( row.disabled ) {
					return new OO.ui.HtmlSnippet( mw.html.element( 'span', {}, value ) );
				}
				return new OO.ui.HtmlSnippet(
					mw.html.element(
						'a',
						{
							href: row.url
						},
						value
					)
				);
			}
		}
	};
	if ( this.editRight ) {
		columns.edit = {
			type: 'action',
			title: mw.message( 'pdfcreator-panel-templates-overview-column-edit-title' ).text(),
			actionId: 'edit',
			icon: 'edit',
			headerText: mw.message( 'pdfcreator-panel-templates-overview-column-edit-label' ).text(),
			invisibleHeader: true,
			width: 40,
			visibleOnHover: true,
			disabled: ( row ) => row.disabled

		};
		columns.duplicate = {
			type: 'action',
			title: mw.message( 'pdfcreator-panel-templates-overview-column-copy-title' ).text(),
			actionId: 'duplicate',
			icon: 'articles',
			headerText: mw.message( 'pdfcreator-panel-templates-overview-column-copy-label' ).text(),
			invisibleHeader: true,
			width: 40,
			visibleOnHover: true,
			disabled: ( row ) => row.disabled
		};
		columns.delete = {
			type: 'action',
			title: mw.message( 'pdfcreator-panel-templates-overview-column-delete-title' ).text(),
			actionId: 'delete',
			icon: 'trash',
			headerText: mw.message( 'pdfcreator-panel-templates-overview-column-delete-label' ).text(),
			invisibleHeader: true,
			width: 40,
			visibleOnHover: true,
			disabled: ( row ) => row.disabled
		};
	}

	this.templatesGrid = new OOJSPlus.ui.data.GridWidget( {
		columns: columns,
		data: data
	} );
	this.templatesGrid.connect( this, {
		action: function ( action, row ) {
			const template = row.template;
			if ( action === 'edit' ) {
				mw.loader.using( [ 'ext.pdfcreator.export.api' ] ).done( () => {
					const api = new ext.pdfcreator.api.Api();
					api.getTemplateValues( template ).done( ( result ) => {
						const templateValues = result.values;
						if ( templateValues.errors ) {
							require( './../dialog/NoEditDialog.js' );
							const dialog = new ext.pdfcreator.ui.dialog.NoEditDialog( {
								template: template,
								errors: templateValues.errors
							} );
							dialog.connect( this, {
								edit: function () {
									const templateTitle = mw.Title.newFromText( 'MediaWiki:PDFCreator/' + template );
									window.location.href = templateTitle.getUrl( { action: 'edit', backTo: 'Special:PDF_templates' } );
								}
							} );
							const windowManager = new OO.ui.WindowManager( {
								modal: true
							} );
							$( document.body ).append( windowManager.$element );
							windowManager.addWindows( [ dialog ] );
							windowManager.openWindow( dialog );
							return;
						}
						mw.loader.using( [ 'ext.pdfcreator.template.edit.dialog' ] ).done( () => {
							const dialog = new ext.pdfcreator.ui.dialog.EditDialog( {
								data: templateValues,
								params: result.params,
								mode: 'edit'
							} );
							dialog.connect( this, {
								saved: function () {
									this.templatesGrid.store.reload();
								}
							} );
							const windowManager = new OO.ui.WindowManager( {
								modal: true
							} );
							$( document.body ).append( windowManager.$element );
							windowManager.addWindows( [ dialog ] );
							windowManager.openWindow( dialog );
						} );
					} );
				} );
				return;
			}
			if ( action === 'duplicate' ) {
				require( './../dialog/DuplicateDialog.js' );
				const dialog = new ext.pdfcreator.ui.dialog.DuplicateDialog( {
					originTemplate: template
				} );
				dialog.connect( this, {
					copied: function () {
						window.location.reload();
					}
				} );
				const windowManager = new OO.ui.WindowManager( {
					modal: true
				} );
				$( document.body ).append( windowManager.$element );
				windowManager.addWindows( [ dialog ] );
				windowManager.openWindow( dialog );
			}
			if ( action === 'delete' ) {
				require( './../dialog/DeleteDialog.js' );
				const dialog = new ext.pdfcreator.ui.dialog.DeleteDialog( {
					template: template
				} );
				dialog.connect( this, {
					deleted: function () {
						window.location.reload();
					}
				} );
				const windowManager = new OO.ui.WindowManager( {
					modal: true
				} );
				$( document.body ).append( windowManager.$element );
				windowManager.addWindows( [ dialog ] );
				windowManager.openWindow( dialog );
			}
		}
	} );
	this.$element.append( this.templatesGrid.$element );
};
