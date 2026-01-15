<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MediaWiki\Permissions\PermissionManager;
use Title;
use TitleFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class CategoryValue extends TitleValue {

	/** @var array|null */
	protected ?array $allowedNamespaces = [ NS_CATEGORY ];

	/** @var ILoadBalancer */
	protected ILoadBalancer $lb;

	/**
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 * @param ILoadBalancer $lb
	 */
	public function __construct( TitleFactory $titleFactory, PermissionManager $permissionManager, ILoadBalancer $lb ) {
		parent::__construct( $titleFactory, $permissionManager );
		$this->lb = $lb;
	}

	/**
	 * @inheritDoc
	 */
	public function setAllowedNamespaces( ?array $namespaces ): static {
		// NOOP
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setBlacklistedNamespace( ?array $namespaces ): static {
		// NOOP
		return $this;
	}

	/**
	 * @param mixed $value
	 * @return Title|null
	 */
	protected function getTitle( mixed $value ): ?Title {
		$value = (string)$value;
		$title = $this->titleFactory->newFromText( $value );
		if ( $title->getNamespace() !== NS_CATEGORY ) {
			if ( $title->getNamespace() !== NS_MAIN ) {
				// Title in another namespace
				return null;
			}
			$title = $this->titleFactory->makeTitle( NS_CATEGORY, $title->getDBkey() );
		}

		return $title;
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	protected function titleExists( Title $title ): bool {
		return $title->exists() || (bool)$this->lb->getConnection( DB_REPLICA )->selectField(
			'categorylinks',
			'cl_from',
			[ 'cl_to' => $title->getDBkey() ],
			__METHOD__
		);
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'category',
		] );
	}
}
