<?php

namespace MWStake\MediaWiki\Component\Utils;

use Config;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Permissions\Authority;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\User\UserGroupManager;
use MediaWiki\User\UserIdentity;

class ReadableNamespaces {

	/**
	 * @var array|null
	 */
	private ?array $allNamespaces = null;

	/** @var array */
	private array $namespacePermissionsCache = [];

	/** @var array */
	private array $userGroupCache = [];

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param Config $config
	 * @param HookContainer $hookContainer
	 * @param UserGroupManager $userGroupManager
	 */
	public function __construct(
		private readonly NamespaceInfo $namespaceInfo,
		private readonly Config $config,
		private readonly HookContainer $hookContainer,
		private readonly UserGroupManager $userGroupManager
	) {
	}

	/**
	 * @param Authority $actor
	 * @return array
	 */
	public function getReadableNamespaces( Authority $actor ): array {
		$this->assertNamespaces();

		$groups = $this->getUserGroups( $actor->getUser() );
		$this->assertNamespacePermissions();
		$readable = [];
		foreach ( $this->allNamespaces as $ns ) {
			if ( array_intersect( $groups, $this->namespacePermissionsCache[$ns] ?? [] ) ) {
				$readable[] = $ns;
			}
		}

		return $readable;
	}

	/**
	 * @param Authority $actor
	 * @return array
	 */
	public function getRestrictedNamespaces( Authority $actor ): array {
		$this->assertNamespaces();
		return array_values( array_diff( $this->allNamespaces ?? [], $this->getReadableNamespaces( $actor ) ) );
	}

	/**
	 * @return void
	 */
	private function assertNamespacePermissions(): void {
		if ( $this->namespacePermissionsCache === [] ) {
			$namespacePermissions = [];
			$hookRes = $this->hookContainer->run(
				'MWStakeUtilsGetReadableNamespaces', [ $this->allNamespaces, &$namespacePermissions ]
			);
			if ( !$hookRes ) {
				$this->namespacePermissionsCache = $namespacePermissions;
				return;
			}
			$this->namespacePermissionsCache = $namespacePermissions;

			$groupPermissions = $this->config->get( 'GroupPermissions' );
			$readerGroups = [];
			foreach ( $groupPermissions as $group => $permissions ) {
				foreach ( $permissions as $permission => $value ) {
					if ( $permission === 'read' ) {
						$readerGroups[] = $group;
					}
				}
			}
			foreach ( $this->allNamespaces as $ns ) {
				if ( !isset( $this->namespacePermissionsCache[$ns] ) ) {
					$this->namespacePermissionsCache[$ns] = $readerGroups;
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private function assertNamespaces() {
		if ( $this->allNamespaces === null ) {
			$this->allNamespaces = $this->namespaceInfo->getValidNamespaces();
			// Remove all < 0 namespaces
			$this->allNamespaces = array_filter( $this->allNamespaces, static function ( $ns ) {
				return $ns >= 0;
			} );
		}
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	private function getUserGroups( UserIdentity $user ): array {
		$groups = $this->userGroupCache[$user->getId()] ?? null;
		if ( $groups === null ) {
			$groups = array_merge(
				$this->userGroupManager->getUserGroups( $user ),
				$this->userGroupManager->getUserImplicitGroups( $user )
			);
			$this->userGroupCache[$user->getId()] = $groups;
		}
		return $groups;
	}

}
