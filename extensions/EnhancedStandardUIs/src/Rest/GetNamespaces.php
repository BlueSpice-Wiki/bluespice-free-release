<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use Wikimedia\Rdbms\LoadBalancer;

class GetNamespaces extends SimpleHandler {

	private TitleFactory $titleFactory;
	private PermissionManager $permissionManager;
	private LoadBalancer $loadBalancer;
	private NamespaceInfo $namespaceInfo;
	private LanguageFactory $languageFactory;

	/**
	 *
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 * @param LoadBalancer $loadBalancer
	 * @param NamespaceInfo $namespaceInfo
	 * @param LanguageFactory $languageFactory
	 */
	public function __construct( TitleFactory $titleFactory, PermissionManager $permissionManager,
		LoadBalancer $loadBalancer, NamespaceInfo $namespaceInfo, LanguageFactory $languageFactory
	) {
		$this->titleFactory = $titleFactory;
		$this->permissionManager = $permissionManager;
		$this->loadBalancer = $loadBalancer;
		$this->namespaceInfo = $namespaceInfo;
		$this->languageFactory = $languageFactory;
	}

	public function run() {
		$pageCounts = $this->getNamespacePageCount();
		$context = RequestContext::getMain();
		$user = $context->getUser();

		$namespaces = [];
		$langCode = $context->getLanguage();
		$lang = $this->languageFactory->getLanguage( $langCode );

		foreach ( $lang->getFormattedNamespaces() as $ns => $title ) {
			if ( $ns < 0 ) {
				continue;
			}
			$testTitle = $this->titleFactory->newFromText( 'Enhanced_DummyPage', $ns );
			if ( !$this->permissionManager->userCan( 'read', $user, $testTitle ) ) {
				continue;
			}
			$namespaces[] = [
				'id' => $ns,
				'name' => $title,
				'isContent' => $this->namespaceInfo->isContent( $ns ),
				'isTalk' => $this->namespaceInfo->isTalk( $ns ),
				'pageCount' => (int)( $pageCounts[$ns] ?? 0 )
			];
		}
		usort( $namespaces, static function ( $a, $b ) {
			return $a[ 'name' ] <=> $b[ 'name' ];
		} );

		return $this->getResponseFactory()->createJson( [ 'namespaces' => $namespaces ] );
	}

	/**
	 * @return array
	 */
	private function getNamespacePageCount() {
		$pageCounts = [];
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'page',
			[
				'page_namespace',
				'COUNT(*) AS count'
			],
			[
				'page_is_redirect' => 0
			],
			__METHOD__,
			[
				'GROUP BY' => 'page_namespace'
			]
		);
		foreach ( $res as $row ) {
			$pageCounts[(int)$row->page_namespace] = $row->count;
		}
		return $pageCounts;
	}
}
