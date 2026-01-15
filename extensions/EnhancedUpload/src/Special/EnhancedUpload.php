<?php

namespace MediaWiki\Extension\EnhancedUpload\Special;

use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\SpecialPage\SpecialPage;

class EnhancedUpload extends SpecialPage {

	/** @var TemplateParser */
	protected $templateParser;

	public function __construct() {
		parent::__construct( 'EnhancedUpload', 'upload' );

		$this->templateParser = new TemplateParser(
			dirname( __DIR__, 2 ) . '/resources/templates'
		);
	}

	/**
	 *
	 * @param string $par
	 */
	public function execute( $par ) {
		$this->setHeaders();

		$this->checkPermissions();
		$this->getOutput()->enableOOUI();
		$this->buildSkeleton();
		$this->getOutput()->addModules( "ext.enhancedUpload" );

		$this->getOutput()->addHTML(
			Html::element( 'div', [
				'id' => 'enhancedUpload-container'
			] )
		);
	}

	/**
	 *
	 * @return void
	 */
	protected function buildSkeleton() {
		$this->getOutput()->addModuleStyles( [ 'ext.enhancedUpload.special.skeleton.styles' ] );
		$skeleton = $this->templateParser->processTemplate(
			'upload-skeleton',
			[]
		);
		$skeletonCnt = Html::openElement( 'div', [
			'id' => 'enhancedUpload-skeleton-cnt'
		] );
		$skeletonCnt .= $skeleton;
		$skeletonCnt .= Html::closeElement( 'div' );
		$this->getOutput()->addHTML( $skeletonCnt );
	}

	/**
	 * @inheritDoc
	 */
	protected function getGroupName() {
		return 'media';
	}
}
