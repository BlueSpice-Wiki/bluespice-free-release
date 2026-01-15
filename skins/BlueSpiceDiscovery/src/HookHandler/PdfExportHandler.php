<?php

namespace BlueSpice\Discovery\HookHandler;

use DOMDocument;
use DOMElement;
use DOMNodeList;

class PdfExportHandler {

	/**
	 * Add bootstrap icons resources to pdf export
	 *
	 * @param array &$template
	 * @param array &$contents
	 * @param ExportSpecification $specs
	 * @param array &$page
	 * @return bool
	 */
	public function onBSUEModulePDFBeforeAddingContent( &$template,	&$contents,	$specification,	&$page ) {
		$dir = dirname( __DIR__, 2 );

		// PDF export service can not handle woff and woff2 fonts.
		$bootstrapFontPath = "$dir/data/PDFTemplates/common/fonts/bootstrap-icons.ttf";
		$template['resources']['STYLESHEET']['bootstrap-icons.ttf'] = $bootstrapFontPath;

		$bootstrapFontStylesPath = "$dir/data/PDFTemplates/common/stylesheets/bootstrap-icons-fonts.css";
		$template['resources']['STYLESHEET']['bootstrap-icons-fonts.css'] = $bootstrapFontStylesPath;

		$bootstrapIconStylesPath = "$dir/resources/bootstrap/icons/bootstrap-icons.css";
		$template['resources']['STYLESHEET']['bootstrap-icons.css'] = $bootstrapIconStylesPath;

		/** @var DOMDocument */
		$dom = $template['dom'];

		/** @var DOMElement */
		$bootstrapFontStyles = $this->createLinkNode( $dom, 'stylesheets/bootstrap-icons-fonts.css' );

		/** @var DOMElement */
		$bootstrapIconsStyles = $this->createLinkNode( $dom, 'stylesheets/bootstrap-icons.css' );

		/** @var DOMNodeList */
		$head = $dom->getElementsByTagName( 'head' );
		$head->item( 0 )->appendChild( $bootstrapFontStyles );
		$head->item( 0 )->appendChild( $bootstrapIconsStyles );

		return true;
	}

	/**
	 * @param DOMDocument $dom
	 * @param string $href
	 * @param string $type
	 * @param string $rel
	 * @return DOMElement
	 */
	private function createLinkNode(
		DOMDocument $dom, string $href, string $type = 'text/css', string $rel = 'stylesheet'
	): DOMElement {
		/** @var DOMElement */
		$linkNode = $dom->createElement( 'link' );
		$linkNode->setAttribute( 'href', $href );
		$linkNode->setAttribute( 'type', $type );
		$linkNode->setAttribute( 'rel', $rel );

		return $linkNode;
	}

}
