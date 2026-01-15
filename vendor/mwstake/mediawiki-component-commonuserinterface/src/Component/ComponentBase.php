<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

abstract class ComponentBase implements IComponent {

	/**
	 *
	 * @var array
	 */
	protected $componentProcessData = [];

	/** @var array */
	protected $options = [];

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLModules(): array {
		return [];
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [];
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return [];
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		return true;
	}

	/**
	 *
	 * @param array $data Arbitrary data to be consumed by the components.
	 *                    Usually this is SkinTemplate's `$tpl->data`
	 * @return void
	 */
	public function setComponentData( $data ): void {
		$this->componentProcessData = $data;
	}
}
