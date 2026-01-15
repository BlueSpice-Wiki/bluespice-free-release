<?php

namespace BlueSpice\Discovery\TemplateDataProvider;

use BaseTemplate;
use BlueSpice\Discovery\ITemplateDataProvider;
use MediaWiki\Context\RequestContext;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Page\PageProps;

class TemplateDataProvider implements ITemplateDataProvider {

	/**
	 *
	 * @var array
	 */
	private $sidebar = [];

	/**
	 *
	 * @var array
	 */
	private $content_navigation = [];

	/**
	 *
	 * @var array
	 */
	private $managedLinks = [];
	/**
	 * @var HookContainer
	 */
	private $hookContainer;
	/**
	 * @var array
	 */
	private $personal_urls;

	/** @var PageProps */
	private $pageProps;

	/**
	 * @param HookContainer $hookContainer
	 * @param PageProps $pageProps
	 */
	public function __construct( HookContainer $hookContainer, PageProps $pageProps ) {
		$this->hookContainer = $hookContainer;
		$this->pageProps = $pageProps;
	}

	/**
	 *
	 * @param BaseTemplate $template
	 */
	public function init( $template ): void {
		$this->content_navigation = $template->get( 'content_navigation' );
		$this->sidebar = $template->get( 'sidebar' );
		$this->personal_urls = $template->get( 'personal_urls' );
		$this->managedLinks = [
			'panel' => $this->collectPanelLinks(),
			'actioncollection' => $this->collectActionLinks(),
		];
		$this->manageLinks();

		$this->hookContainer->run( 'BlueSpiceDiscoveryTemplateDataProviderAfterInit', [ $this ] );
	}

	/**
	 *
	 * @param string $group
	 * @param string $id
	 * @return void
	 */
	public function register( $group, $id ): void {
		$groupKeys = $this->getRegistryGroupKeys( $group );
		if ( empty( $groupKeys ) || $groupKeys['path'] === 'template' ) {
			return;
		}
		$groupPath = $groupKeys['path'];
		$groupName = $groupKeys['name'];
		$this->buildGroupPath( $groupPath, $groupName );
		if ( !array_key_exists( $id, $this->managedLinks[$groupPath]['toolbox'] ) ) {
			return;
		}
		$this->managedLinks[$groupPath][$groupName] = array_merge(
			$this->managedLinks[$groupPath][$groupName],
			[
				$id => $this->managedLinks[$groupPath]['toolbox'][$id]
			]
		);
		unset( $this->managedLinks[$groupPath]['toolbox'][$id] );
	}

	/**
	 *
	 * @param string $group
	 * @param string $id
	 * @return void
	 */
	public function unregister( $group, $id ): void {
		$groupKeys = $this->getRegistryGroupKeys( $group );
		if ( empty( $groupKeys ) || $groupKeys['path'] === 'template' ) {
			return;
		}
		$groupPath = $groupKeys['path'];
		$groupName = $groupKeys['name'];

		if ( $groupName === 'toolbox' ) {
			// Toolbox is colleciting all unmanaged links
			return;
		}
		$this->buildGroupPath( $groupPath, $groupName );
		if ( isset( $this->managedLinks[$groupPath][$groupName][$id] ) ) {
			$this->managedLinks[$groupPath]['toolbox'][$id] = $this->managedLinks[$groupPath][$groupName][$id];
			unset( $this->managedLinks[$groupPath][$groupName][$id] );
		}
	}

