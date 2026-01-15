<?php

namespace MWStake\MediaWiki\Component\Wikitext\Node;

class Transclusion extends TextNode {
	/** @var string */
	private $target;
	/** @var array */
	private $params;

	/**
	 * @param string $target
	 * @param array $params
	 * @param string $wikitext
	 */
	public function __construct( $target, $params, $wikitext ) {
		$this->target = $target;
		$this->params = $params;
		parent::__construct( $wikitext );
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'transclusion';
	}

	/**
	 * @return string
	 */
	public function getTarget(): string {
		return $this->target;
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		return $this->params;
	}

	/**
	 * @param string $target
	 */
	public function setTarget( $target ) {
		$this->setData(
			str_replace( $this->target, $target, $this->getCurrentData() )
		);
		$this->target = $target;
	}

	/**
	 * @param int|string $index
	 * @param string $value
	 * @param bool $allowNew
	 * @return bool
	 */
	public function setParam( $index, $value, $allowNew = false ): bool {
		if ( !$allowNew && !isset( $this->params[$index] ) ) {
			return false;
		}
		$search = '';
		$replacement = '';
		if ( isset( $this->params[$index] ) ) {
			if ( $value === $this->params[$index] ) {
				return true;
			}
			if ( is_int( $index ) ) {
				$search = '/\|' . $this->params[$index] . '/';
				$replacement = '|' . $value;
			} else {
				$search = '/\|' . $index . '=' . $this->params[$index] . '/';
				$replacement = "|$index=$value";
			}
		} else {
			$search = '/\}\}/';
			if ( is_int( $index ) ) {
				$replacement = "|$value}}";
			} else {
				$replacement = "|$index=$value}}";
			}
		}
		$this->setData( preg_replace(
			$search,
			$replacement, $this->getCurrentData()
		) );
		$this->params[$index] = $value;

		return false;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'type' => $this->getType(),
			'target' => $this->getTarget(),
			'params' => $this->getParams(),
			'wikitext' => $this->getCurrentData()
		];
	}
}
