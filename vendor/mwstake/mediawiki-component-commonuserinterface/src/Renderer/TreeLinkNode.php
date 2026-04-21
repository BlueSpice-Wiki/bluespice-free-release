<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\DataAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITreeLinkNode;

class TreeLinkNode extends TreeTextNode {

	/**
	 *
	 * @var MainConfig
	 */
	private $mainConfig = null;

	/**
	 *
	 * @param MainConfig $mainConfig
	 */
	public function __construct( $mainConfig ) {
		parent::__construct();

		$this->mainConfig = $mainConfig;
	}

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof ITreeLinkNode;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IComponent $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof ITreeLinkNode ) {
			$id = $component->getId();
			$templateData = [
				'id' => $id,
				'role' => $component->getRole(),
				'text' => $component->getText()->text(),
				'href' => $component->getHref(),
				'labelId' => "$id-label",
				'preHtml' => $component->getPreHtml(),
				'isLeaf' => $component->getIsLeaf()
			];

			$title = $component->getTitle()->text();
			if ( $title !== '' ) {
				$templateData['title'] = $title;
			}

			$this->getClasses( $component, $templateData );
			$this->getChildren( $component, $subComponentNodes, $templateData );
			$this->getData( $component, $templateData );

			// Is target external?
			$parsedUrl = wfParseUrl( $component->getHref() );
			// MediaWiki global $wgExternalLinkTarget
			$externalLinkTarget = $this->mainConfig->get( 'ExternalLinkTarget' );
			if ( $parsedUrl && $externalLinkTarget ) {
				$templateData = array_merge(
					$templateData,
					[
						'target' => $externalLinkTarget
					]
				);
				// See https://www.mediawiki.org/wiki/Manual:$wgExternalLinkTarget
				$rel = [ 'external', 'noreferrer', 'noopener' ];
				if ( isset( $templateData['rel'] ) ) {
					$componentRel = implode( ' ', $templateData['rel'] );
					$rel = array_merge( $componentRel, $rel );
				}
				$templateData['rel'] = implode( ' ', $rel );
			}
		} else {
			throw new Exception( "Can not extract data from " . get_class( $component ) );
		}

		return $templateData;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @return string
	 */
	public function getTemplatePathname(): string {
		return $this->templateBasePath . '/tree-link-node.mustache';
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'text', 'data' ];
	}

	/**
	 * @param ITreeLinkNode $component
	 * @param array &$templateData
	 * @return void
	 */
	protected function getData( $component, array &$templateData ) {
		$data = $component->getData();
		if ( empty( $data ) ) {
			return;
		}
		$dataAttributesBuilder = new DataAttributesBuilder();
		$dataLinks = $dataAttributesBuilder->toString( $data );
		$templateData['data'] = $dataLinks;
	}

}
