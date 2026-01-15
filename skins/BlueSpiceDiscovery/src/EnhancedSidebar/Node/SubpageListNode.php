<?php

namespace BlueSpice\Discovery\EnhancedSidebar\Node;

use BlueSpice\Discovery\SubpageDataGenerator;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use UnexpectedValueException;

class SubpageListNode extends InternalLinkNode {

	/** @var int */
	private $depth;

	/**
	 * @param PermissionManager $permissionManager
	 * @param array $data
	 */
	public function __construct(
		PermissionManager $permissionManager, $data
	) {
		parent::__construct( $permissionManager, $data );
		$this->depth = $data['depth'] ?? 1;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'enhanced-sidebar-subpage-tree';
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return parent::jsonSerialize() + [
			'depth' => $this->depth
		];
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function isHidden( User $user ): bool {
		return parent::isHidden( $user );
	}

	/**
	 * Serialize in format to be consumed by a tree
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function treeSerialize(): array {
		if ( !( $this->target instanceof Title ) ) {
			throw new UnexpectedValueException(
				'Target is not a Title! Calling from an invalid context?'
			);
		}

		$data = parent::treeSerialize();
		return array_merge(
			$data,
			[
				'classes' => $this->getOutputCssClasses(),
				'items' => $this->getSubpages(),
			]
		);
	}

	/**
	 * @return bool
	 */
	public function supportsChildren(): bool {
		// It adds its own children, no children from definition supported
		return false;
	}

	/**
	 *
	 * @return array
	 */
	private function getSubpages(): array {
		if ( $this->target instanceof Title === false ) {
			$services = MediaWikiServices::getInstance();
			$titleFactory = $services->getTitleFactory();
			$this->target = $titleFactory->newFromText( $this->target );
		}

		if ( !$this->target->hasSubpages() ) {
			return [];
		}

		$subpageDataGenerator = new SubpageDataGenerator();
		$subpageDataGenerator->setTreeRootTitle( $this->target );
		$subpageData = $subpageDataGenerator->generate( $this->target, $this->depth );

		return $subpageData;
	}

	/**
	 * @return array|string[]
	 */
	protected function getOutputCssClasses(): array {
		return array_diff( parent::getOutputCssClasses(), [ 'internal', 'new' ] );
	}

	/**
	 * @return string
	 */
	protected function getDisplayText(): string {
		$msg = Message::newFromKey( $this->text );
		if ( $msg->exists() ) {
			return $msg->plain();
		}

		return $this->text;
	}
}
