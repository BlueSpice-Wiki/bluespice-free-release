<?php

namespace MediaWiki\Extension\FlexiSkin\Special;

use MediaWiki\Extension\FlexiSkin\IFlexiSkinManager;
use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;

class FlexiSkin extends SpecialPage {

	/** @var TemplateParser */
	protected $templateParser;

	public function __construct() {
		parent::__construct( 'FlexiSkin', 'flexiskin-viewspecialpage', true );

		$this->templateParser = new TemplateParser(
			dirname( __DIR__, 2 ) . '/resources/templates'
		);
	}

	/**
	 *
	 * @param string $par
	 * @return void
	 */
	public function execute( $par ) {
		parent::execute( $par );

		$this->setHeaders();
		$this->getOutput()->enableOOUI();
		$this->buildSkeleton();
		$this->getOutput()->addModuleStyles( [ 'ext.flexiskin.specialpage.styles' ] );
		$this->getOutput()->addModules( [ 'ext.flexiskin.specialpage.scripts' ] );
		$this->getOutput()->addHTML( Html::element( 'div', [ 'id' => 'fs-container' ] ) );

		/** @var IFlexiSkinManager $manager */
		$manager = MediaWikiServices::getInstance()->getService( 'FlexiSkinManager' );
		$flexiSkin = $manager->getFlexiSkin();
		$skinName = $this->getSkin()->getSkinName();
		if ( $flexiSkin === null ) {
			$flexiSkin = $manager->create( $skinName, [] );
		}
		$config = $flexiSkin->getConfig();

		foreach ( $manager->getPlugins() as $key => $plugin ) {
			$plugin->adaptConfiguration( $config );
			$plugin->setDefaults( $flexiSkin, $config );
		}
		$adaptedSkin = new \MediaWiki\Extension\FlexiSkin\FlexiSkin(
			$flexiSkin->getId(),
			$flexiSkin->getName(),
			$config,
			$flexiSkin->isActive()
		);

		$this->getOutput()->addJsConfigVars( 'wgFlexiSkin', $adaptedSkin );
		$this->getOutput()->addJsConfigVars(
			'wgFlexiSkinColorPresets', $this->getConfig()->get( 'FlexiSkinColorPresets' )
		);
	}

	/**
	 *
	 * @return void
	 */
	protected function buildSkeleton() {
		$skeleton = $this->templateParser->processTemplate(
			'flexiskin-skeleton',
			[]
		);
		$skeletonCnt = Html::openElement( 'div', [
			'id' => 'fs-skeleton-cnt'
		] );
		$skeletonCnt .= $skeleton;
		$skeletonCnt .= Html::closeElement( 'div' );
		$this->getOutput()->addHTML( $skeletonCnt );
	}

	/**
	 * @return string groupname
	 */
	protected function getGroupName() {
		return 'other';
	}
}
