<?php

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;

class CustomTagDroplet extends TagDroplet {

	/** @var string */
	private $name;
	/** @var string */
	private $description;
	/** @var string */
	private $icon;
	/** @var string */
	private $tagname;
	/** @var bool */
	private $hasContent;
	/** @var string */
	private $veCommand;
	/** @var array */
	private $categories;
	/** @var array */
	private $attributes;
	/** @var array */
	private $rlModules = [];
	/** @var string */
	private $type;

	/**
	 * @param string $name
	 * @param string $description
	 * @param string $icon
	 * @param string $tagname
	 * @param array $attributes
	 * @param bool $hasContent
	 * @param string $veCommand
	 * @param string $type
	 * @param array|null $categories
	 * @param array $rlModules
	 */
	public function __construct(
		string $name, string $description, string $icon, string $tagname, array $attributes,
		bool $hasContent, string $veCommand, string $type, ?array $categories = [], ?array $rlModules = []
	) {
		$this->name = $name;
		$this->description = $description;
		$this->icon = $icon;
		$this->tagname = $tagname;
		$this->attributes = $attributes;
		$this->hasContent = $hasContent;
		$this->veCommand = $veCommand;
		$this->categories = $categories;
		$this->rlModules = $rlModules;
		$this->type = $type;
	}

	protected function hasContent(): bool {
		return $this->hasContent;
	}

	public function getVeCommand(): string {
		return $this->veCommand;
	}

	/**
	 * Tag attributes
	 * @return string
	 */
	protected function getTagName(): string {
		return $this->tagname;
	}

	/**
	 * Tag attributes
	 * @return array
	 */
	protected function getAttributes(): array {
		return $this->attributes;
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
