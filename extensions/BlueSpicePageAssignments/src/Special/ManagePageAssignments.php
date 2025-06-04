<?php

namespace BlueSpice\PageAssignments\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class ManagePageAssignments extends OOJSGridSpecialPage {

	public function __construct() {
		parent::__construct( 'ManagePageAssignments', 'pageassignments' );
	}

	/**
	 * @inheritDoc
	 */
	public function doExecute( $subPage ) {
		$out = $this->getOutput();
		$out->addModules( [ 'ext.pageassignments.manager' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-pageassignments-manager' ] ) );
	}
}
