<?php

namespace MediaWiki\Extension\MenuEditor\Node;

class GenericKeyword extends Keyword {

	/**
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * @param string $type
	 * @param int $level
	 * @param string $keyword
	 * @param string|null $originalWikitext
	 */
	public function __construct( string $type, int $level, $keyword, $originalWikitext = null ) {
		parent::__construct( $level, $keyword, $originalWikitext );
		$this->type = $type;
	}

		/**
		 * @return string
		 */
	public function getType(): string {
		return $this->type;
	}

}
