<?php

namespace BlueSpice\HideTitle\ResourceLoader;

use BlueSpice\HideTitle\CssSelectorModuleFactory;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;
use MediaWiki\ResourceLoader\FileModule as ResourceLoaderFileModule;

class AddResources extends ResourceLoaderFileModule {

	/**
	 * @inheritDoc
	 */
	public function readStyleFiles( array $styles, ResourceLoaderContext $context ) {
		$rlStyles = parent::readStyleFiles( $styles, $context );

		$factory = new CssSelectorModuleFactory();
		$selectorModules = $factory->getModules( $context );

		if ( empty( $selectorModules ) ) {
			return $rlStyles;
		}

		$hideTitleStyles = [];
		foreach ( $selectorModules as $module ) {
			$moduleSelectors = $module->getSelectors();

			if ( empty( $moduleSelectors ) ) {
				continue;
			}

			foreach ( $moduleSelectors as $media => $selectors ) {
				if ( empty( $selectors ) ) {
					continue;
				}

				if ( !isset( $hideTitleStyles[$media] ) ) {
					$hideTitleStyles[$media] = [];
				}

				$hideTitleStyles[$media] = array_merge( $hideTitleStyles[$media], $selectors );
				$hideTitleStyles[$media] = array_unique( $hideTitleStyles[$media] );
			}
		}

		$hideTitleRlStyles = [];
		foreach ( $hideTitleStyles as $media => $selectors ) {
			$css = implode( ",\n", $selectors );
			$css .= " { display: none; }";

			if ( !isset( $hideTitleRlStyles[$media] ) ) {
				$hideTitleRlStyles[$media] = [];
			}

			$hideTitleRlStyles[$media] = $css;
		}

		$rlStyles = array_merge( $rlStyles, $hideTitleRlStyles );
		return $rlStyles;
	}

}
