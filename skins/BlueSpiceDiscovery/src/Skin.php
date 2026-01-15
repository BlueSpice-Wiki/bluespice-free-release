<?php

namespace BlueSpice\Discovery;

use Exception;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Registration\ExtensionRegistry;
use SkinTemplate;

class Skin extends SkinTemplate {
	/**
	 *
	 * @var string
	 */
	public $skinname = 'discovery';

	/**
	 *
	 * @var string
	 */
	public $template = Template::class;

	/**
	 *
	 * @param OutputPage $out
	 */
	private function addStaticResourceModuleStyles( $out ) {
		/** @var IContextSource */
		$context = $this->getContext();

		$services = MediaWikiServices::getInstance();
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );
		$layoutEnabled = $config->get( 'LayoutEnabled' );

		// Layout resource modules
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$layoutEnabled = $config->get( 'LayoutEnabled' );

		$queryValues = $context->getRequest()->getQueryValues();
		if ( array_key_exists( 'skintemplate', $queryValues ) ) {
				$layoutEnabled = $queryValues['skintemplate'];
		}

		$layoutRegistry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoveryLayoutRegistry'
		);

		$layoutSpecs = [];
		if ( isset( $layoutRegistry[$layoutEnabled] ) ) {
			$layoutSpecs = $layoutRegistry[$layoutEnabled];
			if ( isset( $layoutSpecs['factory'] ) && is_array( $layoutSpecs['factory'] ) ) {
				$callback = end( $layoutSpecs['factory'] );
				$layoutSpecs['factory'] = $callback;
			}
			if ( isset( $layoutSpecs['class'] ) && is_array( $layoutSpecs['class'] ) ) {
				$callback = end( $layoutSpecs['class'] );
				$layoutSpecs['class'] = $callback;
			}
			if ( isset( $layoutSpecs['factory'] ) && isset( $layoutSpecs['class'] ) ) {
				unset( $layoutSpecs['factory'] );
			}
		} else {
			throw new Exception(
				'No layout ' . $layoutEnabled . ' registered'
			);
		}

		/** @var ObjectFactory */
		$objectFactory = $services->getService( 'ObjectFactory' );
		/** @var ISkinLayout */
		$skinLayout = $objectFactory->createObject( $layoutSpecs );

		if ( $skinLayout instanceof IResourceProvider ) {
			$styles = $skinLayout->getStyles();
			$scripts = $skinLayout->getScripts();

			if ( !empty( $styles ) ) {
				$out->addModuleStyles( $styles );
			}

			if ( !empty( $scripts ) ) {
				$out->addModules( $scripts );
			}
		}

		// Structure resource modules
		$structureElements = $skinLayout->getSkinStructureElements();
		foreach ( $structureElements as $structureElement ) {
			if ( $structureElement instanceof IResourceProvider ) {
				$styles = $structureElement->getStyles();
				$scripts = $structureElement->getScripts();

				if ( !empty( $styles ) ) {
					$out->addModuleStyles( $styles );
				}

				if ( !empty( $scripts ) ) {
					$out->addModules( $scripts );
				}
			}
		}
	}

	/**
	 * @param OutputPage $out
	 * @return void
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		// Enable responsive behaviour on mobile browsers
		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1' );

		// Use mediawiki resource module for skin
		$out->addModuleStyles( "skin.discovery.styles" );

		// Use bootstrap framework
		$out->addModuleStyles( "skin.discovery.bootstrap.styles" );
		$out->addModules( "skin.discovery.bootstrap.scripts" );

		// Add only ResourceModules for active layout and used structures
		$this->addStaticResourceModuleStyles( $out );
	}

	/**
	 *
	 * @return bool
	 */
	public function isViewMode() {
		if (
			$this->getTitle()->isMainPage() &&
			( $this->getRequest()->getRawVal( 'action' ) ?? 'view' ) === 'view'
		) {
			return true;
		}
		return false;
	}

	/**
	 * Make sure ParserFunctions within `MediaWiki:Sidebar` are evaluated
	 * @param array &$bar
	 * @param string $message
	 */
	public function addToSidebar( &$bar, $message ) {
		if ( $message === 'sidebar' ) {
			$this->addToSidebarPlain( $bar, wfMessage( $message )->inContentLanguage()->text() );
			return;
		}
		parent::addToSidebar( $bar, $message );
	}
}
