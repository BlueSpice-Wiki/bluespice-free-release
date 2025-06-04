<?php

namespace MediaWiki\Extension\MenuEditor\Node;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class TwoFoldLinkSpec extends MenuNode {
	/** @var TitleFactory */
	private $titleFactory;
	/** @var string */
	private $label;
	/** @var string */
	private $target;

	/**
	 * @param string $target
	 * @param string $label
	 * @param string $originalWikitext
	 * @param TitleFactory $titleFactory
	 * @param int|null $level
	 */
	public function __construct( $target, $label, $originalWikitext, TitleFactory $titleFactory, ?int $level = 2 ) {
		parent::__construct( $level, $originalWikitext );
		$this->titleFactory = $titleFactory;
		$this->target = $target;
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'menu-two-fold-link-spec';
	}

	/**
	 * @return string
	 */
	public function getTarget(): string {
		return $this->target;
	}

	/**
	 * @param string $target
	 * @throws \UnexpectedValueException
	 */
	public function setTarget( string $target ) {
		$this->verifyTarget( $target );
		$this->target = $target;
	}

	/**
	 * @param string $target
	 * @throws \UnexpectedValueException
	 */
	public function verifyTarget( string $target ) {
		if ( !$this->isLink( $target ) && !$this->isValidPageName( $target ) ) {
			throw new \UnexpectedValueException( 'Invalid target: ' . $target );
		}
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}

	/**
	 * @param string $label
	 */
	public function setLabel( string $label ) {
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getCurrentData(): string {
		return "{$this->getLevelString()} {$this->getTarget()}|{$this->getLabel()}";
	}

	/**
	 * @param string $target
	 * @return bool
	 */
	protected function isLink( string $target ) {
		$urlUtils = MediaWikiServices::getInstance()->getUrlUtils();
		return (bool)preg_match( '/^(?i:' . $urlUtils->validProtocols() . ')/', $target );
	}

	/**
	 * @param string $target
	 * @return bool
	 */
	protected function isValidPageName( $target ) {
		$title = $this->titleFactory->newFromText( $target );
		return $title instanceof Title;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'type' => $this->getType(),
			'level' => $this->getLevel(),
			'target' => $this->getTarget(),
			'label' => $this->getLabel(),
			'wikitext' => $this->getCurrentData()
		];
	}
}
