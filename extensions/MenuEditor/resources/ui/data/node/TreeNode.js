ext.menueditor.ui.data.node.TreeNode = function ( cfg ) {
	this.nodeData = cfg.nodeData;
	this.allowEdits = cfg.allowEdits || false;
	cfg.label = this.labelFromData( this.nodeData );
	cfg.labelAdd = mw.message( 'menueditor-ui-add-sub-element-label' ).text();
	cfg.style = {
		IconExpand: 'next',
		IconCollapse: 'expand'
	};
	ext.menueditor.ui.data.node.TreeNode.parent.call( this, cfg );

	this.$element.attr( 'data-type', this.nodeData.type );
	this.$element.attr( 'data-level', cfg.level );
};

OO.inheritClass( ext.menueditor.ui.data.node.TreeNode, OOJSPlus.ui.data.tree.Item );

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.TreeNode.prototype.labelFromData = function ( data ) {
	return '';
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.TreeNode.prototype.getIcon = function ( data ) {
	return '';
};

ext.menueditor.ui.data.node.TreeNode.prototype.getForm = function ( dialog ) {
	const dfd = $.Deferred();
	mw.loader.using( [ 'ext.forms.standalone' ], () => {
		// The following messages are used here
		// * menueditor-ui-menu-wiki-link-label
		// * menueditor-ui-menu-two-fold-link-spec-label
		// * menueditor-ui-menu-raw-text-label
		// * menueditor-ui-menu-keyword-label
		let msg = mw.message( 'menueditor-ui-' + this.nodeData.type + '-label-edit' );

		// Allow other extensions to show a readable name
		// without using message prefix 'menueditor-ui-'
		if ( !msg.exists() ) {
			// eslint-disable-next-line mediawiki/msg-doc
			msg = mw.message( this.nodeData.type + '-label-edit' );
		}

		const labelText = msg.text();

		const form = new mw.ext.forms.standalone.Form( Object.assign( {
			data: this.getNodeData(),
			definition: {
				items: [
					{
						type: 'section_label',
						title: labelText
					},
					{
						type: 'text',
						name: 'type',
						hidden: true
					}
				].concat( this.getFormFields( dialog ) ),
				buttons: []
			},
			errorReporting: false,
			showTitle: false
		}, this.getFormConfig() ) );
		form.render();
		form.$element.addClass( 'menueditor-menu-node-form' );

		dfd.resolve( form );
	}, () => {
		// eslint-disable-next-line no-console
		console.error( 'Cannot load Forms framework' );
		dfd.reject();
	} );

	return dfd.promise();
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.TreeNode.prototype.getFormFields = function ( dialog ) {
	// STUB
	return [];
};

ext.menueditor.ui.data.node.TreeNode.prototype.getFormConfig = function () {
	// STUB
	return {};
};

ext.menueditor.ui.data.node.TreeNode.prototype.possiblyAddOptions = function () {
	ext.menueditor.ui.data.node.TreeNode.parent.prototype.possiblyAddOptions.call( this );

	if ( !this.allowEdits ) {
		return;
	}
	const editButton = new OO.ui.ButtonWidget( {
		framed: false,
		label: mw.message( 'menueditor-ui-edit-label' ).text(),
		icon: 'edit'
	} );
	editButton.connect( this, {
		click: 'onEdit'
	} );
	// No idea why some random margin is added by default
	editButton.$element.css( { 'margin-left': 0 } );
	this.optionsPanel.$element.prepend( editButton.$element );
};

ext.menueditor.ui.data.node.TreeNode.prototype.onEdit = function () {
	this.tree.editNode( this );
};

ext.menueditor.ui.data.node.TreeNode.prototype.getNodeData = function () {
	const node = Object.assign( {}, this.nodeData );
	delete ( node.items );
	delete ( node.name );
	return node;
};

ext.menueditor.ui.data.node.TreeNode.prototype.updateData = function ( data ) {

	this.nodeData = Object.assign( this.nodeData, data );
	this.label = this.labelFromData( this.nodeData );
	this.labelWidget.setLabel( this.label );
	this.$element.attr( 'data-type', this.nodeData.type );
};

ext.menueditor.ui.data.node.TreeNode.prototype.shouldRender = function () {
	return true;
};

ext.menueditor.ui.data.node.TreeNode.prototype.addLabel = function () {
	this.labelWidget = new OOJSPlus.ui.widget.LabelWidget(
		Object.assign( {},
			{
				icon: this.getIcon()
			}, this.buttonCfg
		)
	);

	this.$wrapper.append( this.labelWidget.$element );
};
