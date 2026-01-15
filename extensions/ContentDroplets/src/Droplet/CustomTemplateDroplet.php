<?php

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Json\FormatJson;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;

class CustomTemplateDroplet extends TemplateDroplet {

	/** @var string */
	private $name;
	/** @var string */
	private $description;
	/** @var string */
	private $icon;
	/** @var string */
	private $target;
	/** @var array */
	private $categories;
	/** @var array */
	private $params;
	/** @var array */
	private $rlModules = [];
	/** @var string */
	private $type;

	/**
	 * @param string $name
	 * @param string $description
	 * @param string $icon
	 * @param string $target
	 * @param array $params
	 * @param string $type
	 * @param array|null $categories
	 * @param array $rlModules
	 */
	public function __construct(
		string $name, string $description, string $icon, string $target,
		array $params, string $type, ?array $categories = [], ?array $rlModules = []
	) {
		$this->name = $name;
		$this->description = $description;
		$this->icon = $icon;
		$this->target = $target;
		$this->params = $params;
		$this->categories = $categories;
		$this->rlModules = $rlModules;
		$this->type = $type;
	}

	/**
	 * Get target for the template
	 * @return string
	 */
	protected function getTarget(): string {
		return $this->target;
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		$target = $this->getTarget();
		$params = $this->getParams();
		$params = $this->formatParams( $params );

		if ( empty( $params ) ) {
			return FormatJson::encode( [
				'target' => [
					'href' => "./Template:$target",
					'wt' => $target
				]
			] );
		}

		return FormatJson::encode( [
			'target' => [
				'href' => "./Template:$target",
				'wt' => $target
			],
			'params' => $params
		] );
	}

	/**
	 * @param array $params
	 * @return array
	 */
	private function formatParams( array $params ) {
		if ( $this->isAssoc( $params ) ) {
			return $params;
		}
		$res = [];
		foreach ( $params as $index => $param ) {
			$res[$index + 1] = $param;
		}
		return $res;
	}

	/**
	 * Check if array is associative
	 * @param array $params
	 * @return bool
	 */
	private function isAssoc( array $params ) {
		return array_keys( $params ) !== range( 0, count( $params ) - 1 );
	}

	/**
	 * Template params
	 * @return array
	 */
	protected function getParams(): array {
		return $this->params;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		$message = Message::newFromKey( $this->name );
		return $message->exists() ? $message : new RawMessage( $this->name );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		$message = Message::newFromKey( $this->description );
		return $message->exists() ? $message : new RawMessage( $this->description );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return $this->icon;
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return $this->rlModules;
	}

	/**
	 * @inheritDoc
	 */
	public function getCategories(): array {
		return $this->categories;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

}
