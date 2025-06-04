<?php

namespace MediaWiki\Extension\MenuEditor\Node;

class Keyword extends MenuNode {
	/** @var string */
	private $keyword;

	/**
	 * @param int $level
	 * @param string $keyword
	 * @param string|null $originalWikitext
	 */
	public function __construct( int $level, $keyword, $originalWikitext = null ) {
		parent::__construct( $level, $originalWikitext );
		$this->keyword = $keyword;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'menu-keyword';
	}

	/**
	 * @param string $keyword
	 */
	public function setKeyword( string $keyword ) {
		if ( !$this->keywordSupported( $keyword ) ) {
			throw new \UnexpectedValueException( 'Unsupported keyword: ' . $keyword );
		}
		$this->keyword = $keyword;
	}

	/**
	 * @return string
	 */
	public function getKeyword(): string {
		return $this->keyword;
	}

	/**
	 * @return string
	 */
	public function getCurrentData(): string {
		return "{$this->getLevelString()} {$this->getKeyword()}";
	}

	/**
	 * @param string $keyword
	 * @return bool
	 */
	private function keywordSupported( $keyword ): bool {
		return true;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'type' => $this->getType(),
			'level' => $this->getLevel(),
			'keyword' => $this->getKeyword(),
			'wikitext' => $this->getCurrentData()
		];
	}
}
