<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;

class AsyncSubpageTreePanel extends SimpleCard {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'subpage-tree-pnl';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		return [ 'w-100', 'bg-transp' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$id = $this->getId();
		$headerText = wfMessage( 'bs-discovery-subpage-tree-pnl-header-text' )->text();

		return [
			new SimpleCardHeader( [
				'id' => $id . '-head',
				'classes' => [ 'menu-title' ],
				'items' => [
					new Literal(
						$id . '-head',
						$headerText
					)
				]
			] ),
			new AsyncSubpageTree( 'async-subpage-tree', '' )
		];
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
}
