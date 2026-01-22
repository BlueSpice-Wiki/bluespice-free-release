<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Extension\ContentDroplets\IDropletDescription;

abstract class TagDroplet implements IDropletDescription {

	/**
	 * @return string
	 */
	public function getContent(): string {
		$tag = $this->getTagName();
		$attributes = $this->getAttributes();
		$attributes = $this->formatAttributes( $attributes );

		$text = "<{$tag}";
		if ( $attributes ) {
			$text .= " " . implode( ' ', $attributes );
		}
		$text .= $this->hasContent() ? "></$tag>" : " />";

		return $text;
	}

	/**
	 * Get target for the template
	 * @return string
	 */
	abstract protected function getTagName(): string;

	/**
	 * List of params allowed for the tag
	 * Assoc array, values are default values
	 * @return array
	 */
	abstract protected function getAttributes(): array;

	/**
	 * Whether tag has content or not (if false, it will be self-closing)
	 * @return bool
	 */
	abstract protected function hasContent(): bool;

	/**
	 * @param array $attributes
	 * @return array
	 */
	private function formatAttributes( array $attributes ) {
		$formatted = [];
		foreach ( $attributes as $key => $value ) {
			$formatted[] = "$key=\"$value\"";
		}
		return $formatted;
	}

	/**
	 * @inheritDoc
	 */
	public function listDroplet(): bool {
		return true;
	}
}
