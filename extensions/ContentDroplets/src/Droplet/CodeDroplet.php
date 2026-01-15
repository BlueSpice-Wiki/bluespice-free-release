<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Message\Message;

class CodeDroplet extends GenericDroplet {

	/**
	 */
	public function __construct() {
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'contentdroplets-droplet-code-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'contentdroplets-droplet-code-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-code';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.geshi.visualEditor' ];
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'content', 'data' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getContent(): string {
		return '<syntaxhighlight lang="abl" line="1">
		CodeBlock
		</syntaxhighlight>';
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'syntaxhighlightDialog';
	}
}
