ve.dm.AttachmentNode = function VeDmAttachmentNode() {
	// Parent constructor
	ve.dm.AttachmentNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.AttachmentNode, ve.dm.MWInlineExtensionNode );

/* Static members */

ve.dm.AttachmentNode.static.name = 'attachments';

ve.dm.AttachmentNode.static.tagName = 'attachments';

// Name of the parser tag
ve.dm.AttachmentNode.static.extensionName = 'attachments';

// This tag renders without content
ve.dm.AttachmentNode.static.childNodeTypes = [];
ve.dm.AttachmentNode.static.isContent = false;

/* Registration */

ve.dm.modelRegistry.register( ve.dm.AttachmentNode );
