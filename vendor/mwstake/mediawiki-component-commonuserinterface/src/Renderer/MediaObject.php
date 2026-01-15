<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\IMediaObject;

class MediaObject extends RendererBase {

	/**
	 * @return string
	 */
	public function getTemplatePathname(): string {
		return $this->templateBasePath . 'card-img.mustache';
	}

	/**
	 * @inheritDoc
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof IMediaObject ) {
			$templateData = [
				'id' => $component->getId(),
				'src' => $component->getImageSrc(),
				'alt' => $component->getImageAltText()->text()
			];
			if ( !empty( $subComponentNodes ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'body' => $subComponentNodes
					]
				);
			}
			if ( !empty( $component->getContainerClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'container-class' => implode( ' ', $component->getContainerClasses() )
					]
				);
			}
			if ( !empty( $component->getContainerClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'img-class' => implode( ' ', $component->getImageClasses() )
					]
				);
			}
			if ( !empty( $component->getContainerClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'body-class' => implode( ' ', $component->getBodyClasses() )
					]
				);
			}
		} else {
			throw new Exception( "Can not extract data from " . get_class( $component ) );
		}

		return $templateData;
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'id', 'container-class', 'img-class', 'src', 'alt', 'body-class' ];
	}

}
