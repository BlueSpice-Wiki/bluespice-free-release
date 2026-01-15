<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\SubpageDataGenerator;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleTreeContainer;

class SubpageTree extends SimpleTreeContainer {

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$services = MediaWikiServices::getInstance();
		$treeDataGenerator = $services->get( 'MWStakeCommonUITreeDataGenerator' );

		$nodes = $treeDataGenerator->generate(
			$this->getTreeData(),
			$this->getTreeExpandPaths()
		);

		return $nodes;
	}

	/**
	 * @return array
	 */
	private function getTreeData(): array {
		/** @var IContextSource */
		$context = RequestContext::getMain();

		/** @var Title */
		$title = $context->getTitle();

		$subpageDataGenerator = new SubpageDataGenerator();
		$subpageDataGenerator->setActiveTitle( $title );
		$subpageData = $subpageDataGenerator->generate( $title );

		return $subpageData;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$title = $context->getTitle();

		if ( !$title->isContentPage() ) {
			return false;
		}

		return true;
	}

	/**
	 * @return array
	 */
	private function getTreeExpandPaths(): array {
		/** @var IContextSource */
		$context = RequestContext::getMain();
		/** @var Title */
		$title = $context->getTitle();

		$titleText = $title->getFullText();
		$titleParts = explode( '/', $titleText );

		$curTitleText = '';
		$path = [];

		for ( $index = 0; $index < count( $titleParts ); $index++ ) {
			$curTitleText .= $titleParts[$index];
			$curTitle = Title::newFromText( $curTitleText );
			$fullId = md5( $curTitle->getFullText() );
			$path[] = substr( $fullId, 0, 6 );

			$curTitleText .= '/';
		}

		$path = array_slice( $path, 1 );

		return [
			implode( '/', $path )
		];
	}

	/**
	 * @return string[]
	 */
	public function getRequiredRLStyles(): array {
		return [ 'skin.discovery.mws-tree-component.styles' ];
	}
}
