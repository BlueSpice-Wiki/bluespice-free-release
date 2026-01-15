<?php

namespace BlueSpice\Discovery;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

class SubpageDataGenerator {

	/** @var Title|null */
	private $activeTitle = null;

	/** @var Title|null */
	private $treeRootTitle = null;

	/**
	 * @param Title $title
	 * @return void
	 */
	public function setActiveTitle( Title $title ): void {
		$this->activeTitle = $title;
	}

	/**
	 * Sets the root title for the subpage tree.
	 *
	 * When provided, this title becomes the anchor for the tree generated.
	 * Only subpages of this title will be included in the output.
	 *
	 * @param Title $title
	 * @return void
	 */
	public function setTreeRootTitle( Title $title ): void {
		$this->treeRootTitle = $title;
	}

	/**
	 * Generates a subpage tree.
	 *
	 * If $this->treeRootTitle is set, subpages of that title are used.
	 * Otherwise, the root title of the given $title is used.
	 *
	 * @param Title $title
	 * @return array
	 */
	public function generate( Title $title, int $maxDepth = 6 ): array {
		$rootTitle = $this->treeRootTitle ?: $title->getRootTitle();
		$rootParts = explode( '/', $rootTitle->getDBkey() );
		$rootDepth = count( $rootParts );

		$subpageTitles = $rootTitle->getSubpages();

		$subpages = [];
		foreach ( $subpageTitles as $subpageTitle ) {
			$subParts = explode( '/', $subpageTitle->getDBkey() );
			$currentDepth = count( $subParts );

			if ( $currentDepth - $rootDepth > $maxDepth ) {
				// too deep
				continue;
			}

			$subpagePath = $this->makeSubpageData( $subpageTitle, $rootDepth, $maxDepth );
			$this->buildSubpageTreeData( $subpagePath, $subpages, $maxDepth );
		}

		return $this->clearRootIndex( $subpages );
	}

	/**
	 * @param array $subpagePath
	 * @param array &$subpages
	 * @param int $maxDepth
	 */
	private function buildSubpageTreeData( array $subpagePath, array &$subpages, int $maxDepth ) {
		$subPageDepth = 0;
		foreach ( $subpagePath as $key => $value ) {
			if ( !isset( $subpages[$key] ) ) {
				$subpages[$key] = $value;
			}

			$subPageDepth++;

			if ( $subPageDepth >= count( $subpagePath ) ) {
				unset( $subpages[$key]['items'] );
				break;
			}

			if ( $subPageDepth >= $maxDepth ) {
				unset( $subpages[$key]['items'] );
				break;
			}

			$subpages = &$subpages[$key]['items'];
		}
	}

	/**
	 * @param Title $title
	 * @param int $minDepth
	 * @param int $maxDepth
	 * @return array
	 */
	private function makeSubpageData( Title $title, int $minDepth, int $maxDepth ): array {
		$list = [];

		$services = MediaWikiServices::getInstance();
		$pageProps = $services->getPageProps();

		$namespace = $title->getNamespace();
		$titleText = $title->getText();
		$titleParts = explode( '/', $titleText );

		$curTitleText = '';
		for ( $index = 0; $index < count( $titleParts ); $index++ ) {
			if ( $index > $maxDepth ) {
				break;
			}

			$titlePart = $titleParts[$index];
			$curTitleText .= $titlePart;

			if ( $index === 0 ) {
				$curTitleText .= '/';
				continue;
			}

			if ( $index < $minDepth ) {
				$curTitleText .= '/';
				continue;
			}

			$curTitle = Title::makeTitle( $namespace, $curTitleText );

			if ( $this->treeRootTitle ) {
				$curDBKey = $curTitle->getDBkey();
				$rootTitleDBKey = $this->treeRootTitle->getDBKey();
				if ( substr( $curDBKey, 0, strlen( $rootTitleDBKey ) ) !== $rootTitleDBKey ) {
					$curTitleText .= '/';
					continue;
				}
			}

			$fullId = md5( $curTitle->getFullText() );
			$id = substr( $fullId, 0, 6 );

			$text = $titleParts[$index];
			$pageId = $curTitle->getId();
			$displayTitleProps = $pageProps->getProperties( $curTitle, 'displaytitle' );

			if ( isset( $displayTitleProps[$pageId] ) ) {
				$text = $displayTitleProps[$pageId];
			}

			$key = $curTitle->getDBkey();
			$list[$key] = [
				'id' => $id,
				'name' => $curTitle->getPrefixedDBkey(),
				'text' => $text,
				'href' => $curTitle->getLocalURL(),
				'items' => []
			];

			$classes = [];
			if ( !$curTitle->exists() ) {
				$classes[] = 'new';

				$titleText = Message::newFromKey(
					'bs-discovery-page-does-not-exist-title',
					$curTitle->getPrefixedText()
				);
				$list[$key]['title'] = $titleText->text();
			}

			if ( $this->activeTitle && $curTitle->equals( $this->activeTitle ) ) {
				$classes[] = 'active';
			}

			if ( !empty( $classes ) ) {
				$list[$key]['classes'] = $classes;
			}

			$curTitleText .= '/';
		}

		return $list;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function clearRootIndex( array $data ): array {
		$indexedData = [];
		foreach ( $data as $key => $value ) {
			if ( isset( $value['items'] ) ) {
				$value['items'] = $this->clearRootIndex( $value['items'] );
			}
			$indexedData[] = $value;
		}

		return $indexedData;
	}
}
