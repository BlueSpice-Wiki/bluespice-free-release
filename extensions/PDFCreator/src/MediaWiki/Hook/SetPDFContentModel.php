<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook;

use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;
use MediaWiki\Revision\SlotRoleRegistry;

class SetPDFContentModel implements ContentHandlerDefaultModelForHook, MediaWikiServicesHook {

	/**
	 * @inheritDoc
	 */
	public function onContentHandlerDefaultModelFor( $title, &$model ) {
		$titleNS = $title->getNamespace();
		if ( $titleNS !== NS_MEDIAWIKI ) {
			return true;
		}
		$titleParts = explode( '/', $title->getText() );
		if ( $titleParts[0] !== 'PDFCreator' ) {
			return true;
		}
		if ( count( $titleParts ) !== 2 ) {
			return true;
		}
		$model = 'pdfcreator_template';
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function onMediaWikiServices( $services ) {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function (
				SlotRoleRegistry $registry
			) {
				if ( !$registry->isDefinedRole( 'pdfcreator_template_intro' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_intro', 'pdfcreator_template', $options );
				}
				if ( !$registry->isDefinedRole( 'pdfcreator_template_header' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_header', 'pdfcreator_template', $options );
				}
				if ( !$registry->isDefinedRole( 'pdfcreator_template_body' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_body', 'pdfcreator_template', $options );
				}
				if ( !$registry->isDefinedRole( 'pdfcreator_template_footer' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_footer', 'pdfcreator_template', $options );
				}
				if ( !$registry->isDefinedRole( 'pdfcreator_template_outro' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_outro', 'pdfcreator_template', $options );
				}
				if ( !$registry->isDefinedRole( 'pdfcreator_template_styles' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_styles', CONTENT_MODEL_CSS, $options );
				}
				if ( !$registry->isDefinedRole( 'pdfcreator_template_options' ) ) {
					$options = [ 'display' => 'none' ];
					$registry->defineRoleWithModel( 'pdfcreator_template_options', CONTENT_MODEL_JSON, $options );
				}
			}
		);
	}
}
