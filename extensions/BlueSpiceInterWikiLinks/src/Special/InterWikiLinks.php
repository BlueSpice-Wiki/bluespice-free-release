<?php

namespace BlueSpice\InterWikiLinks\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class InterWikiLinks extends OOJSGridSpecialPage {

	public function __construct() {
		parent::__construct( 'InterWikiLinks', 'interwikilinks-viewspecialpage' );
	}

	/**
	 * @inheritDoc
	 */
	public function doExecute( $subPage ) {
		$out = $this->getOutput();
		$out->addModules( [ 'ext.bluespice.interwikilinks.manager' ] );
		$out->addHTML( Html::element( 'div', [
			'id' => 'bs-interwikilinks-manager',
			'data-can-edit' => $this->getUser()->isAllowed( 'wikiadmin' ) ? 1 : 0
		] ) );
	}
}
