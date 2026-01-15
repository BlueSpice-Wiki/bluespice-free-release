<?php

namespace BlueSpice\PageAssignments\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class PageAssignments extends SpecialPage {

	public function __construct() {
		parent::__construct( 'PageAssignments' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$out->addModules( [ 'ext.pageassignments.overview' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-pageassignments-overview' ] ) );
	}
}
