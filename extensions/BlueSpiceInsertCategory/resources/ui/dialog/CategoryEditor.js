( function ( $, bs, mw ) {
	bs.util.registerNamespace( 'ext.InsertCategory.ui.dialog' );

	ext.InsertCategory.ui.dialog.CategoryEditor = function ( config ) {
		ext.InsertCategory.ui.dialog.CategoryEditor.parent.call( this, Object.assign( {
			size: 'large'
		}, config ) );

		this.pageId = mw.config.get( 'wgArticleId' );

		this.$element.addClass( 'bs-insert-category-editor-dialog' );
	};

	OO.inheritClass( ext.InsertCategory.ui.dialog.CategoryEditor, OO.ui.ProcessDialog );

	ext.InsertCategory.ui.dialog.CategoryEditor.static.name = 'BSInsertCategoryEditorDialog';
	ext.InsertCategory.ui.dialog.CategoryEditor.static.title = mw.message( 'bs-insertcategory-edit-dialog-title' ).text();
	ext.InsertCategory.ui.dialog.CategoryEditor.static.actions = [
		{
			action: 'submit',
			label: mw.message( 'bs-insertcategory-edit-dialog-button-submit' ).text(),
			flags: [ 'primary', 'progressive' ]
		},
		{
			action: 'cancel',
			label: mw.message( 'bs-insertcategory-edit-dialog-button-cancel' ).text(),
			flags: [ 'safe' ]
		}
	];

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.initialize = function () {
		ext.InsertCategory.ui.dialog.CategoryEditor.parent.prototype.initialize.call( this );

		this.addSelector();
		this.getPageCategories().done( ( selected, implicit ) => {
			this.popPending();
			this.selector.setValue( selected );
			this.selector.setDisabled( false );
			this.selector.focus();
			this.addImplicitCategorySection( implicit );
			this.addCategoryTree();
			this.updateSize();
		} ).fail( () => {
			this.popPending();

			const error = new OO.ui.Error(
				mw.message( 'bs-insertcategory-edit-dialog-page-categories-get-error-message' ).text(),
				{
					recoverable: false
				}
			);
			this.showErrors( error );
		} );

	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.getReadyProcess = function () {
		return ext.InsertCategory.ui.dialog.CategoryEditor.parent.prototype.getReadyProcess.call( this )
			.next( function () {
				this.selector.focus();
			}, this );
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.getPageCategories = function () {
		this.pushPending();
		const dfd = $.Deferred();

		new mw.Api().get( {
			action: 'bs-categorylinks-store',
			start: 0,
			limit: 999,
			filter: JSON.stringify( [ {
				type: 'numeric',
				operator: 'eq',
				property: 'page_id',
				value: this.pageId
			} ] )
		} ).done( ( response ) => {
			const selected = [], implicit = [];
			for ( let i = 0; i < response.results.length; i++ ) {
				const result = response.results[ i ];
				if ( result.category_is_explicit === true ) {
					selected.push( result.category_title );
				} else {
					implicit.push( {
						title: result.category_title,
						href: result.category_link
					} );
				}
			}

			dfd.resolve( selected, implicit );
		} ).fail( ( error ) => {
			console.log( error ); // eslint-disable-line no-console
			dfd.reject();
		} );

		return dfd.promise();
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.addSelector = function () {
		this.selector = new OOJSPlus.ui.widget.CategoryMultiSelectWidget( {
			allowArbitrary: true,
			$overlay: this.$overlay,
			disabled: true
		} );

		const selectorLayout = new OO.ui.FieldLayout( this.selector, {
			label: mw.message( 'bs-insertcategory-edit-dialog-input-label' ).text(),
			align: 'top'
		} );

		this.$body.append( new OO.ui.PanelLayout( {
			padded: true,
			expanded: false,
			$content: selectorLayout.$element
		} ).$element );
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.addImplicitCategorySection = function ( implicit ) {
		if ( implicit.length === 0 ) {
			return;
		}

		const implicitCatLayout = new OO.ui.FieldsetLayout( {
				label: mw.message( 'bs-insertcategory-edit-dialog-implicit-categories-label' ).text(),
				help: mw.message( 'bs-insertcategory-edit-dialog-implicit-categories-help' ).text()
			} ),
			implicitCatTextLayout = new OO.ui.HorizontalLayout();

		let html = '';
		for ( let i = 0; i < implicit.length; i++ ) {
			html = html + '<span class="oo-ui-tagItemWidget">' + implicit[ i ].title + '</span>';
		}

		const htmlSnippet = new OO.ui.HtmlSnippet( html );

		const implicitCatText = new OO.ui.Element( {
			content: [ htmlSnippet ]
		} );

		implicitCatTextLayout.addItems( [ implicitCatText ] );
		implicitCatLayout.addItems( [ implicitCatTextLayout ] );

		this.$body.append( new OO.ui.PanelLayout( {
			padded: true,
			expanded: false,
			$content: implicitCatLayout.$element
		} ).$element );
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.getActionProcess = function ( action ) {
		if ( action === 'submit' ) {
			return new OO.ui.Process( () => {
				const dfd = $.Deferred();
				const categories = this.selector.getValue();

				$.ajax( {
					url: mw.util.wikiScript( 'rest' ) + '/bluespice/insertcategory/v1/set_categories/' + this.pageId,
					dataType: 'json',
					type: 'POST',
					data: JSON.stringify( {
						categories: categories
					} ),
					contentType: 'application/json; charset=utf-8'
				} ).done( () => {
					window.location.reload();
				} ).fail( () => {
					dfd.reject( new OO.ui.Error( mw.msg( 'bs-insertcategory-set-categories-error' ) ) );
				} );

				return dfd.promise();
			} );
		}

		if ( action === 'cancel' ) {
			return new OO.ui.Process( () => {
				this.close();
			} );
		}

		return ext.InsertCategory.ui.dialog.CategoryEditor.parent.prototype.getActionProcess.call( this, action );
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.getBodyHeight = function () {
		if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
			return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
		}
		return this.$body[ 0 ].scrollHeight;
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.onDismissErrorButtonClick = function () {
		this.hideErrors();
		this.updateSize();
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.showErrors = function () {
		ext.InsertCategory.ui.dialog.CategoryEditor.parent.prototype.showErrors.call( this, arguments );
		this.updateSize();
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.onTreeItemSelected = function ( item ) {
		const text = item.label,
			value = this.selector.getValue();

		if ( value.indexOf( text ) !== -1 ) {
			value.splice( value.indexOf( text ), 1 );
		} else {
			value.push( text );
		}
		this.selector.setValue( value );
	};

	ext.InsertCategory.ui.dialog.CategoryEditor.prototype.addCategoryTree = function () {
		this.categoryTree = new OOJSPlus.ui.data.StoreTree(
			{
				store: {
					action: 'bs-category-treestore',
					rootNode: 'src'
				},
				expanded: false,
				id: 'category-editor-tree',
				labelledby: 'bs-insertcategory-edit-dialog-tree-view-tgl',
				style: {
					IconExpand: 'next',
					IconCollapse: 'expand'
				}
			}
		);
		this.categoryTree.connect( this, {
			loaded: function () {
				this.updateSize();
			},
			'collapse-expand': function () {
				this.updateSize();
			},
			itemSelected: function ( item ) {
				this.onTreeItemSelected( item );
			}
		} );
		this.categoryTree.$element.hide();
		this.categoryTree.$element.css( {
			'max-height': '500px',
			'padding-left': '20px',
			'overflow-y': 'auto'
		} );

		const expander = new OO.ui.ButtonWidget( {
			label: mw.message( 'bs-insertcategory-edit-dialog-tree-view' ).text(),
			framed: false,
			flags: [ 'primary', 'progressive' ],
			id: 'bs-insertcategory-edit-dialog-tree-view-tgl'
		} );
		expander.connect( this, {
			click: function () {
				this.categoryTree.$element.toggle();
				this.updateSize();
			}
		} );

		this.$body.append( $( '<div>' ).css( {
			padding: '10px',
			margin: '0 0 0 10px'
		} ).append(
			expander.$element,
			this.categoryTree.$element
		) );
	};
}( jQuery, blueSpice, mediaWiki ) );
