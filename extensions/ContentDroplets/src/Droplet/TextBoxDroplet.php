<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Message\Message;

class TextBoxDroplet extends TemplateDroplet {

	/** @var string */
	private $type;
	/** @var string[] */
	private $categories;

	/**
	 * @param string $type
	 * @param string[] $categories
	 */
	public function __construct( $type, $categories = [ 'content' ] ) {
		$this->type = $type;
		$this->categories = $categories;
	}

	/**
	 * Get target for the template
	 * @return string
	 */
	protected function getTarget(): string {
		return 'Textbox';
	}

	/**
	 * Template params
	 * @return array
	 */
	protected function getParams(): array {
		return [
			'boxtype' => $this->type,
			'header' => '',
			'text' => '',
			'icon' => 'yes'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( "contentdroplets-droplet-text-box-{$this->type}" );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( "contentdroplets-droplet-text-box-{$this->type}-desc" );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return "droplet-{$this->type}";
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.contentdroplets.droplets.text' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getCategories(): array {
		return $this->categories;
	}
}
