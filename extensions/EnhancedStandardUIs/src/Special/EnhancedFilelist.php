<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class EnhancedFilelist extends OOJSGridSpecialPage {

	/**
	 *
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct( 'EnhancedFilelist' );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function doExecute( $subPage ) {
		$out = $this->getOutput();
		$out->getMetadata()->setPreventClickjacking( false );
		$out->addModules( [ 'ext.enhancedstandarduis.special.filelist' ] );

		$html = Html::element(
			'div',
			[
				'id' => 'enhanced-ui-filelist-cnt'
			]
		);

		$out->addHTML( $html );
	}

}
