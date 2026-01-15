<?php

namespace BlueSpice\VisualEditorConnector\Hook;

use MediaWiki\Api\Hook\APIAfterExecuteHook;
use MediaWiki\Extension\VisualEditor\ApiVisualEditor;

class RemoveTocPlaceholder implements APIAfterExecuteHook {

	/**
	 * @inheritDoc
	 */
	public function onAPIAfterExecute( $module ) {
		if ( !( $module instanceof ApiVisualEditor ) ) {
			return;
		}
		$result = $module->getResult();
		$content = $result->getResultData( [ 'visualeditor', 'content' ] );
		if ( !$content ) {
			return;
		}
		$content = preg_replace(
			'/<meta\\b[^>]*\\bproperty\\s*=\\s*"mw:PageProp\\/toc"[^>]*>/',
			'',
			$content
		);
		// Parser inserts a placeholder at the point where TOC should go
		// In VE, if TOC comes from a template, this insertion breaks the model
		// and prevents VE from recognizing whole template content as a translusion
		// Since in VE, TOC placeholder is never replaced anyways, we can remove it
		$result->removeValue( [ 'visualeditor' ], 'content' );
		$result->addValue(
			[ 'visualeditor' ],
			'content',
			$content
		);
	}
}
