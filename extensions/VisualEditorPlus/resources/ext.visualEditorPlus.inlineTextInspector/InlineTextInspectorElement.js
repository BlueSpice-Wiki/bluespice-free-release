ext.visualEditorPlus.ui.InlineTextInspectorElement = function ( inspector, config ) {
	config.expanded = false;
	// Parent constructor
	ext.visualEditorPlus.ui.InlineTextInspectorElement.super.call( this, config );
	this.$element.addClass( 'ext-visualEditorPlus-inlineTextInspector-element' );
	this.inspector = inspector;
};

OO.inheritClass( ext.visualEditorPlus.ui.InlineTextInspectorElement, OO.ui.PanelLayout );

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.init = function () {
	// Called when inspector element is added to the parent inspector
};

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.inspect = function ( range, selectedText ) { // eslint-disable-line no-unused-vars
	// Overwrite this method in your inspector
};

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.getTitleLabel = function () {
	// Override this method in your inspector
	return null;
};

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.getIcon = function () {
	// Override this method in your inspector
	return null;
};

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.onClose = function () {
	// Override this method in your inspector
};

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.getHeader = function () {
	if ( !this.getTitleLabel() ) {
		return null;
	}
	const header = new OO.ui.HorizontalLayout( {
		classes: [ 'ext-visualEditorPlus-inlineTextInspector-element-header' ]
	} );
	if ( this.getIcon() ) {
		header.$element.append( new OO.ui.IconWidget( {
			icon: this.getIcon(),
			classes: [ 'ext-visualEditorPlus-inlineTextInspector-element-icon' ]
		} ).$element );
	}
	header.$element.append( new OO.ui.LabelWidget( {
		label: this.getTitleLabel(),
		classes: [ 'ext-visualEditorPlus-inlineTextInspector-element-label' ]
	} ).$element );

	return header;
};

ext.visualEditorPlus.ui.InlineTextInspectorElement.prototype.getPriority = function () {
	return 0;
};
