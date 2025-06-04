<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Extension\ContentDroplets\IDropletDescription;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;

class GenericDroplet implements IDropletDescription {
	/** @var string */
	private $name;
	/** @var string */
	private $description;
	/** @var string */
	private $icon;
	/** @var string */
	private $content;
	/** @var array */
	private $categories;
	/** @var array */
	private $rlModules = [];

	/**
	 * @param string $name
	 * @param string $description
	 * @param string $icon
	 * @param string $content
	 * @param array|null $categories
	 * @param array $rlModules
	 */
	public function __construct(
		string $name, string $description, string $icon, string $content,
		?array $categories = [], ?array $rlModules = []
	) {
		$this->name = $name;
		$this->description = $description;
		$this->icon = $icon;
		$this->content = $content;
		$this->categories = $categories;
		$this->rlModules = $rlModules;
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
	 * @inheritDoc
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * @inheritDoc
	 */
	public function getVeCommand(): ?string {
		return null;
	}
}
