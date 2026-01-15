ve.ce.AttachmentNode = function VeCeAttachmentNode() {
	// Parent constructor
	ve.ce.AttachmentNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.AttachmentNode, ve.ce.MWInlineExtensionNode );

/* Static properties */

ve.ce.AttachmentNode.static.name = 'attachments';

ve.ce.AttachmentNode.static.primaryCommandName = 'attachments';

// If body is empty, tag does not render anything
ve.ce.AttachmentNode.static.rendersEmpty = false;

/* Registration */

ve.ce.nodeFactory.register( ve.ce.AttachmentNode );
