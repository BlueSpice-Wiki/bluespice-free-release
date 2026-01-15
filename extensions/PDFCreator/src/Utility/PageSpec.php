<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class PageSpec {

	/** @var string */
	private $type = '';

	/** @var string */
	private $label = '';

	/** @var string */
	private $prefixedDBKey = null;

	/** @var int */
	private $revisionId;

	/** @var array */
	private $params;

	/**
	 * @param string $type
	 * @param string $label
	 * @param string|null $prefixedDBKey
	 * @param int|null $revisionId
	 * @param array $params
	 */
	public function __construct( string $type, string $label = '',
		?string $prefixedDBKey = '', ?int $revisionId = null, $params = [] ) {
		$this->type = $type;
		$this->label = $label;
		if ( $prefixedDBKey === null || $prefixedDBKey === '' ) {
			$this->prefixedDBKey = null;
		} else {
			$this->prefixedDBKey = $prefixedDBKey;
		}
		$this->revisionId = $revisionId;
		$this->params = $params;
	}

	/**
	 * Type of content to get the HtmlProvider.
	 * Value is key of HtmlProvider
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}

	/**
	 * @return string|null
	 */
	public function getPrefixedDBKey(): ?string {
		return $this->prefixedDBKey;
	}

	/**
	 * @return int|null
	 */
	public function getRevisionId(): ?int {
		return $this->revisionId;
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		return $this->params;
	}

	/**
	 * @return string
	 */
	public function getUniqueId(): string {
		if ( $this->prefixedDBKey !== null ) {
			return md5( $this->prefixedDBKey );
		} else {
			return md5( $this->label );
		}
	}
}
