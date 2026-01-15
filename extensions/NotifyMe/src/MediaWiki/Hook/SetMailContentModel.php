<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Hook;

use MediaWiki\Context\RequestContext;
use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;
use MediaWiki\Revision\SlotRoleRegistry;
use MediaWiki\Title\Title;

class SetMailContentModel implements ContentHandlerDefaultModelForHook, MediaWikiServicesHook {
	/**
	 * @param Title $title
	 * @param string &$model
	 *
	 * @return bool
	 */
	public function onContentHandlerDefaultModelFor( $title, &$model ) {
		if ( preg_match( '/\.mail$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = 'mail_template';
			return false;
		}
		return true;
	}

	/**
	 * @param MediaWikiServices $services
	 *
	 * @return bool|void
	 */
	public function onMediaWikiServices( $services ) {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function (
				SlotRoleRegistry $registry
			) {
				if ( !$registry->isDefinedRole( 'mail_template_meta' ) ) {
					$options = [ 'display' => 'none' ];
					if ( RequestContext::getMain()->getRequest()->getBool( 'debug' ) ) {
						$options['display'] = 'section';
					}
					$registry->defineRoleWithModel( 'mail_template_meta', CONTENT_MODEL_JSON, $options );
				}
			}
		);
	}

	/**
	 * @param Title $title
	 * @param string &$lang
	 * @param string $model
	 * @param string $format
	 *
	 * @return bool
	 */
	public static function onCodeEditorGetPageLanguage( $title, &$lang, $model, $format ) {
		if ( $model === 'mail_template' ) {
			$lang = 'html';
			return false;
		}

		return true;
	}
}