	/**
	 *
	 * @param string $group
	 * @param string $id
	 * @return void
	 */
	public function delete( $group, $id ): void {
		$groupKeys = $this->getRegistryGroupKeys( $group );
		if ( empty( $groupKeys ) || $groupKeys['path'] === 'template' ) {
			return;
		}

		$groupPath = $groupKeys['path'];
		$groupName = $groupKeys['name'];

		if ( isset( $this->managedLinks[$groupPath][$groupName][$id] ) ) {
			unset( $this->managedLinks[$groupPath][$groupName][$id] );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAll(): array {
		$allLinks = $this->managedLinks;
		$allLinks = array_merge(
			$allLinks,
			[
				'template' => $this->collectTemplateLinks()
			]
		);

		return $allLinks;
	}

	/**
	 *
	 * @return void
	 */
	private function manageLinks(): void {
		$this->makePanelWatch();
		$this->makePanelShare();
		$this->makePanelPrint();
		$this->makePanelEdit();
		$this->makePanelViews();
		$this->makePanelActionsPrimary();
		$this->makePanelActionsSecondary();
		$this->makePanelDetails();
		$this->makePanelVariants();
		$this->makePanelCreate();
		$this->makePanelNamespaces();
		$this->makeActionsCollectionViews();
		$this->makeActionsCollectionNamespaces();
		$this->makeActionsCollectionContentActions();
	}

	/**
	 *
	 * @return array
	 */
	private function collectTemplateLinks(): array {
		$templateLinks = [
			'views' => $this->content_navigation['views'] ?? [],
			'actions' => $this->content_navigation['actions'] ?? [],
			'variants' => $this->content_navigation['variants'] ?? [],
			'sidebar' => $this->sidebar,
			'personal_urls' => $this->personal_urls,
			'namespaces' => $this->content_navigation['namespaces'] ?? [],
		];
		return $templateLinks;
	}

	/**
	 *
	 * @return array
	 */
	private function collectPanelLinks(): array {
		$templateLinks = $this->collectActionLinks();
		$sidebar = $this->sidebar;
		if ( array_key_exists( 'LANGUAGES', $sidebar ) ) {
			$templateLinks = array_merge(
				$templateLinks,
				[
					'languages' => $sidebar['LANGUAGES']
				]
			);
			unset( $sidebar['LANGUAGES'] );
		}
		if ( array_key_exists( 'TOOLBOX', $sidebar ) ) {
			unset( $sidebar['TOOLBOX'] );
		}
		$templateLinks = array_merge(
			$templateLinks,
			[
				'sidebar' => $sidebar
			]
		);
		return $templateLinks;
	}

	/**
	 *
	 * @return array
	 */
	private function collectActionLinks(): array {
		$actionLinks = array_merge(
			$this->content_navigation['views'],
			$this->content_navigation['actions'],
			$this->content_navigation['variants'],
			$this->content_navigation['namespaces']
		);

		if ( array_key_exists( 'TOOLBOX', $this->sidebar ) ) {
			$actionLinks = array_merge(
				$actionLinks,
				$this->sidebar['TOOLBOX']
			);
		}

		foreach ( $actionLinks as $key => $link ) {
			if ( !isset( $link['id'] ) ) {
				unset( $actionLinks[$key] );
			}
		}

		$links = [];
		foreach ( $actionLinks as $link ) {
			$id = $link['id'];
			$links[$id] = $link;
		}
		return [
			'toolbox' => $links
		];
	}

	/**
	 *
	 * @param string $group
	 * @return void
	 */
	private function getRegistryGroupKeys( $group ): array {
		$group = trim( $group, "/\x20\t\n\r\0\x0B" );
		$groupParts = explode( '/', $group );
		if ( count( $groupParts ) > 2 ) {
			[];
		}
		$groupName = array_pop( $groupParts );
		$groupPath = array_pop( $groupParts );
		if ( empty( $groupPath ) ) {
			$groupPath = 'panel';
		}

		return [
			'path' => $groupPath,
			'name' => $groupName
		];
	}

	/**
	 *
	 * @param string $key
	 * @param array &$array
	 * @return void
	 */
	private function ensureArrayKey( $key, &$array ): void {
		if ( !array_key_exists( $key, $array ) ) {
			$array = array_merge(
				$array,
				[
					$key => []
				]
			);
		}
	}

	/**
	 *
	 * @param mixed $groupPath
	 * @param mixed $groupName
	 * @return void
	 */
	private function buildGroupPath( $groupPath, $groupName ): void {
		$this->ensureArrayKey( $groupPath, $this->managedLinks );
		$this->ensureArrayKey( $groupName, $this->managedLinks[$groupPath] );
		$this->ensureArrayKey( 'toolbox', $this->managedLinks[$groupPath] );
	}

	/**
	 *
	 * @param string $groupPath
	 * @param array $idList
	 * @return void
	 */
	private function registerLinks( $groupPath, $idList ): void {
		foreach ( $idList as $id ) {
			$this->register( $groupPath, $id );
		}
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelWatch(): void {
		$idList = [ 'ca-watch', 'ca-unwatch' ];
		$this->registerLinks( 'panel/watch', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelShare(): void {
		$idList = [ 't-permalink', 't-sharebymail' ];
		$this->registerLinks( 'panel/share', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelPrint(): void {
		/** We don't want to show a print link - ERM36321 */
		$this->delete( 'actioncollection/toolbox', 't-print' );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelEdit(): void {
		$idList = [ 'ca-edit', 'ca-ve-edit' ];
		$title = RequestContext::getMain()->getTitle();
		if ( $title && $title->isContentPage() ) {
			$newSectionProps = $this->pageProps->getProperties( $title, 'newsectionlink' );
			if ( isset( $newSectionProps[$title->getId()] ) ) {
				$idList[] = 'ca-new-section';
			}
		}

		$this->registerLinks( 'panel/edit', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelViews(): void {
		$idList = [ 'ca-view' ];
		$this->registerLinks( 'panel/views', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelActionsPrimary(): void {
		$idList = [ 'ca-move', 'ca-delete', 'ca-purge' ];
		$this->registerLinks( 'panel/actions_primary', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelActionsSecondary(): void {
		$idList = [];
		$this->registerLinks( 'panel/actions_secondary', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelDetails(): void {
		$idList = [ 'ca-history', 't-info' ];
		$this->registerLinks( 'panel/details', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelVariants(): void {
		$idList = [];
		$this->registerLinks( 'panel/vaiants', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelCreate(): void {
		$idList = [ 'ca-new-page', 'ca-new-subpage' ];
		$this->registerLinks( 'panel/create', $idList );
	}

	/**
	 *
	 * @return void
	 */
	private function makePanelNamespaces(): void {
		foreach ( $this->content_navigation['namespaces'] as $key => $link ) {
			$this->register( 'panel/namespaces', $link['id'] );
		}
	}

	/**
	 *
	 * @return void
	 */
	private function makeActionsCollectionViews(): void {
		foreach ( $this->content_navigation['views'] as $key => $link ) {
			$this->register( 'actioncollection/views', $link['id'] );
		}
	}

	/**
	 *
	 * @return void
	 */
	private function makeActionsCollectionNamespaces(): void {
		foreach ( $this->content_navigation['namespaces'] as $key => $link ) {
			$this->register( 'actioncollection/namespaces', $link['id'] );
		}
		$this->register( 'actioncollection/namespaces', 'ca-new-section' );
	}

	/**
	 *
	 * @return void
	 */
	private function makeActionsCollectionContentActions(): void {
		foreach ( $this->content_navigation['actions'] as $key => $link ) {
			$this->register( 'actioncollection/actions', $link['id'] );
		}
		$this->unregister( 'actioncollection/actions', 'ca-readers' );
		$this->unregister( 'actioncollection/actions', 'ca-sharebymail' );
	}
}
