<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;

class ExportPage {

		/** @var string */
	private $type;

	/** @var string */
	private $label;

	/** @var string|null */
	private $prefixedDBKey = null;

	/** @var DOMDocument */
	private $dom;

	/** @var string|null */
	private $uniqueId;

	/** @var array */
	private $params;

	/**
	 * @param string $type
	 * @param DOMDocument $dom
	 * @param string $label
	 * @param string|null $prefixedDBKey
	 * @param array $params
	 * @param string|null $uniqueId
	 */
	public function __construct(
		string $type, DOMDocument $dom, string $label,
		?string $prefixedDBKey = null, array $params = [], ?string $uniqueId = null
	) {
		$this->type = $type;
		$this->dom = $dom;
		$this->label = $label;
		$this->prefixedDBKey = $prefixedDBKey;
		$this->uniqueId = $uniqueId;
		$this->params = $params;
	}

	/**
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
	 * @return DOMDocument
	 */
	public function getDOMDocument(): DOMDocument {
		return $this->dom;
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
		if ( $this->uniqueId !== null ) {
			return $this->uniqueId;
		} elseif ( $this->prefixedDBKey !== null ) {
			return md5( $this->prefixedDBKey );
		} else {
			return md5( $this->label );
		}
	}
}
