<?php

namespace MWStake\MediaWiki\Component\Wikitext\Node;

class Header extends TextNode {
	/** @var int */
	private $level;
	/** @var string */
	private $text;

	/**
	 * @param int $level
	 * @param string $text
	 * @param string $wikitext
	 */
	public function __construct( $level, $text, $wikitext ) {
		$this->level = $level;
		$this->text = $text;
		parent::__construct( $wikitext );
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'header';
	}

	/**
	 * @return int
	 */
	public function getLevel(): int {
		return $this->level;
	}

	/**
	 * @return string
	 */
	public function getHeaderText(): string {
		return $this->text;
	}

	/**
	 * @param int $level
	 */
	public function setLevel( int $level ) {
		if ( $level === $this->level ) {
			return;
		}
		$this->level = $level;
		$levelString = str_pad( '', $level, '=' );
		$this->setData( "$levelString{$this->text}$levelString" );
	}

	/**
	 * @param string $value
	 */
	public function setHeaderText( $value ) {
		if ( $value === $this->text ) {
			return;
		}
		$this->setData(
			preg_replace( "/{$this->text}/", $value, $this->getCurrentData() )
		);
		$this->text = $value;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'type' => $this->getType(),
			'level' => $this->getLevel(),
			'headerText' => $this->getHeaderText(),
			'wikitext' => $this->getCurrentData()
		];
	}
}
